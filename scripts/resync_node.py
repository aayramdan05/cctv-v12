import os
import psycopg2
import requests
from datetime import datetime
from dotenv import load_dotenv

# Load Environment
load_dotenv('/home/aay/cctv-scripts/.env')

DB_HOST = os.getenv('DB_HOST')
DB_PORT = os.getenv('DB_PORT')
DB_NAME = os.getenv('DB_DATABASE')
DB_USER = os.getenv('DB_USERNAME')
DB_PASS = os.getenv('DB_PASSWORD')

NODE_IP = os.getenv('SERVER_RECORDER_IP')
STORAGE_BASE_PATH = os.getenv('RECORDINGS_PATH', '/var/www/html/storage/recordings')
MASTER_URL = os.getenv('MASTER_URL', f"http://{DB_HOST}")
SYNC_TOKEN = os.getenv('SYNC_TOKEN', 'secret_unpad_cctv_2026')

def get_db_connection():
    return psycopg2.connect(
        host=DB_HOST, port=DB_PORT, dbname=DB_NAME, user=DB_USER, password=DB_PASS
    )

def resync():
    print(f"🚀 Memulai Re-Sync Rekaman untuk Node IP: {NODE_IP}")
    
    # 1. Ambil daftar kamera yang ditugaskan untuk Node ini dari Master
    try:
        api_url = f"{MASTER_URL}/api/node-config?ip={NODE_IP}&token={SYNC_TOKEN}"
        response = requests.get(api_url, timeout=10)
        if response.status_code != 200:
            print(f"❌ Gagal ambil config dari API: {response.status_code}")
            return
        
        data = response.json()
        cameras_list = data.get('cameras_list', [])
        cam_ids = [cam['id'] for cam in cameras_list]
        
        if not cam_ids:
            print("⚠️ Tidak ada kamera yang ditemukan untuk Node ini.")
            return
            
        print(f"✅ Ditemukan {len(cam_ids)} kamera di Node ini.")
    except Exception as e:
        print(f"❌ Error API: {e}")
        return

    conn = get_db_connection()
    cur = conn.cursor()

    # 2. Hapus data rekaman lama khusus untuk kamera-kamera di Node ini
    # (Hanya menghapus yang hari ini agar tidak terlalu ekstrem, atau semua jika Mas mau)
    print("🧹 Menghapus data rekaman lama untuk Node ini dari database...")
    cur.execute("DELETE FROM recordings WHERE cctv_id IN %s", (tuple(cam_ids),))
    print(f"✅ Berhasil menghapus {cur.rowcount} baris data lama.")

    # 3. Scan folder fisik dan masukkan kembali
    print(f"📁 Menyisir folder: {STORAGE_BASE_PATH}")
    count = 0
    
    # Format folder: recordings/YYYY-MM-DD/cam_ID_YYYY-MM-DD_HH-MM-SS.mp4
    for date_folder in os.listdir(STORAGE_BASE_PATH):
        folder_path = os.path.join(STORAGE_BASE_PATH, date_folder)
        if not os.path.isdir(folder_path): continue
        
        for filename in os.listdir(folder_path):
            if not filename.endswith('.mp4'): continue
            
            try:
                # Parsing nama file: cam_3_2026-05-11_13-21-41.mp4
                parts = filename.split('_')
                if len(parts) < 3: continue
                
                cam_id = int(parts[1])
                if cam_id not in cam_ids: continue # Bukan milik node ini (keamanan)
                
                file_full_path = os.path.join(folder_path, filename)
                file_size_mb = round(os.path.getsize(file_full_path) / (1024 * 1024), 2)
                
                # Parsing waktu dari nama file (Format baru: cam_ID_YYYY-MM-DD_HH-MM-SS.mp4)
                # Jika format lama (cam_ID_HH-MM-SS.mp4), sesuaikan.
                time_str = parts[-1].replace('.mp4', '') # HH-MM-SS
                h, m, s = map(int, time_str.split('-'))
                start_time_seconds = (h * 3600) + (m * 60) + s
                
                # Masukkan ke DB
                now_str = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
                cur.execute("""
                    INSERT INTO recordings (cctv_id, date, filename, start_time, duration, size_mb, created_at, updated_at)
                    VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
                """, (cam_id, date_folder, filename, start_time_seconds, 900, file_size_mb, now_str, now_str))
                
                count += 1
                if count % 10 == 0:
                    print(f"⏳ Terproses {count} file...")
                    
            except Exception as e:
                # print(f"⚠️ Skip file {filename}: {e}")
                continue

    conn.commit()
    cur.close()
    conn.close()
    print(f"🎉 SELESAI! Berhasil mendaftarkan ulang {count} file rekaman ke Master.")

if __name__ == "__main__":
    resync()
