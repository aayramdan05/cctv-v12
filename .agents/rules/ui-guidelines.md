---
name: cctv-ui-guidelines
description: Frontend, Tailwind CSS, & Alpine.js Rules for CCTV v12
---

# Pedoman UI & Frontend (CCTV v12)

Dokumen ini berisi pedoman frontend untuk pengembangan antarmuka proyek CCTV Monitoring v12. AI wajib membaca dan mematuhi aturan ini setiap kali melakukan perubahan pada tampilan (UI) atau logika JavaScript di frontend.

## 1. Alpine.js State & DOM Manipulation
- **DILARANG** melakukan manipulasi DOM secara langsung (Vanilla JS `appendChild`, `innerHTML`, `removeChild`) pada elemen yang berada di dalam komponen Alpine.js (`x-data`).
- Memindahkan elemen secara manual di dalam DOM akan merusak *tree* dan mengacaukan *state* Alpine.js (contohnya memicu error `isPlaying is not defined`).
- Gunakan pendekatan reaktif:
  - Gunakan `x-bind:class` atau `:class` untuk mengubah kelas secara dinamis.
  - Gunakan `x-show` atau `x-if` untuk menyembunyikan/menampilkan elemen.
  - Jika memerlukan pergerakan visual (misal elemen harus terlihat pindah wadah), gunakan trik *CSS Positioning* (misalnya membuatnya `fixed` dengan *z-index* tinggi) alih-alih `appendChild`.

## 2. Layout Fullscreen (Live Monitoring)
Proyek ini memiliki **3 cara masuk ke mode layar penuh (fullscreen)** pada halaman Monitoring (`monitoring/index.blade.php`):
1. **Tombol Kiosk Utama:** Membesarkan seluruh kontainer utama (`main`), mempertahankan struktur *grid*.
2. **Tombol Fullscreen pada Slot CCTV:** Masuk ke layar penuh untuk satu kamera secara individu.
3. **Double-Click pada Layar CCTV:** Sama seperti tombol *fullscreen* pada slot.

**ATURAN WAJIB:** 
Setiap kali menambahkan elemen melayang (*floating UI*, misal tombol *timeline*, menu pengaturan, atau *tooltip*), **wajib** dipastikan bahwa elemen tersebut:
- Tetap terlihat (tidak tertimpa `z-index` layar video) pada **KETIGA skenario fullscreen di atas**.
- Solusi yang direkomendasikan untuk fullscreen slot adalah mem-fullscreen `main` namun mengekspansi *slot* menggunakan CSS `fixed inset-0 z-[5000]`, dan memberikan kontrol floating *z-index* lebih tinggi (misal `z-[6000]`), daripada menggunakan fungsi native `.requestFullscreen()` pada elemen slot langsung.

## 3. Tailwind CSS (JIT Compiler)
- Proyek ini menggunakan Tailwind CSS dengan *Just-In-Time* (JIT) compiler via Vite.
- Penggunaan kelas khusus (seperti Arbitrary Values `!z-[6000]`, `h-[calc(100vh-4rem)]`, dll) **TIDAK AKAN MUNCUL DI BROWSER** jika file belum di-build.
- **Setiap kali mengubah file `.blade.php` atau `.js` dengan menambahkan kelas Tailwind baru, wajib menjalankan perintah:**
  `npm run build`
- Jika *build error*, pertimbangkan penggunaan penulisan *style* sebaris statis (`style="..."`) sebagai fallback darurat.

## 4. Estetika dan Desain
- Gunakan warna-warna bawaan Tailwind yang sudah di-*custom* (seperti `slate`, `cyan`).
- Gunakan *glassmorphism* (misal: `bg-slate-900/50 backdrop-blur`) untuk UI yang melayang di atas video.
- Selalu tambahkan efek interaktif seperti *hover* (`hover:bg-cyan-500`) dan transisi (`transition-all duration-300`).
