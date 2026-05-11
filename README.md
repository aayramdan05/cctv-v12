# 🏢 CCTV Multi-Node Infrastructure Setup (UNPAD)

Panduan ini menjelaskan cara membagi beban CCTV ke beberapa server Node (Distributed Recording).

---

## 🖥️ BAGIAN 1: INSTALASI SERVER MASTER (10.69.69.21)

### 1. Ambil Source Code dari GitHub
```bash
git clone https://github.com/aayramdan05/cctv-v12.git
cd cctv-v12
composer install
npm install && npm run build
cp .env.example .env # Jangan lupa atur koneksi DB dan APP_URL
php artisan key:generate
php artisan migrate
```

### 2. Izinkan Akses Database untuk Node
Node butuh konek ke DB Master.
- Edit `/etc/postgresql/XX/main/postgresql.conf`:
  ```ini
  listen_addresses = '*'
  ```
- Edit `/etc/postgresql/XX/main/pg_hba.conf`, tambahkan IP Node baru:
  ```text
  host    cctv_prod       postgres        10.69.69.41/32          md5
  ```
- Buka Firewall: `sudo ufw allow from 10.69.69.41 to any port 5432`
- Restart Postgres: `sudo systemctl restart postgresql`

---

## 🚀 BAGIAN 2: INSTALASI SERVER NODE (10.69.69.41)

### 1. Ambil Folder Scripts via GitHub (Sparse Checkout)
Agar server Node tidak mendownload seluruh project Laravel (hanya butuh folder scripts), gunakan cara ini:
```bash
mkdir -p /home/aay/cctv-scripts
cd /home/aay/cctv-scripts
git init
git remote add -f origin https://github.com/aayramdan05/cctv-v12.git
git config core.sparseCheckout true
echo "scripts/" >> .git/info/sparse-checkout
git pull origin main
```

### 2. Jalankan Installer Otomatis
```bash
cd /home/aay/cctv-scripts/scripts
chmod +x install_node.sh
sudo ./install_node.sh
```
*Catatan: Isi IP Master `10.69.69.21` dan IP Node ini `10.69.69.41` saat ditanya.*

### 3. Penyesuaian Path & Izin Akses (Wajib)
Ubah folder rekaman ke `/var/www/html` agar stabil:
```bash
sudo mkdir -p /var/www/html/storage/recordings
sudo chown -R aay:www-data /var/www/html/storage
sudo chmod -R 775 /var/www/html/storage
```
- Pastikan `STORAGE_BASE_PATH` di `sync_node.py` adalah `/var/www/html/storage/recordings`.
- Pastikan Nginx Node mengarah ke `/var/www/html/storage/`.

### 4. Jalankan Service
```bash
sudo systemctl daemon-reload
sudo systemctl restart go2rtc cctv-sync cctv-onvif
```

---

## 🔔 FITUR REAL-TIME SYNC
Sistem ini menggunakan **Postgres LISTEN/NOTIFY**.
- **Master:** Mengirim NOTIFY via `Cctv` Model.
- **Node:** Script Python melakukan LISTEN dan langsung update saat ada perubahan.

---

## 🔍 MONITORING
- `sudo journalctl -u cctv-sync -f` (Log Sinkronisasi & Perekam)
- `sudo journalctl -u cctv-onvif -f` (Log Deteksi Gerakan)
