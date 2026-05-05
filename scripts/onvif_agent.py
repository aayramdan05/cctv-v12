import time
import os
import requests
import threading
from onvif import ONVIFCamera
from datetime import datetime

# CONFIGURATION
DB_HOST = os.getenv('DB_HOST', '127.0.0.1')
NODE_IP = os.getenv('SERVER_RECORDER_IP', '127.0.0.1')
MASTER_URL = os.getenv('MASTER_URL', f"http://{DB_HOST}")
SYNC_TOKEN = os.getenv('SYNC_TOKEN', 'secret_unpad_cctv_2026')

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

    try:
        print(f"🔍 [CAM {cam_id}] Mencoba koneksi ONVIF ke {cam_ip}:{port}...")
        mycam = ONVIFCamera(cam_ip, port, user, password)
        
        # Inisialisasi Event Service
        event_service = mycam.create_events_service()
        properties = event_service.GetEventProperties()
        
        # Membuat PullPoint (Cara paling stabil untuk ambil event)
        pullpoint = mycam.create_pullpoint_service()
        
        print(f"✅ [CAM {cam_id}] Berhasil subscribe ONVIF!")

        while True:
            # Tarik pesan setiap 5 detik
            messages = pullpoint.PullMessages(Timeout='PT5S', MessageLimit=10)
            
            for msg in messages.NotificationMessage:
                # Cek apakah ini Motion Detection
                topic = msg.Topic._value_1
                if 'MotionAlarm' in topic or 'CellMotionDetector' in topic:
                    # Cek nilai boolean motion (IsMotion)
                    try:
                        is_motion = msg.Message.Data.SimpleItem[0].Value
                        if is_motion == 'true' or is_motion == True:
                            report_to_master(cam_id, 'motion')
                    except:
                        # Fallback jika struktur XML berbeda
                        report_to_master(cam_id, 'motion')
            
            time.sleep(1)

    except Exception as e:
        print(f"❌ [CAM {cam_id}] ONVIF Error: {e}")
        print(f"🔄 [CAM {cam_id}] Mencoba ulang dalam 1 menit...")
        time.sleep(60)
        subscribe_to_camera(cam)

def main():
    print("🚀 ONVIF Event Agent Started")
    while True:
        try:
            # Ambil daftar kamera dari Master
            api_url = f"{MASTER_URL}/api/node-config?ip={NODE_IP}&token={SYNC_TOKEN}"
            res = requests.get(api_url, timeout=10)
            cameras = res.json().get('cameras_list', [])

            for cam in cameras:
                # Jalankan listener per kamera di thread terpisah
                t = threading.Thread(target=subscribe_to_camera, args=(cam,), daemon=True)
                t.start()
            
            # Update daftar kamera setiap 1 jam
            time.sleep(3600)
        except Exception as e:
            print(f"❌ Main Loop Error: {e}")
            time.sleep(10)

if __name__ == "__main__":
    main()
