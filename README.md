# 📹 CCTV Monitoring System (Unpad v12)

Sebuah sistem manajemen, monitoring, dan analisis CCTV tingkat lanjut berbasis arsitektur terdistribusi (Distributed Nodes). Sistem ini dirancang untuk menangani ratusan IP Camera dengan pengelolaan *storage* yang dipisah antar-node, pelacakan deteksi gerakan (Motion Detection) via ONVIF, dan fitur *slicing* & *export* video cerdas via HTTP.

## 🌟 Fitur Utama

### 1. Distributed Architecture (Master-Node System)
*   **Master Server:** Bertugas menyajikan Web Dashboard (UI), menangani autentikasi, mengatur antrean (Queue Worker), dan memonitor infrastruktur. Master **tidak menyimpan** file rekaman video secara fisik.
*   **Node Server:** Bertugas melakukan rekam *live stream* ke Harddisk lokal dan menyediakan titik akhir (Nginx proxy) agar Master bisa "menarik" file video kapan saja.

### 2. Smart Export Video (FFmpeg over HTTP)
Fitur export video (`ProcessRecordingExport`) memungkinkan Master Server untuk mengekstrak potongan video secara akurat.
*   Tidak menggunakan *NFS Mount*.
*   Master akan melakukan *query* ke database, mengonstruksi URL langsung ke Node IP (menghindari blokir HTTPS internal).
*   FFmpeg menggunakan fitur *smart seek* (`-ss`) via HTTP untuk mengunduh, memotong, dan menyambungkan (Concat Demuxer) rekaman menjadi satu file `.mp4` secara utuh tanpa proses *re-render* yang memberatkan CPU (`-c copy`).

### 3. Real-Time Motion Detection (ONVIF Agent)
Script Python khusus (`onvif_agent.py`) berjalan sebagai *background service* (Systemd) di Node.
*   Mem-parsing event `PullPoint` ONVIF dari IP Camera.
*   Fitur Anti-Memory Leak: Melacak thread aktif sehingga tidak terjadi tumpang tindih thread kamera.
*   Fitur Anti-Spam: Memiliki **cooldown 10 detik** per kamera untuk mencegah *flooding* database di Master Server jika terjadi gerakan terus-menerus.
*   Di frontend, gerakan-gerakan ini dirender pada *timeline* sebagai "stripe" orange yang akurat berdasarkan detiknya.

### 4. Infrastructure Monitoring
Terintegrasi secara penuh dengan tumpukan **Prometheus & Grafana** menggunakan Docker Compose.
*   Memantau metrik krusial seperti CPU I/O Wait (karena tingginya beban tulis Harddisk CCTV), RAM, Network, dan Storage pada seluruh Node secara real-time.

### 5. Role-Based Access Control
Hak akses berjenjang mulai dari:
*   **Admin / Operator Pusat:** Mengakses semua kamera dan fitur penuh.
*   **Operator Fakultas:** Hanya mengakses kamera pada gedungnya.
*   **User / API Viewer:** Akses terbatas hanya pada kamera yang di-*assign* kepadanya.

---

## 🛠 Instalasi & Setup

### A. Setup Master Server (Web Laravel)
1. Clone repositori ini.
2. Jalankan `composer install` & setup `.env`.
3. Jalankan migrasi: `php artisan migrate`.
4. Untuk menyalakan fitur Export, nyalakan Queue Worker (sangat disarankan via Systemd):
   ```bash
   sudo systemctl enable --now laravel-worker
   ```

### B. Setup Node Server (Perekam & ONVIF Agent)
1. Pastikan server Nginx berjalan pada port 80 dan mengarah ke folder penyimpanan rekaman.
2. Install Python dan jalankan ONVIF Agent:
   ```bash
   python3 -m venv venv
   source venv/bin/activate
   pip install zeep onvif_zeep requests
   ```
3. Konfigurasi `onvif_agent.py` dan pasang sebagai service systemd `cctv-onvif.service`.

### C. Setup Infrastructure Monitoring (Docker)
Di dalam folder `docker-monitoring/` terdapat file konfigurasi *Docker Compose*.
*   **Di Master:** Jalankan `docker compose up -d` untuk menjalankan Prometheus dan Grafana.
*   **Di tiap Node:** Jalankan file `docker-compose.node.yml` untuk menghidupkan *Node Exporter*.
*   Akses Grafana di port `3000` dan import Dashboard Node Exporter Full (ID `1860`).

---

## ⚙️ Komponen & Command Penting

*   **Menyinkronkan Rekaman Lama:**
    Jika ada folder video lama di Node yang belum masuk database, gunakan script artisan:
    ```bash
    php artisan cctv:sync-db
    ```
*   **Mengecek Log ONVIF Agent:**
    ```bash
    tail -f /home/aay/cctv-scripts/onvif_agent.log
    # Atau jika menggunakan systemd:
    sudo journalctl -u cctv-onvif -f
    ```
*   **Mengecek Log Worker Export:**
    ```bash
    tail -f storage/logs/laravel.log
    sudo systemctl status laravel-worker
    ```

---
*CCTV Monitoring System - Built with Laravel & Python ONVIF*
