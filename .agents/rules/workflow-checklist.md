---
name: cctv-workflow-checklist
description: QA and Workflow Checklist for AI execution
---

# Pedoman Eksekusi & Checklist (CCTV v12)

Dokumen ini berisi daftar centang (checklist) yang wajib diverifikasi oleh AI secara mandiri sebelum melapor kepada *User* bahwa tugas telah selesai. AI dilarang meminta *User* melakukan tugas-tugas di bawah ini jika AI bisa melakukannya sendiri.

## Checklist Wajib Sebelum Mengakhiri Tugas

1. **Jalankan npm run build (Wajib jika UI berubah)**
   - Apakah Anda baru saja menambahkan atau mengubah kelas Tailwind (terutama nilai *arbitrary* seperti `!w-[500px]`)?
   - Apakah Anda memodifikasi komponen Alpine.js di dalam `.blade.php` atau file `.js`?
   - **Tindakan:** Jalankan `npm run build` menggunakan tool `run_command` di *background* dan tunggu hingga selesai sebelum menjawab "Tugas Selesai". Jangan biarkan *User* yang menjalankan perintah ini.

2. **Verifikasi Otorisasi & Middleware**
   - Apakah ada Rute/Controller baru yang dibuat?
   - Pastikan rute tersebut dilindungi *middleware* otentikasi.
   - Pastikan perlindungan akses spesifik (`permission:...`) sudah ditambahkan.
   - Periksa logika Gate di model/Policy jika diperlukan.

3. **Verifikasi Notifikasi Database**
   - Apakah Anda mengubah tabel `cctvs` secara langsung menggunakan *DB facade* atau Eloquent tanpa *event*?
   - Ingat bahwa `NOTIFY cctv_update` sangat krusial untuk daemon streaming. Pastikan notifikasi tersebut tetap ter-trigger.

4. **Verifikasi Activity Log**
   - Setiap fitur mutasi (*insert/update/delete*) pada master data harus memiliki *log*. Pastikan Anda tidak melupakan perekaman ke `activity_logs`.

5. **Respons yang Jelas**
   - Hindari laporan yang bertele-tele. Sebutkan secara singkat apa yang dilakukan, dan beritahu *User* bagian mana yang harus mereka uji secara manual (jika itu butuh interaksi visual, seperti menekan tombol Kiosk).
