import os
import time
import subprocess
import psycopg2
import psycopg2.extensions
import yaml
import requests
import select
import glob
import threading
from datetime import datetime, timedelta
from dotenv import load_dotenv
import builtins

# Global dictionary untuk menyimpan nama kamera berdasarkan ID
camera_names = {}

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
CHECK_INTERVAL = 30    # Jeda pengecekan update dari database (detik)

GO2RTC_CONFIG_PATH = os.getenv('GO2RTC_CONFIG_PATH', '/home/aay/go2rtc.yaml')
STORAGE_BASE_PATH = os.getenv('RECORDINGS_PATH', '/var/www/html/storage/recordings')

MASTER_URL = os.getenv('MASTER_URL', f"http://{DB_HOST}")
SYNC_TOKEN = os.getenv('SYNC_TOKEN', 'secret_unpad_cctv_2026') # Harus sama dengan di Master

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

def sync_go2rtc_config_from_db():
    """Menarik konfigurasi kamera dari API Master (DIPROTEKSI) dan mengupdate go2rtc.yaml"""
    try:
        import requests
        # Panggil API Master dengan Token Keamanan
        api_url = f"{MASTER_URL}/api/node-config?ip={NODE_IP}&token={SYNC_TOKEN}"
        response = requests.get(api_url, timeout=10)
        
        if response.status_code != 200:
            print(f"❌ Gagal ambil config dari API: {response.status_code}")
            return []

        data = response.json()
        streams_from_api = data.get('streams', {})
        cameras_list = data.get('cameras_list', [])

        # Simpan kode kamera untuk keperluan log agar lebih mudah dibaca (e.g. [CAM.LOBBY.01])
        for cam in cameras_list:
            camera_names[cam['id']] = cam.get('kode_cctv', f"ID_{cam['id']}")

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
            
            # Beritahu go2rtc untuk reload via API (Coba POST dan GET)
            reloaded = False
            try:
                # Coba POST dulu
                r = requests.post("http://localhost:1984/api/reload", timeout=3)
                if r.status_code == 200:
                    reloaded = True
                    print("✅ Go2RTC Configuration Reloaded via API (POST).")
                else:
                    # Coba GET jika POST gagal
                    r = requests.get("http://localhost:1984/api/reload", timeout=3)
                    if r.status_code == 200:
                        reloaded = True
                        print("✅ Go2RTC Configuration Reloaded via API (GET).")
            except Exception as e:
                print(f"⚠️ API Reload gagal: {e}")

            # FALLBACK: Jika API gagal, paksa restart service
            if not reloaded:
                print("⚙️ Melakukan Fallback: Restarting go2rtc service...")
                subprocess.run(['sudo', 'systemctl', 'restart', 'go2rtc'])
                print("✅ Go2RTC Service Restarted.")

        return cameras_list

    except Exception as e:
        print(f"❌ Error Sync Config from API: {e}")
        return []

