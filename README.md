# 🛡️ UNPAD Multi-Node CCTV System (v12)

Sistem Monitoring CCTV Skala Besar berbasis Multi-Node untuk lingkungan Universitas Padjadjaran.

## 🏗️ Arsitektur Sistem
1.  **Master Server (Laravel 10)**:
    *   Pusat kendali, manajemen user, manajemen kamera, dan monitoring dashboard.
    *   Menyediakan API untuk konfigurasi Node dan pelaporan event.
    *   Menjalankan **Queue Worker** untuk proses export rekaman yang berat.

2.  **Recording Node (Python Agent)**:
    *   Server terdistribusi yang melakukan penarikan *stream* dari kamera fisik.
    *   Mengelola *live streaming* via WebRTC/HLS menggunakan **Go2RTC**.
    *   Melakukan perekaman otomatis ke storage lokal dalam potongan 15 menit (.mp4).
    *   Mensinkronisasikan metadata rekaman ke Database Master secara *real-time*.

## 🧠 ONVIF Event Intelligence
Sistem ini sekarang dilengkapi dengan kemampuan deteksi pergerakan (Motion Detection) berbasis protokol ONVIF yang bekerja secara real-time.

### Fitur Utama:
- **Real-time Detection**: Node Agent berlangganan ke Event Service kamera dan melaporkan pergerakan seketika.
- **Smart Timeline**: Halaman Live Monitoring menampilkan warna **Orange** pada blok rekaman jika terdeteksi ada pergerakan.
- **Event History**: Log riwayat kejadian lengkap dengan lokasi dan waktu untuk kebutuhan audit.

### Setup Node Agent (onvif_agent.py):
1. Install dependensi di Node: `pip install zeep onvif-zeep`
2. Konfigurasi `.env` di Node (MASTER_URL, SYNC_TOKEN).
3. Jalankan Agent sebagai background service menggunakan Systemd (`/etc/systemd/system/cctv-onvif.service`).

---

## ⚙️ Cara Menambah Kamera Baru
1. Masuk ke Dashboard Master.
2. Pilih menu **Master Kamera** -> **Tambah Kamera**.
3. Pilih **Server Node** tujuan.
4. Masukkan URL RTSP dan kredensial ONVIF.
5. Klik Simpan.

## 🛠️ Pengembangan
- **Web Interface**: Tailwind CSS + Alpine.js
- **Backend**: Laravel 10 (PHP 8.2)
- **Database**: MySQL / MariaDB
- **Recording Engine**: FFmpeg + Python
