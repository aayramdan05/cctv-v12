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
load_dotenv('/home/aay/cctv-scripts/.env')

# Memaksa print() untuk selalu melakukan flush agar log langsung muncul di systemd
def print(*args, **kwargs):
    kwargs['flush'] = True
    builtins.print(*args, **kwargs)


# CONFIGURATION
DB_HOST = os.getenv('DB_HOST', '127.0.0.1')
DB_PORT = os.getenv('DB_PORT', '5432')
DB_NAME = os.getenv('DB_DATABASE', 'cctv_prod')
DB_USER = os.getenv('DB_USERNAME', 'postgres')
DB_PASS = os.getenv('DB_PASSWORD', '')

NODE_IP = os.getenv('SERVER_RECORDER_IP', '10.69.69.41')
MASTER_URL = os.getenv('MASTER_URL', 'http://10.69.69.21')
SYNC_TOKEN = os.getenv('SYNC_TOKEN', 'secret_unpad_cctv_2026')

# Melacak thread yang sedang berjalan
active_threads = {}

# Melacak waktu terakhir pergerakan terdeteksi per kamera (untuk cooldown)
last_motion_time = {}

def report_to_master(cctv_id, event_type):
    """Melaporkan kejadian ke Master Server"""
    try:
        url = f"{MASTER_URL}/api/report-event?cctv_id={cctv_id}&type={event_type}&token={SYNC_TOKEN}"
        requests.get(url, timeout=5)
        print(f"🔔 [CAM {cctv_id}] Event {event_type} dilaporkan ke Master!")
    except Exception as e:
        print(f"❌ Gagal lapor event: {e}")

def subscribe_to_camera(cam):
    """Berlangganan event dari kamera via ONVIF"""
    cam_id = cam['id']
    cam_ip = cam['ip']
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
            
            import sys
            import onvif
            wsdl_dirs = [
                '/usr/local/wsdl',
                '/usr/wsdl',
                os.path.join(sys.prefix, 'wsdl'),
                os.path.join(os.path.dirname(onvif.__file__), 'wsdl'),
                os.path.join(os.path.dirname(onvif.__file__), '..', 'wsdl'),
                '/usr/local/lib/python3.13/dist-packages/wsdl'
            ]
            valid_wsdl_dir = None
            for d in wsdl_dirs:
                if os.path.exists(os.path.join(d, 'devicemgmt.wsdl')):
                    valid_wsdl_dir = d
                    break
                    
            if valid_wsdl_dir:
                mycam = ONVIFCamera(cam_ip, port, user, password, wsdl_dir=valid_wsdl_dir)
            else:
                mycam = ONVIFCamera(cam_ip, port, user, password)
            
            # Inisialisasi Event Service
            event_service = mycam.create_events_service()
            
            # Membuat PullPoint (Cara paling stabil untuk ambil event)
            pullpoint = mycam.create_pullpoint_service()
            
            print(f"✅ [CAM {cam_id}] Berhasil subscribe ONVIF!")

            while True:
                # Tarik pesan
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
                                            report_to_master(cam_id, 'motion')
                                            last_motion_time[cam_id] = current_time
                            except:
                                # Fallback deteksi teks mentah
                                raw_msg = str(msg)
                                if any(x in raw_msg for x in ['IsMotion="true"', 'Value="true"', 'IsMotion="1"']):
                                    current_time = time.time()
                                    last_time = last_motion_time.get(cam_id, 0)
                                    if current_time - last_time >= 10:
                                        print(f"✨ [CAM {cam_id}] DETEKSI PERGERAKAN (Raw)!")
                                        report_to_master(cam_id, 'motion')
                                        last_motion_time[cam_id] = current_time
                                    
                        except Exception as parse_err:
                            print(f"⚠️ [CAM {cam_id}] Deep Parse Error: {parse_err}")
                
                time.sleep(0.5)

        except Exception as e:
            print(f"❌ [CAM {cam_id}] ONVIF Error: {e}")
            print(f"🔄 [CAM {cam_id}] Mencoba ulang dalam 1 menit...")
            time.sleep(60)

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

    # 2. Setup Listener Real-time dengan auto-reconnect
    conn = None
    while True:
        try:
            if conn is None or conn.closed:
                print("🔌 Menghubungkan ke database...")
                conn = psycopg2.connect(
                    host=DB_HOST, port=DB_PORT, dbname=DB_NAME, user=DB_USER, password=DB_PASS
                )
                conn.set_isolation_level(psycopg2.extensions.ISOLATION_LEVEL_AUTOCOMMIT)
                curs = conn.cursor()
                curs.execute("LISTEN cctv_update;")
                print("✅ ONVIF Listener aktif (Real-time). Menunggu sinyal...")
            
            conn.poll()
            while conn.notifies:
                notify = conn.notifies.pop(0)
                if notify.payload == NODE_IP or notify.payload == 'ALL':
                    print(f"🔔 NOTIFIKASI DITERIMA: Payload={notify.payload}. Syncing...")
                    sync_cameras()
            time.sleep(1)
        except Exception as e:
            print(f"❌ Main Loop Error: {e}")
            if conn:
                try:
                    conn.close()
                except Exception:
                    pass
                conn = None
            print("🔄 Mencoba menghubungkan kembali ke database dalam 5 detik...")
            time.sleep(5)

if __name__ == "__main__":
    main()
