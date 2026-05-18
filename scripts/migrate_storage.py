import os
import shutil
import psycopg2
import re
from dotenv import load_dotenv
from datetime import datetime

# Load Config (Sama seperti sync_node.py)
load_dotenv()

DB_HOST = os.getenv('DB_HOST', 'localhost')
DB_NAME = os.getenv('DB_NAME', 'cctv_prod')
DB_USER = os.getenv('DB_USER', 'aay')
DB_PASS = os.getenv('DB_PASS', 'admcctvD@pnu1957')
STORAGE_BASE_PATH = os.getenv('STORAGE_BASE_PATH', '/var/www/html/storage/recordings')

import time

def get_db_connection():
    """Mencoba koneksi ke database dengan fitur Retry jika slot penuh"""
    while True:
        try:
            conn = psycopg2.connect(host=DB_HOST, dbname=DB_NAME, user=DB_USER, password=DB_PASS)
            return conn
        except psycopg2.OperationalError as e:
            if "slots are reserved" in str(e) or "too many clients" in str(e):
                print("⚠️ Database penuh! Menunggu 5 detik untuk mencoba lagi...", flush=True)
                time.sleep(5)
            else:
                raise e

def migrate():
    print(f"🚀 Memulai migrasi folder di {STORAGE_BASE_PATH}...")
    
    # Ambil folder tanggal (hanya folder, bukan file)
    date_folders = [f for f in os.listdir(STORAGE_BASE_PATH) if os.path.isdir(os.path.join(STORAGE_BASE_PATH, f))]
    
    conn = get_db_connection()
    curs = conn.cursor()
    
    total_moved = 0
    
    for date_folder in date_folders:
        # Kita fokus ke tanggal 12 dan 13 Mei 2026
        if date_folder not in ['2026-05-12', '2026-05-13']:
            continue
            
        date_path = os.path.join(STORAGE_BASE_PATH, date_folder)
        print(f"📂 Memproses folder: {date_folder}")
        
        # Ambil semua file mp4 yang ada LANGSUNG di bawah folder tanggal
        files = [f for f in os.listdir(date_path) if f.endswith('.mp4')]
        
        for filename in files:
            # Format filename: cam_ID_TIMESTAMP.mp4
            match = re.match(r'cam_(\d+)_', filename)
            if match:
                cam_id = match.group(1)
                
                # Path lama dan path baru
                old_path = os.path.join(date_path, filename)
                cam_folder = os.path.join(date_path, f"cam_{cam_id}")
                new_path = os.path.join(cam_folder, filename)
                
                # Buat sub-folder kamera
                os.makedirs(cam_folder, exist_ok=True)
                
                try:
                    # 1. Pindahkan file fisik
                    shutil.move(old_path, new_path)
                    
                    # 2. Update Database Master
                    curs.execute(
                        "UPDATE recordings SET filepath = %s WHERE filename = %s",
                        (new_path, filename)
                    )
                    
                    total_moved += 1
                    if total_moved % 50 == 0:
                        print(f"✅ Sudah memindahkan {total_moved} file...")
                        conn.commit() # Commit bertahap
                        
                except Exception as e:
                    print(f"❌ Gagal memindahkan {filename}: {e}")
    
    conn.commit()
    curs.close()
    conn.close()
    print(f"🏁 MIGRASI SELESAI! Total {total_moved} file berhasil dirapikan ke sub-folder kamera.")

if __name__ == "__main__":
    migrate()
