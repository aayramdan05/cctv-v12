# UNPAD Enterprise CCTV Monitoring System

Sistem monitoring CCTV berbasis web yang dirancang untuk skala besar menggunakan arsitektur **Hybrid Multi-Node**. Sistem ini memisahkan antara server kontrol (Master) dan server perekam (Node) untuk efisiensi bandwidth dan skalabilitas.

## 🚀 Arsitektur Sistem

Sistem ini terdiri dari dua komponen utama:

1.  **Master Server (Laravel)**:
    *   Berfungsi sebagai pusat antarmuka pengguna (UI).
    *   Mengelola database pusat (PostgreSQL) untuk metadata CCTV dan rekaman.
    *   Mengatur otentikasi dan izin akses video menggunakan Nginx `auth_request`.
2.  **Recording Node (Python Agent)**:
    *   Server terdistribusi yang melakukan penarikan *stream* dari kamera fisik.
    *   Mengelola *live streaming* via WebRTC/HLS menggunakan **Go2RTC**.
    *   Melakukan perekaman otomatis ke storage lokal dalam potongan 15 menit (.mp4).
    *   Mensinkronisasikan metadata rekaman ke Database Master secara *real-time*.

---

## 🛠️ Instalasi Master Server

### 1. Persyaratan Sistem
*   PHP 8.2+ & Composer
*   PostgreSQL 14+
*   Node.js & NPM
*   Nginx (dengan modul `libnginx-mod-http-auth-request`)

### 2. Langkah Instalasi
```bash
# Clone repository
git clone <repository-url>
cd cctv-v12

# Install dependensi
composer install
npm install

# Konfigurasi Environment
cp .env.example .env
php artisan key:generate

# Database Migration
php artisan migrate
```

### 3. Konfigurasi Nginx Master
Pastikan Nginx Master memiliki konfigurasi *reverse proxy* untuk mengarahkan permintaan ke Node-node pendukung. Contoh untuk Node 2:
```nginx
location /node2/storage/ {
    auth_request /auth-video;
    rewrite ^/node2/(.*) /$1 break;
    proxy_pass http://<IP_NODE_2>:80;
}

location /node2/ {
    auth_request /auth-video;
    rewrite ^/node2/(.*) /$1 break;
    proxy_pass http://<IP_NODE_2>:80;
}
```

---

## 📹 Instalasi Recording Node (Remote Node)

Setiap server yang dijadikan node perekam harus dikonfigurasi sebagai berikut:

### 1. Persyaratan Node
*   Python 3.10+
*   FFmpeg (Terinstal di System Path)
*   Go2RTC (Binary)
*   Nginx (Untuk menyajikan file statis rekaman)

### 2. Setup Folder & Izin Akses
Penting agar Nginx bisa membaca file video di folder user:
```bash
mkdir -p ~/storage/recordings
chmod o+x /home/<user>
chmod -R 755 ~/storage
```

### 3. Konfigurasi Nginx Node (Port 80)
```nginx
server {
    listen 80;
    
    location /storage/ {
        root /home/<user>/;
        add_header Access-Control-Allow-Origin *;
        add_header Accept-Ranges bytes;
        max_ranges 100;
    }

    location / {
        proxy_pass http://127.0.0.1:1984; # Go2RTC API
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
```

### 4. Setup Python Agent (`sync_node.py`)
Install dependensi python:
```bash
pip install psycopg2-binary pyyaml python-dotenv
```

Buat file `.env` di folder script:
```env
DB_HOST=10.69.69.21 (IP Master)
DB_DATABASE=cctv_prod
DB_USERNAME=postgres
DB_PASSWORD=your_password
SERVER_RECORDER_IP=10.69.69.39 (IP Node ini)
RETENTION_DAYS=2
```

### 5. Menjalankan Sebagai Service (Systemd)
Buat file `/etc/systemd/system/cctv-sync.service`:
```ini
[Unit]
Description=CCTV Node Agent
After=network.target

[Service]
ExecStart=/usr/bin/python3 /home/<user>/cctv-scripts/sync_node.py
WorkingDirectory=/home/<user>/cctv-scripts
User=<user>
Restart=always

[Install]
WantedBy=multi-user.target
```
Aktifkan service:
```bash
sudo systemctl enable --now cctv-sync
```

---

## 💡 Fitur Utama UI
*   **Grid View**: Pilihan tampilan 1, 4, atau 9 kamera sekaligus.
*   **Timeline Playback**: Klik pada balok hijau untuk memutar ulang rekaman di jam tersebut.
*   **Digital Zoom**: Gunakan *scroll wheel* mouse atau menu zoom untuk memperbesar area video.
*   **Mobile Optimized**: Tampilan list kamera di HP yang luas dan *native-like*.

---

## 📝 Catatan Penting
*   **Sinkronisasi Waktu**: Pastikan semua Node memiliki waktu yang sinkron dengan Master (gunakan NTP) agar timeline rekaman akurat.
*   **Retention**: Script secara otomatis menghapus rekaman fisik dan data di DB setelah melewati batas hari yang ditentukan di `.env`.

---
© 2026 UNPAD CCTV Infrastructure Team
