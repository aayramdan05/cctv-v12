import os
import time
import subprocess
import psycopg2
import yaml
import glob
import threading
from datetime import datetime, timedelta
from dotenv import load_dotenv

# Load Environment Variables dari file .env
load_dotenv('/home/aay/cctv-scripts/.env')

DB_HOST = os.getenv('DB_HOST')
DB_PORT = os.getenv('DB_PORT')
DB_NAME = os.getenv('DB_DATABASE')
DB_USER = os.getenv('DB_USERNAME')
DB_PASS = os.getenv('DB_PASSWORD')

NODE_IP = os.getenv('SERVER_RECORDER_IP')
RETENTION_DAYS = int(os.getenv('RETENTION_DAYS', 2))
RECORD_DURATION = 900  # 15 menit per chunk file mp4
CHECK_INTERVAL = 5     # Cek update konfigurasi database setiap 5 menit

GO2RTC_CONFIG_PATH = os.getenv('GO2RTC_CONFIG_PATH', '/home/aay/go2rtc.yaml')
STORAGE_BASE_PATH = os.getenv('RECORDINGS_PATH', '/home/aay/storage/recordings')

def get_db_connection():
    """Membuat koneksi ke database Master dengan Autocommit (Anti-Zombie)"""
    try:
        conn = psycopg2.connect(
            host=DB_HOST, port=DB_PORT, dbname=DB_NAME, user=DB_USER, password=DB_PASS
        )
        conn.autocommit = True
        return conn
    except Exception as e:
        print(f"❌ Database Connection Error: {e}")
        return None

def auto_cleanup():
    """Worker background untuk menghapus file dan record lama"""
    while True:
        conn = get_db_connection()
        current_retention = 7 # Default fallback
        
        if conn:
            try:
                cur = conn.cursor()
                # 1. Ambil ID Server dan Batas Retensi dari Database Master
                cur.execute("SELECT id, retention_days FROM servers WHERE ip_address = %s", (NODE_IP,))
                server_row = cur.fetchone()
                
                if server_row:
                    server_id = server_row[0]
                    current_retention = server_row[1] or 7
                    
                    print(f"🧹 Memulai Auto-Cleanup (Batas Dashboard: {current_retention} hari)...")
                    
                    cutoff_date = datetime.now() - timedelta(days=current_retention)
                    cutoff_date_str = cutoff_date.strftime('%Y-%m-%d')

                    # 2. Hapus dari Database (Hanya CCTV milik Server ini)
                    cur.execute("""
                        DELETE FROM recordings 
                        WHERE date < %s AND cctv_id IN (
                            SELECT id FROM cctvs WHERE server_id = %s
                        )
                    """, (cutoff_date_str, server_id))
                    print(f"🗑️ Membersihkan data database sebelum {cutoff_date_str}.")
                
                cur.close()
            except Exception as e:
                print(f"❌ Error Auto-Cleanup Database: {e}")
            finally:
                conn.close()

        # 3. Hapus Folder Fisik di Harddisk Node (Gunakan current_retention terbaru)
        try:
            cutoff_date = datetime.now() - timedelta(days=current_retention)
            if os.path.exists(STORAGE_BASE_PATH):
                folders = glob.glob(f"{STORAGE_BASE_PATH}/*")
                for folder in folders:
                    if os.path.isdir(folder):
                        folder_name = os.path.basename(folder)
                        try:
                            folder_date = datetime.strptime(folder_name, '%Y-%m-%d')
                            if folder_date < cutoff_date:
                                subprocess.run(['rm', '-rf', folder])
                                print(f"🗑️ Folder rekaman usang terhapus: {folder_name}")
                        except ValueError:
                            pass
        except Exception as e:
            print(f"❌ Error Auto-Cleanup Folder: {e}")

        # Tidur 6 jam sebelum mengecek lagi
        time.sleep(6 * 3600)

# Tambahkan ini di bagian atas (Environment Variables)
MASTER_URL = os.getenv('MASTER_URL', f"http://{DB_HOST}")

def sync_go2rtc_config_from_db():
    """Menarik konfigurasi kamera dari API Master (Sudah didekripsi) dan mengupdate go2rtc.yaml"""
    try:
        import requests
        # Panggil API Master
        api_url = f"{MASTER_URL}/api/node-config?ip={NODE_IP}"
        response = requests.get(api_url, timeout=10)
        
        if response.status_code != 200:
            print(f"❌ Gagal ambil config dari API: {response.status_code}")
            return []

        data = response.json()
        streams_from_api = data.get('streams', {})
        cameras_list = data.get('cameras_list', [])

        if not streams_from_api:
            print(f"⚠️ Tidak ada kamera yang ditugaskan untuk Node IP: {NODE_IP}")
            return []

        # Susun struktur YAML untuk go2rtc
        new_config = {
            'streams': streams_from_api,
            'rtsp': {'listen': ':8554'}
        }
        
        # Cek apakah config berubah sebelum menulis (efisiensi)
        current_config = {}
        if os.path.exists(GO2RTC_CONFIG_PATH):
            with open(GO2RTC_CONFIG_PATH, 'r') as f:
                try:
                    current_config = yaml.safe_load(f) or {}
                except:
                    pass

        if new_config != current_config:
            print(f"🔄 Perubahan terdeteksi! Mengupdate {GO2RTC_CONFIG_PATH}...")
            with open(GO2RTC_CONFIG_PATH, 'w') as f:
                yaml.dump(new_config, f, default_flow_style=False)
            
            # Beritahu go2rtc untuk reload via API (port 1984)
            try:
                requests.get("http://127.0.0.1:1984/api/reload", timeout=2)
                print("✅ Go2RTC Configuration Reloaded.")
            except Exception as e:
                print(f"⚠️ Gagal reload Go2RTC API: {e} (Mungkin Go2RTC belum jalan)")

        return cameras_list

    except Exception as e:
        print(f"❌ Error Sync Config from API: {e}")
        return []

