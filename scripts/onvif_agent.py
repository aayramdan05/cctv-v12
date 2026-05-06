import time
import os
import requests
import threading
import json
from onvif import ONVIFCamera
from datetime import datetime
import builtins

# Memaksa print() untuk selalu melakukan flush agar log langsung muncul di systemd
def print(*args, **kwargs):
    kwargs['flush'] = True
    builtins.print(*args, **kwargs)


# CONFIGURATION
DB_HOST = os.getenv('DB_HOST', '127.0.0.1')
NODE_IP = os.getenv('SERVER_RECORDER_IP', '10.69.69.39')
MASTER_URL = os.getenv('MASTER_URL', 'https://cctv.unpad.net')
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
            mycam = ONVIFCamera(cam_ip, port, user, password)
            
            # Inisialisasi Event Service
            event_service = mycam.create_events_service()
            properties = event_service.GetEventProperties()
            
            # Membuat PullPoint (Cara paling stabil untuk ambil event)
            pullpoint = mycam.create_pullpoint_service()
            
            print(f"✅ [CAM {cam_id}] Berhasil subscribe ONVIF!")

            while True:
                # Tarik pesan (Gunakan format yang lebih kompatibel)
                try:
                    # Beberapa kamera butuh timeout dalam format string, beberapa tidak suka keyword 'Timeout'
                    messages = pullpoint.PullMessages({'Timeout': 'PT5S', 'MessageLimit': 10})
                except:
                    # Fallback: coba tanpa argumen jika gagal
                    messages = pullpoint.PullMessages()
                
                if hasattr(messages, 'NotificationMessage'):
                    for msg in messages.NotificationMessage:
                        try:
                            # Cara lebih aman mengambil Topic
                            topic = "Unknown"
                            if hasattr(msg, 'Topic') and msg.Topic is not None:
                                # Coba beberapa kemungkinan lokasi Topic string
                                topic = getattr(msg.Topic, '_value_1', str(msg.Topic))
                            
                            # Deteksi via XML (X-Ray Mode)
                            try:
                                msg_obj = msg.Message._value_1
                                # Ambil semua item di dalam XML Message secara universal
                                for item in msg_obj.xpath('.//tt:SimpleItem', namespaces={'tt': 'http://www.onvif.org/ver10/schema'}):
                                    name = item.get('Name')
                                    value = item.get('Value')
                                    
                                    # Cek Motion atau Tamper
                                    if name in ["IsMotion", "IsTamper"] and (value == "true" or value == "1"):
                                        current_time = time.time()
                                        last_time = last_motion_time.get(cam_id, 0)
                                        # COOLDOWN 10 DETIK
                                        if current_time - last_time >= 10:
                                            print(f"✨ [CAM {cam_id}] DETEKSI PERGERAKAN!")
                                            report_to_master(cam_id, 'motion')
                                            last_motion_time[cam_id] = current_time
                            except:
                                # Jika bukan objek XML, fallback ke deteksi teks mentah
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
            # Loop akan mengulang koneksi dari awal tanpa memakan memori call stack baru

def main():
    print("🚀 ONVIF Event Agent Started")
    while True:
        try:
            # Ambil daftar kamera dari Master
            api_url = f"{MASTER_URL}/api/node-config?ip={NODE_IP}&token={SYNC_TOKEN}"
            res = requests.get(api_url, timeout=10)
            
            # Bersihkan response jika ada teks sampah di awal/akhir
            raw_text = res.text.strip()
            
            # Jika response diawali dengan 'OK' atau teks lain, coba ambil bagian JSON-nya
            if not raw_text.startswith('{'):
                start_index = raw_text.find('{')
                if start_index != -1:
                    raw_text = raw_text[start_index:]
            
            try:
                data = json.loads(raw_text)
                cameras = data.get('cameras_list', [])
            except Exception as je:
                print(f"❌ JSON Decode Error: {je}")
                print(f"📄 Raw Response: {res.text[:100]}...")
                time.sleep(10)
                continue

            for cam in cameras:
                cam_id = cam['id']
                # Jalankan listener per kamera di thread terpisah JIKA belum jalan
                if cam_id not in active_threads or not active_threads[cam_id].is_alive():
                    print(f"🌟 Memulai thread baru untuk kamera {cam_id}")
                    t = threading.Thread(target=subscribe_to_camera, args=(cam,), daemon=True)
                    t.start()
                    active_threads[cam_id] = t
            
            # Update daftar kamera setiap 1 jam
            time.sleep(3600)
        except Exception as e:
            print(f"❌ Main Loop Error: {e}")
            time.sleep(10)

if __name__ == "__main__":
    main()
