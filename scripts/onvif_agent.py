import time
import os
import requests
import threading
import json
import psycopg2
import psycopg2.extensions
import select
from onvif import ONVIFCamera
from datetime import datetime
from dotenv import load_dotenv
import builtins

# Load Environment Variables
import subprocess

# Load Environment Variables
load_dotenv('/home/aay/cctv-scripts/.env')

# ... (print function remains same)

# CONFIGURATION
DB_HOST = os.getenv('DB_HOST', '127.0.0.1')
DB_PORT = os.getenv('DB_PORT', '5432')
DB_NAME = os.getenv('DB_DATABASE', 'cctv_prod')
DB_USER = os.getenv('DB_USERNAME', 'postgres')
DB_PASS = os.getenv('DB_PASSWORD', '')

NODE_IP = os.getenv('SERVER_RECORDER_IP', '10.69.69.41')
MASTER_URL = os.getenv('MASTER_URL', 'http://10.69.69.21')
SYNC_TOKEN = os.getenv('SYNC_TOKEN', 'secret_unpad_cctv_2026')

SNAPSHOT_DIR = '/var/www/html/storage/recordings/snapshots'

# Melacak thread yang sedang berjalan
active_threads = {}

# Melacak waktu terakhir pergerakan terdeteksi per kamera (untuk cooldown)
last_motion_time = {}

def cleanup_old_snapshots():
    """Menghapus screenshot yang sudah lebih dari 1 jam"""
    try:
        now = time.time()
        # 1 Jam = 3600 Detik
        retention_period = 3600 
        
        if not os.path.exists(SNAPSHOT_DIR):
            return
            
        for f in os.listdir(SNAPSHOT_DIR):
            file_path = os.path.join(SNAPSHOT_DIR, f)
            if os.path.isfile(file_path):
                # Cek umur file
                if now - os.path.getmtime(file_path) > retention_period:
                    os.remove(file_path)
                    print(f"🧹 [CLEANUP] Menghapus screenshot lama: {f}")
    except Exception as e:
        print(f"⚠️ Gagal melakukan cleanup: {e}")

def capture_screenshot(cam_id, rtsp_url):
    """Mengambil screenshot dari stream RTSP menggunakan FFmpeg"""
    cleanup_old_snapshots()
    
    if not os.path.exists(SNAPSHOT_DIR):
        try:
            os.makedirs(SNAPSHOT_DIR, exist_ok=True)
            os.chmod(SNAPSHOT_DIR, 0o777)
        except: pass
    
    filename = f"event_{cam_id}_{datetime.now().strftime('%Y%m%d_%H%M%S')}.jpg"
    filepath = os.path.join(SNAPSHOT_DIR, filename)
    
    try:
        # Gunakan original RTSP agar lebih cepat dan stabil untuk snapshot
        command = [
            'ffmpeg', '-y', '-rtsp_transport', 'tcp', '-timeout', '8000000',
            '-i', rtsp_url, '-ss', '00:00:01', '-frames:v', '1',
            '-q:v', '2', filepath
        ]
        subprocess.run(command, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, timeout=15)
        
        # Validasi: Cek apakah file ada dan tidak kosong (0 bytes)
        if os.path.exists(filepath) and os.path.getsize(filepath) > 0:
            print(f"📸 [CAM {cam_id}] Screenshot berhasil: {filename}")
            return filename
        else:
            print(f"⚠️ [CAM {cam_id}] FFmpeg selesai tapi file kosong/tidak ada.")
    except Exception as e:
        print(f"❌ [CAM {cam_id}] Gagal ambil screenshot: {e}")
    
    return None

def report_to_master(cctv_id, event_type, image_file=None):
    """Melaporkan kejadian ke Master Server dengan opsional gambar"""
    try:
        url = f"{MASTER_URL}/api/report-event?cctv_id={cctv_id}&type={event_type}&token={SYNC_TOKEN}"
        if image_file:
            url += f"&image={image_file}"
            
        requests.get(url, timeout=5)
        print(f"🔔 [CAM {cctv_id}] Event {event_type} dilaporkan ke Master!")
    except Exception as e:
        print(f"❌ Gagal lapor event: {e}")

