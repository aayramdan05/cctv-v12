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

GO2RTC_CONFIG_PATH = '/home/aay/go2rtc.yaml'
STORAGE_BASE_PATH = '/home/aay/storage/recordings'

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
        print(f"🧹 Memulai Pengecekan Auto-Cleanup (Batas: {RETENTION_DAYS} hari)...")
        cutoff_date = datetime.now() - timedelta(days=RETENTION_DAYS)
        cutoff_date_str = cutoff_date.strftime('%Y-%m-%d')

        conn = get_db_connection()
        if conn:
            try:
                cur = conn.cursor()
                
                # 1. Hapus dari Database (Hanya CCTV milik Server ini)
                cur.execute("SELECT id FROM servers WHERE ip_address = %s", (NODE_IP,))
                server_row = cur.fetchone()
                if server_row:
                    server_id = server_row[0]
                    cur.execute("""
                        DELETE FROM recordings 
                        WHERE date < %s AND cctv_id IN (
                            SELECT id FROM cctvs WHERE server_id = %s
                        )
                    """, (cutoff_date_str, server_id))
                    print(f"🗑️ Membersihkan data rekaman sebelum {cutoff_date_str} dari Database.")
                
                cur.close()
            except Exception as e:
                print(f"❌ Error Auto-Cleanup Database: {e}")
            finally:
                conn.close()

        # 2. Hapus Folder Fisik di Harddisk Node
        try:
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

def get_cameras_from_go2rtc():
    """Membaca CCTV dari go2rtc.yaml dan menggunakan RTSP Lokal (Menghemat bandwidth!)"""
    cameras = []
    if os.path.exists(GO2RTC_CONFIG_PATH):
        try:
            with open(GO2RTC_CONFIG_PATH, 'r') as f:
                config = yaml.safe_load(f) or {}
                
            streams = config.get('streams', {})
            for cam_name, urls in streams.items():
                if cam_name.startswith('camera_'):
                    try:
                        c_id = int(cam_name.replace('camera_', ''))
                        # Menggunakan RTSP LOKAL dari Go2RTC!
                        local_rtsp_url = f"rtsp://127.0.0.1:8554/{cam_name}"
                        cameras.append({'id': c_id, 'url': local_rtsp_url})
                    except ValueError:
                        pass
            print(f"✅ Menemukan {len(cameras)} Kamera di {GO2RTC_CONFIG_PATH}")
        except Exception as e:
            print(f"❌ Error membaca go2rtc.yaml: {e}")
    else:
        print(f"⚠️ File config tidak ditemukan: {GO2RTC_CONFIG_PATH}")
        
    return cameras

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

def health_check_worker():
    """Worker untuk mengecek apakah kamera fisik UP atau DOWN (Update Dashboard)"""
    print(f"📡 Memulai thread Health-Check Kamera (Node: {NODE_IP})...")
    while True:
        conn = get_db_connection()
        if conn:
            try:
                cur = conn.cursor()
                # 1. Ambil semua CCTV yang dikelola server ini
                cur.execute("""
                    SELECT c.id, c.rtsp_url, c.nama_cctv 
                    FROM cctvs c
                    JOIN servers s ON c.server_id = s.id
                    WHERE s.ip_address = %s
                """, (NODE_IP,))
                cameras = cur.fetchall()

                for cam_id, rtsp_url, name in cameras:
                    # 2. Probe menggunakan FFmpeg (Timeout 5 detik)
                    # Kita cek apakah stream bisa dibuka
                    cmd = [
                        'ffprobe', '-v', 'error', '-rtsp_transport', 'tcp', 
                        '-show_entries', 'format=format_name', '-t', '1',
                        rtsp_url
                    ]
                    
                    try:
                        result = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, timeout=8)
                        status = 'online' if result.returncode == 0 else 'offline'
                    except subprocess.TimeoutExpired:
                        status = 'offline'
                    
                    # 3. Update Status ke Database
                    cur.execute("UPDATE cctvs SET status = %s, updated_at = NOW() WHERE id = %s", (status, cam_id))
                    
                    if status == 'offline':
                        print(f"⚠️ Kamera {name} (ID: {cam_id}) TERDETEKSI OFFLINE!")

                cur.close()
            except Exception as e:
                print(f"❌ Error Health-Check: {e}")
            finally:
                conn.close()
        
        # Cek setiap 2 menit agar tidak membebani network
        time.sleep(120)

if __name__ == '__main__':
    print(f"🚀 Memulai CCTV Node Agent | Interval: {CHECK_INTERVAL}m | Retention: {RETENTION_DAYS} Hari")
    
    # Jalankan Auto-Cleanup & Health-Check di background
    threading.Thread(target=auto_cleanup, daemon=True).start()
    threading.Thread(target=health_check_worker, daemon=True).start()
    
    active_threads = {}
    
    while True:
        cameras = get_cameras_from_go2rtc()
        
        # Mulai thread untuk kamera baru
        for cam in cameras:
            if cam['id'] not in active_threads or not active_threads[cam['id']].is_alive():
                t = threading.Thread(target=record_worker, args=(cam['id'], cam['url']), daemon=True)
                t.start()
                active_threads[cam['id']] = t
                
        # Sinkronisasi konfigurasi database Master setiap 5 menit
        time.sleep(CHECK_INTERVAL * 60) 