def record_worker(cam_id, stream_url):
    """Worker rekaman individu per kamera"""
    print(f"🔴 Memulai thread auto-recording Kamera {cam_id}")
    while True:
        now = datetime.now()
        date_folder = now.strftime('%Y-%m-%d')
        start_seconds = now.hour * 3600 + now.minute * 60 + now.second
        
        folder_path = f"{STORAGE_BASE_PATH}/{date_folder}"
        os.makedirs(folder_path, exist_ok=True)
        
        end_time = now + timedelta(seconds=RECORD_DURATION)
        part_counter = 1
        recorded_parts = []
        
        # 1. Perekaman per Part (Format TS HLS)
        while datetime.now() < end_time:
            remaining_sec = int((end_time - datetime.now()).total_seconds())
            if remaining_sec < 5:
                break
                
            temp_path = f"{folder_path}/temp_{cam_id}_{int(time.time())}.ts"
            
            cmd = [
                'ffmpeg', '-y', '-hide_banner', '-loglevel', 'error',
                '-rtsp_transport', 'tcp',
                '-i', stream_url,
                '-t', str(remaining_sec),
                '-c', 'copy',
                '-f', 'mpegts',
                temp_path
            ]
            
            subprocess.run(cmd, stderr=subprocess.PIPE)
            
            # Pastikan file berhasil direkam dan isinya lebih dari 50KB
            if os.path.exists(temp_path) and os.path.getsize(temp_path) > 50 * 1024:
                recorded_parts.append(temp_path)
            else:
                if os.path.exists(temp_path):
                    os.remove(temp_path)
                time.sleep(5)
                
            part_counter += 1
            
        # 2. Penggabungan & Konversi ke MP4 Final
        if len(recorded_parts) > 0:
            final_filename = f"cam_{cam_id}_{now.strftime('%Y-%m-%d_%H-%M-%S')}.mp4"
            final_path = f"{folder_path}/{final_filename}"
            list_txt_path = f"{folder_path}/list_{cam_id}_{int(time.time())}.txt"
            
            with open(list_txt_path, 'w') as f:
                for part in recorded_parts:
                    f.write(f"file '{part}'\n")
                    
            concat_cmd = [
                'ffmpeg', '-y', '-hide_banner', '-loglevel', 'error',
                '-f', 'concat', '-safe', '0',
                '-i', list_txt_path,
                '-c', 'copy',
                '-movflags', '+faststart',
                final_path
            ]
            
            subprocess.run(concat_cmd)
            
            # 3. PEMBERSIHAN FILE TEMP DENGAN PENGECEKAN EKSTRA AMAN
            if os.path.exists(list_txt_path):
                os.remove(list_txt_path)
            for part in recorded_parts:
                if os.path.exists(part):
                    try:
                        os.remove(part)
                    except Exception:
                        pass
                        
            # 4. SIMPAN LAPORAN KE DATABASE MASTER
            if os.path.exists(final_path) and os.path.getsize(final_path) > 1024 * 1024:
                file_size_mb = round(os.path.getsize(final_path) / (1024 * 1024), 2)
                
                conn = get_db_connection()
                if conn:
                    try:
                        cur = conn.cursor()
                        now_str = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
                        cur.execute("""
                            INSERT INTO recordings (cctv_id, date, filename, start_time, duration, size_mb, created_at, updated_at)
                            VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                        """, (cam_id, date_folder, final_filename, start_seconds, RECORD_DURATION, file_size_mb, now_str, now_str))
                        print(f"🎬 Berhasil rekam & simpan ke DB: {final_filename} ({file_size_mb} MB)")
                        cur.close()
                    except Exception as e:
                        print(f"❌ Gagal simpan {final_filename} ke DB: {e}")
                    finally:
                        conn.close()

if __name__ == '__main__':
    print(f"🚀 Memulai CCTV Node Agent | Interval: {CHECK_INTERVAL}m | Retention: {RETENTION_DAYS} Hari")
    
    # Jalankan Auto-Cleanup di background
    threading.Thread(target=auto_cleanup, daemon=True).start()
    
    active_threads = {}
    
    while True:
        cameras = sync_go2rtc_config_from_db()
        
        # Mulai thread untuk kamera baru
        for cam in cameras:
            if cam['id'] not in active_threads or not active_threads[cam['id']].is_alive():
                t = threading.Thread(target=record_worker, args=(cam['id'], cam['url']), daemon=True)
                t.start()
                active_threads[cam['id']] = t
                
        # Sinkronisasi konfigurasi database Master setiap 5 menit
        time.sleep(CHECK_INTERVAL * 60) 