def subscribe_to_camera(cam):
    """Berlangganan event dari kamera via ONVIF"""
    cam_id = cam['id']
    cam_ip = cam['ip']
    
    # Ambil original_url untuk screenshot, fallback ke url go2rtc
    snap_url = cam.get('original_url') or cam.get('url')
    
    onvif_data = cam.get('onvif', {})
    
    port = onvif_data.get('port', 80)
    user = onvif_data.get('user')
    password = onvif_data.get('password')

    if not cam_ip or not user or not password:
        print(f"⚠️ [CAM {cam_id}] Data ONVIF tidak lengkap, skip.")
        return

    while True:
        try:
            print(f"🔍 [CAM {cam_id}] Mencoba koneksi ONVIF ke {cam_ip}:{port}...")
            mycam = ONVIFCamera(cam_ip, port, user, password)
            
            # Inisialisasi Event Service
            event_service = mycam.create_events_service()
            
            # Membuat PullPoint (Cara paling stabil untuk ambil event)
            pullpoint = mycam.create_pullpoint_service()
            
            print(f"✅ [CAM {cam_id}] Berhasil subscribe ONVIF!")

            while True:
                try:
                    messages = pullpoint.PullMessages({'Timeout': 'PT5S', 'MessageLimit': 10})
                except:
                    messages = pullpoint.PullMessages()
                
                if hasattr(messages, 'NotificationMessage'):
                    for msg in messages.NotificationMessage:
                        try:
                            # Deteksi via XML (X-Ray Mode)
                            try:
                                msg_obj = msg.Message._value_1
                                for item in msg_obj.xpath('.//tt:SimpleItem', namespaces={'tt': 'http://www.onvif.org/ver10/schema'}):
                                    name = item.get('Name')
                                    value = item.get('Value')
                                    
                                    if name in ["IsMotion", "IsTamper"] and (value == "true" or value == "1"):
                                        current_time = time.time()
                                        last_time = last_motion_time.get(cam_id, 0)
                                        # COOLDOWN 10 DETIK
                                        if current_time - last_time >= 10:
                                            print(f"✨ [CAM {cam_id}] DETEKSI PERGERAKAN!")
                                            img_name = capture_screenshot(cam_id, snap_url)
                                            report_to_master(cam_id, 'motion', img_name)
                                            last_motion_time[cam_id] = current_time
                            except:
                                raw_msg = str(msg)
                                if any(x in raw_msg for x in ['IsMotion="true"', 'Value="true"', 'IsMotion="1"']):
                                    current_time = time.time()
                                    last_time = last_motion_time.get(cam_id, 0)
                                    if current_time - last_time >= 10:
                                        print(f"✨ [CAM {cam_id}] DETEKSI PERGERAKAN (Raw)!")
                                        img_name = capture_screenshot(cam_id, snap_url)
                                        report_to_master(cam_id, 'motion', img_name)
                                        last_motion_time[cam_id] = current_time
                                    
                        except Exception as parse_err:
                            print(f"⚠️ [CAM {cam_id}] Deep Parse Error: {parse_err}")
                
                time.sleep(0.5)

        except Exception as e:
            print(f"❌ [CAM {cam_id}] ONVIF Error: {e}")
            print(f"🔄 [CAM {cam_id}] Mencoba ulang dalam 1 menit...")
            time.sleep(60)

            # Loop akan mengulang koneksi dari awal tanpa memakan memori call stack baru

def sync_cameras():
    """Mengambil daftar kamera dan mengupdate thread"""
    try:
        # Ambil daftar kamera dari Master
        api_url = f"{MASTER_URL}/api/node-config?ip={NODE_IP}&token={SYNC_TOKEN}"
        res = requests.get(api_url, timeout=10)
        
        raw_text = res.text.strip()
        if not raw_text.startswith('{'):
            start_index = raw_text.find('{')
            if start_index != -1:
                raw_text = raw_text[start_index:]
        
        data = json.loads(raw_text)
        cameras = data.get('cameras_list', [])

        for cam in cameras:
            cam_id = cam['id']
            if cam_id not in active_threads or not active_threads[cam_id].is_alive():
                print(f"🌟 Memulai thread ONVIF untuk kamera {cam_id} ({cam['ip']})")
                t = threading.Thread(target=subscribe_to_camera, args=(cam,), daemon=True)
                t.start()
                active_threads[cam_id] = t
        return True
    except Exception as e:
        print(f"❌ Sync Error: {e}")
        return False

def main():
    print("🚀 ONVIF Event Agent Started")
    
    # 1. Sync pertama kali
    sync_cameras()

    # 2. Setup Listener Real-time
    try:
        conn = psycopg2.connect(
            host=DB_HOST, port=DB_PORT, dbname=DB_NAME, user=DB_USER, password=DB_PASS
        )
        conn.set_isolation_level(psycopg2.extensions.ISOLATION_LEVEL_AUTOCOMMIT)
        curs = conn.cursor()
        curs.execute("LISTEN cctv_update;")
        print("✅ ONVIF Listener aktif (Real-time). Menunggu sinyal...")
    except Exception as e:
        print(f"⚠️ Gagal memulai Real-time listener: {e}. Menggunakan mode polling 5 menit.")
        while True:
            sync_cameras()
            time.sleep(300)
        return

    while True:
        try:
            conn.poll()
            while conn.notifies:
                notify = conn.notifies.pop(0)
                if notify.payload == NODE_IP or notify.payload == 'ALL':
                    print(f"🔔 NOTIFIKASI DITERIMA: Payload={notify.payload}. Syncing...")
                    sync_cameras()
            time.sleep(1)
        except Exception as e:
            print(f"❌ Main Loop Error: {e}")
            time.sleep(5)

if __name__ == "__main__":
    main()