def record_worker(cam_id, stream_url):
    """Worker rekaman individu per kamera (Direct Fragmented MP4)"""
    cam_label = camera_names.get(cam_id, f"ID_{cam_id}")
    print(f"🔴 Memulai thread auto-recording Kamera: {cam_label}", flush=True)
    while True:
        # Cek apakah ada sinyal untuk berhenti
        if stop_signals.get(cam_id):
            print(f"👋 [{cam_label}] Thread dihentikan (Sinyal STOP diterima).", flush=True)
            break

        try:
            now = datetime.now()
            date_folder = now.strftime('%Y-%m-%d')
            folder_path = f"{STORAGE_BASE_PATH}/{date_folder}"
            os.makedirs(folder_path, exist_ok=True)
            
            final_filename = f"cam_{cam_id}_{now.strftime('%H-%M-%S')}.mp4"
            final_path = f"{folder_path}/{final_filename}"
            
            # 🎬 DAFTARKAN AWAL KE DATABASE (Agar muncul di dashboard Playback seketika)
            start_time = int(time.time())
            now_str = now.strftime('%Y-%m-%d %H:%M:%S')
            try:
                conn = get_db_connection()
                cur = conn.cursor()
                cur.execute("""
                    INSERT INTO recordings (cctv_id, date, filename, start_time, duration, size_mb, created_at, updated_at)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                    ON CONFLICT DO NOTHING
                """, (cam_id, date_folder, final_filename, start_time, RECORD_DURATION, 0, now_str, now_str))
                conn.commit()
                cur.close()
                conn.close()
                print(f"📡 [{cam_label}] Rekaman terdaftar di Master: {final_filename}", flush=True)
            except psycopg2.errors.ForeignKeyViolation:
                print(f"❌ [{cam_label}] Kamera sudah tidak ada di Database Master. Mematikan thread...", flush=True)
                stop_signals[cam_id] = True
                break
            except Exception as e:
                print(f"⚠️ [{cam_label}] Gagal pendaftaran awal: {e}", flush=True)

            # 🎥 MULAI REKAMAN (Fragmented MP4)
            ffmpeg_cmd = [
                'ffmpeg', '-y', '-hide_banner', '-loglevel', 'error',
                '-rtsp_transport', 'tcp',
                '-i', stream_url,
                '-c', 'copy', '-map', '0',
                '-f', 'mp4',
                '-movflags', '+frag_keyframe+empty_moov+default_base_moof',
                '-t', str(RECORD_DURATION),
                final_path
            ]
            
            p = subprocess.Popen(ffmpeg_cmd)
            active_processes[cam_id] = p
            p.wait()
            
            # Hapus dari daftar proses setelah selesai
            if cam_id in active_processes:
                active_processes.pop(cam_id)

            # 🏁 UPDATE DATABASE SAAT SELESAI
            if os.path.exists(final_path):
                file_size_mb = round(os.path.getsize(final_path) / (1024 * 1024), 2)
                try:
                    conn = get_db_connection()
                    cur = conn.cursor()
                    cur.execute("""
                        UPDATE recordings 
                        SET size_mb = %s, updated_at = %s 
                        WHERE cctv_id = %s AND filename = %s
                    """, (file_size_mb, datetime.now().strftime('%Y-%m-%d %H:%M:%S'), cam_id, final_filename))
                    conn.commit()
                    cur.close()
                    conn.close()
                    print(f"🎬 [{cam_label}] Rekaman selesai & diperbarui: {final_filename} ({file_size_mb} MB)", flush=True)
                except Exception as e:
                    print(f"❌ [{cam_label}] Gagal update hasil ke DB: {e}", flush=True)
            
        except Exception as e:
            print(f"❌ [{cam_label}] Worker Error: {e}", flush=True)
            time.sleep(10)

