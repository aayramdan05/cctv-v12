---
name: cctv-architecture
description: Backend & Database Architecture Rules for CCTV v12
---

# Pedoman Arsitektur (CCTV v12)

Dokumen ini berisi pedoman arsitektur untuk pengembangan backend dan database pada proyek CCTV Monitoring v12. AI wajib membaca dan mematuhi aturan ini setiap kali melakukan perubahan kode di backend.

## 1. Otentikasi dan SSO (PAUS ID)
- Proyek ini menggunakan SSO Unpad (PAUS ID) untuk login utama melalui rute `/auth/paus/callback`.
- Pengguna yang masuk pertama kali akan dibuatkan akun otomatis di tabel `users`.
- Jangan ubah alur ini menjadi login lokal (kecuali diminta secara eksplisit). Integrasi ini harus selalu dipertahankan dan hindari bypass authentikasi SSO.

## 2. Role-Based Access Control (RBAC)
- **DILARANG MENGGUNAKAN SPATIE LARAVEL PERMISSION.** Proyek ini memiliki implementasi RBAC kustom.
- Konfigurasi Role disimpan pada tabel/model `RolePermission` dengan primary key `role` berformat string (misalnya `admin`, `operator`).
- Izin (`permissions`) disimpan dalam bentuk kolom `json`/`array`.
- Jika membuat *Controller* baru, pastikan membatasi akses fitur menggunakan metode Gate Policy yang telah disediakan atau *middleware* `permission:nama_izin`.
- **Contoh Middleware:** `Route::middleware(['auth', 'permission:cctv_view'])`
- **Jangan mengecek nama *role* statis** (`if ($user->role == 'admin')`). Role bersifat dinamis, selalu cek kapabilitas dengan fungsi *Gate/policy* bawaan (`Gate::allows('...')`).

## 3. Integrasi Kamera / Multi-Node
- Sinkronisasi dengan *node/streaming server* dilakukan menggunakan **PostgreSQL NOTIFY**.
- Di model `Cctv` (`Cctv.php`), setiap terjadi operasi simpan/ubah (`saved()`), akan dijalankan: `DB::statement("NOTIFY cctv_update, '{$payload}'")`.
- Jika Anda harus membuat *query* massal (`update` massal tanpa memanggil *model events*), pertimbangkan bahwa *event* NOTIFY ini tidak akan ter-trigger dan node server tidak akan tahu ada perubahan. Sebisa mungkin gunakan Eloquent `save()`/`update()` agar *event* berjalan, atau panggil NOTIFY secara manual jika melakukan *bulk query*.

## 4. Activity Logs (Audit Trail)
- Aplikasi memiliki *audit trail* ekstensif yang direkam ke tabel `activity_logs`.
- Jika Anda menambah fungsionalitas baru yang mengubah data krusial (Create, Update, Delete untuk master data atau setelan kamera), **wajib** menyertakan perekaman ke `activity_logs`.
- Model dan fungsi terkait log ini dapat dipelajari dari model `ActivityLog` dan contoh-contoh di dalam *controller* lain (seperti `UserController` atau *model events* di `Cctv`).

## 5. Middleware Kamera Nginx (Satpam)
- Saat node *go2rtc/Nginx* menarik video, mereka akan me-*request* validasi ke endpoint internal (e.g. `/auth-video` atau `/api/node-config`).
- Harap berhati-hati jika memodifikasi endpoint auth/api ini. Pastikan tidak ada kebocoran stream dan tidak merusak skema respons yang diharap oleh script Python node server (`onvif_agent.py` atau `go2rtc` config).
