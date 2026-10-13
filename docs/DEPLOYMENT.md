# 🚀 Deployment Guide: CCTV Monitoring System (Unpad)

Dokumen ini berisi panduan teknis mendalam untuk men-deploy aplikasi **CCTV Monitoring System** yang menggunakan arsitektur *Distributed Nodes*. Sistem ini terdiri dari satu **Server Master** (Dashboard Web) dan beberapa **Server Node** (Agen Perekam & Streaming).

---

## 1. Topologi Deployment & Requirement

- **Server Master (Laravel):**
  - **OS:** Debian 12 (Bookworm)
  - **Database:** PostgreSQL 15/16 (Wajib, karena fitur `LISTEN/NOTIFY` bergantung pada Postgres).
  - **Runtime:** PHP 8.2+, Node.js (untuk kompilasi aset), Composer, Nginx.
  
- **Server Node / Fakultas (Python Agents):**
  - **OS:** Debian 12 (Bookworm)
  - **Engine:** Python 3.10+, FFmpeg (Wajib untuk *video chunking*).
  - **Streaming Server:** Go2RTC (WebRTC / RTSP server).
  - **Storage:** Kapasitas disk besar yang di-*mount* ke `/var/www/html/storage/recordings` atau via NFS.

---

## 2. Instalasi Server Master (Dashboard & DB)

### Langkah Awal
Clone repositori utama ke Server Master:
```bash
git clone https://github.com/aayramdan05/cctv-v12.git /var/www/cctv-master
cd /var/www/cctv-master
composer install --optimize-autoloader --no-dev
npm install && npm run build
cp .env.example .env
```

### Konfigurasi `.env`
Pastikan pengaturan database diisi dengan benar. Sangat penting bagi Server Master untuk tidak menggunakan database SQLite karena Node Agent akan terkoneksi langsung ke database ini.
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cctv_db
DB_USERNAME=postgres
DB_PASSWORD=rahasia
```
Setelah `.env` disesuaikan, jalankan migrasi:
```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

### Membuka Akses PostgreSQL untuk Server Node
Karena script Python (`sync_node.py`) di Server Node akan melakukan *listen* terhadap *event* dari database Master, port Postgres Master harus dibuka ke luar.
1. Buka file `/etc/postgresql/15/main/postgresql.conf`:
   ```ini
   listen_addresses = '*'
   ```
2. Buka file `/etc/postgresql/15/main/pg_hba.conf` dan daftarkan IP Node (Misalnya 10.69.69.41):
   ```text
   host    cctv_db       postgres        10.69.69.41/32          md5
   ```
3. Restart Postgres: `sudo systemctl restart postgresql`

---

## 3. Instalasi Server Node (Python Agents & FFmpeg)

Server Node bertugas mengambil *stream* kamera RTSP via FFmpeg dan menyajikannya via WebRTC (Go2RTC). Server Node tidak menjalankan Laravel, melainkan *script Python* di dalam folder `scripts/`.

### Persiapan Node
```bash
mkdir -p /home/aay/cctv-scripts
cd /home/aay/cctv-scripts
# Mengambil hanya folder /scripts/ dari repository GitHub
git init
git remote add -f origin https://github.com/aayramdan05/cctv-v12.git
git config core.sparseCheckout true
echo "scripts/" >> .git/info/sparse-checkout
git pull origin main
```

### Menjalankan Instalasi Otomatis
Di dalam folder `scripts/`, terdapat `install_node.sh` yang akan memasang FFmpeg, Go2RTC, dependensi Python (psycopg2, yaml, requests), serta membuat service `systemd`.
```bash
cd scripts
chmod +x install_node.sh
sudo ./install_node.sh
```
*Selama proses instalasi, script akan meminta Anda memasukkan IP Server Master (contoh: 10.69.69.21) dan IP Server Node ini (contoh: 10.69.69.41).*

### Konfigurasi Folder Storage Perekaman
Kamera akan menyimpan rekaman (*chunk* MP4 per 15 menit). Folder rekaman ini perlu bisa diakses publik (lewat Nginx) agar Dashboard Master bisa memutar ulang (*playback*).
```bash
sudo mkdir -p /var/www/html/storage/recordings
sudo chown -R aay:www-data /var/www/html/storage
sudo chmod -R 775 /var/www/html/storage
```
*Pastikan variabel `STORAGE_BASE_PATH` di file `.env` agen menunjuk ke path tersebut.*

### Menjalankan Agen
```bash
sudo systemctl daemon-reload
# Menjalankan agen sinkronisasi (merekam video dan listen Postgres API)
sudo systemctl enable --now cctv-sync
# Menjalankan agen ONVIF (untuk deteksi motion & diagnostik kamera)
sudo systemctl enable --now cctv-onvif
```

---

## 4. Maintenance & Monitoring

- **Restarting Sync Agent:** Jika Anda mengubah variabel `.env` di Node, lakukan:
  `sudo systemctl restart cctv-sync`
- **Melihat Log Real-time Sync:**
  `sudo journalctl -u cctv-sync -f`
- **Troubleshooting Database Listen:** Pastikan pada log `cctv-sync` tertulis `✅ LISTENER AKTIF. Menunggu sinyal real-time...`. Jika error, periksa *firewall* Postgres di Server Master (Port 5432).
- **Auto-Cleanup:** Node agent (`sync_node.py`) memiliki *thread background* yang otomatis menghapus folder rekaman lama (`/var/www/html/storage/recordings/YYYY-MM-DD`) berdasarkan pengaturan masa retensi (`retention_days`) di Dashboard Master.

---
*Dokumen ini merupakan "Living Documentation" yang harus selalu diperbarui selaras dengan perubahan arsitektur aplikasi.*