if __name__ == '__main__':
    print(f"🚀 CCTV Node Agent Started (Node IP: {NODE_IP})", flush=True)
    print(f"📡 DEBUG: Mengambil config pertama kali...", flush=True)

    # 1. Jalankan sinkronisasi pertama kali saat startup
    try:
        cameras = sync_go2rtc_config_from_db()
        print(f"✅ DEBUG: Config awal berhasil diambil. Total {len(cameras)} kamera.", flush=True)
    except Exception as e:
        print(f"⚠️ DEBUG: Gagal ambil config awal: {e}. Tetap lanjut ke listener...", flush=True)
        cameras = []
    
    # 2. Jalankan Auto-Cleanup di background
    print(f"📡 DEBUG: Memulai thread Auto-Cleanup...", flush=True)
    threading.Thread(target=auto_cleanup, daemon=True).start()
    
    active_threads = {}
    active_processes = {} # Melacak proses FFmpeg yang sedang jalan
    stop_signals = {} # Sinyal untuk menghentikan thread zombi
    
    def manage_recording_threads(cameras_list):
        current_cam_ids = [cam['id'] for cam in cameras_list]
        
        # A. Matikan thread untuk kamera yang sudah tidak ada di list Node ini
        stopped_count = 0
        for cam_id in list(active_threads.keys()):
            if cam_id not in current_cam_ids:
                cam_label = camera_names.get(cam_id, f"ID_{cam_id}")
                print(f"🛑 [{cam_label}] Tidak lagi di Node ini. Mematikan paksa...", flush=True)
                stop_signals[cam_id] = True # Kirim sinyal stop ke thread
                
                # BUKAN CUMA SINYAL, TAPI KILL PROSESNYA JUGA
                if cam_id in active_processes:
                    try:
                        active_processes[cam_id].terminate()
                        print(f"💀 [{cam_label}] Proses FFmpeg dihentikan paksa.", flush=True)
                    except:
                        pass
                
                active_threads.pop(cam_id)
                stopped_count += 1
        
        # B. Mulai thread untuk kamera baru
        started_count = 0
        for cam in cameras_list:
            cam_id = cam['id']
            if cam_id not in active_threads or not active_threads[cam_id].is_alive():
                print(f"🌟 [CAM {cam_id}] Memulai thread auto-recording baru", flush=True)
                stop_signals[cam_id] = False # Reset sinyal stop
                t = threading.Thread(target=record_worker, args=(cam_id, cam['url']), daemon=True)
                t.start()
                active_threads[cam_id] = t
                started_count += 1
        
        if started_count > 0 or stopped_count > 0:
            print(f"📊 Summary: {started_count} Started, {stopped_count} Stopped.", flush=True)

    # Jalankan recording pertama kali
    print(f"📡 DEBUG: Memulai thread perekaman...", flush=True)
    time.sleep(2) # Beri waktu Go2RTC startup
    manage_recording_threads(cameras)

    # 3. Setup Listener Real-time
    print(f"📡 DEBUG: Menghubungkan ke Listener Database {DB_HOST}...", flush=True)
    conn = get_db_connection()
    if not conn:
        print("❌ DEBUG: Gagal konek ke DB untuk listener. Keluar...", flush=True)
        exit(1)

    print(f"✅ DEBUG: Koneksi DB listener sukses. Menyiapkan LISTEN...", flush=True)
    conn.set_isolation_level(psycopg2.extensions.ISOLATION_LEVEL_AUTOCOMMIT)
    curs = conn.cursor()
    
    print("👂 DEBUG: Menjalankan perintah LISTEN cctv_update...", flush=True)
    curs.execute("LISTEN cctv_update;")
    print("✅ LISTENER AKTIF. Menunggu sinyal real-time...", flush=True)

    while True:
        try:
            # Poll koneksi database
            conn.poll()
            while conn.notifies:
                notify = conn.notifies.pop(0)
                payload = notify.payload.strip()
                print(f"🔔 NOTIFIKASI MASUK: Payload='{payload}' (IP Node ini: '{NODE_IP}')", flush=True)
                
                if payload == NODE_IP or payload == 'ALL':
                    print("🔄 SINKRONISASI OTOMATIS DIMULAI...", flush=True)
                    updated_cameras = sync_go2rtc_config_from_db()
                    
                    # ⏳ Jeda 2 detik agar Go2RTC siap sebelum direkam
                    time.sleep(2)
                    
                    manage_recording_threads(updated_cameras)
                else:
                    print(f"⏭️ Notifikasi diabaikan. Payload '{payload}' tidak cocok.", flush=True)
            
            # Tidur sebentar agar tidak membebani CPU (1 detik)
            time.sleep(1)
                        
        except (psycopg2.DatabaseError, psycopg2.OperationalError) as e:
            print(f"⚠️ Koneksi terputus: {e}. Mencoba reconnect dalam 5 detik...", flush=True)
            time.sleep(5)
            conn = get_db_connection()
            if conn:
                conn.set_isolation_level(psycopg2.extensions.ISOLATION_LEVEL_AUTOCOMMIT)
                curs = conn.cursor()
                curs.execute("LISTEN cctv_update;")
                print("🔄 Reconnected & Listening again.", flush=True)
        except Exception as e:
            print(f"❌ Error in listener loop: {e}", flush=True)
            time.sleep(5)
