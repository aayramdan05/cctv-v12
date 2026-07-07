<x-app-layout>
    <!-- Tambahkan SweetAlert2 & AnimateCSS untuk UI interaktif -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <main id="main-content" class="pt-20 p-6 md:p-8 min-h-screen bg-slate-50" x-data="{
        activeRole: 'admin',
        searchQuery: '',
        
        // Cek kecocokan pencarian izin
        matchesQuery(permKey, desc) {
            if (!this.searchQuery) return true;
            const q = this.searchQuery.toLowerCase();
            return permKey.toLowerCase().includes(q) || desc.toLowerCase().includes(q);
        },

        // Hitung berapa izin yang cocok di suatu kategori
        countMatched(catId) {
            const container = document.getElementById(catId + '-' + this.activeRole);
            if (!container) return 1; // Fallback jika dom belum siap
            const rows = container.querySelectorAll('.perm-row');
            let count = 0;
            rows.forEach(row => {
                const perm = row.dataset.perm;
                const desc = row.dataset.desc;
                if (this.matchesQuery(perm, desc)) count++;
            });
            return count;
        },

        // Pilih Semua / Hapus Centang per kategori
        toggleCategory(catId, checked) {
            const container = document.getElementById(catId + '-' + this.activeRole);
            if (!container) return;
            const checkboxes = container.querySelectorAll('.perm-checkbox');
            checkboxes.forEach(cb => {
                // Hanya centang yang lolos filter pencarian
                const row = cb.closest('.perm-row');
                if (row) {
                    const perm = row.dataset.perm;
                    const desc = row.dataset.desc;
                    if (this.matchesQuery(perm, desc)) {
                        cb.checked = checked;
                    }
                }
            });
        }
    }">
        
        <!-- Breadcrumb -->
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-xs md:text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-500">Super Admin</span>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Hak Akses (RBAC)</span>
            </div>
        </div>

        <!-- Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-cyan-500 text-white flex items-center justify-center text-sm shadow-md">
                        <i class="fas fa-user-shield"></i>
                    </span>
                    Pengaturan Hak Akses (RBAC)
                </h2>
                <p class="text-slate-500 text-sm">Sesuaikan izin operasional masing-masing peran (role) secara dinamis tanpa mengubah kode program.</p>
            </div>
        </div>

        <!-- Main Form Wrapper -->
        <form method="POST" action="{{ route('superadmin.rbac.update') }}" class="space-y-6">
            @csrf

            <!-- Split Layout -->
            <div class="flex flex-col lg:flex-row gap-8 items-start">
                
                <!-- LEFT PANEL: ROLE SELECTOR TABS -->
                <div class="w-full lg:w-80 shrink-0 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 space-y-2">
                    <div class="px-3 pb-3 border-b border-slate-100 mb-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Pilih Peran</span>
                    </div>

                    @php
                        $rolesList = [
                            'admin' => [
                                'title' => 'Administrator',
                                'desc' => 'Mengelola CRUD cctv, peta, & data master',
                                'icon' => 'fa-user-tie',
                                'color' => 'from-cyan-500 to-blue-500'
                            ],
                            'operator' => [
                                'title' => 'Operator Pusat',
                                'desc' => 'Memantau live stream, playback, dan koordinat peta',
                                'icon' => 'fa-users-cog',
                                'color' => 'from-emerald-500 to-teal-500'
                            ],
                            'faculty_operator' => [
                                'title' => 'Operator Fakultas',
                                'desc' => 'Memantau & mengelola cctv terbatas pada fakultasnya',
                                'icon' => 'fa-university',
                                'color' => 'from-purple-500 to-indigo-500'
                            ],
                            'user' => [
                                'title' => 'User Biasa',
                                'desc' => 'Hak akses pemantauan standar (view only)',
                                'icon' => 'fa-user',
                                'color' => 'from-amber-500 to-orange-500'
                            ],
                            'api_viewer' => [
                                'title' => 'API Viewer / Client',
                                'desc' => 'Koneksi integrasi data pihak ketiga',
                                'icon' => 'fa-robot',
                                'color' => 'from-slate-600 to-slate-800'
                            ]
                        ];
                    @endphp

                    @foreach($rolesList as $roleKey => $meta)
                        <button type="button" 
                                @click="activeRole = '{{ $roleKey }}'"
                                :class="activeRole === '{{ $roleKey }}' ? 'bg-gradient-to-r from-cyan-50 to-blue-50 border-l-4 border-cyan-500 text-cyan-700 shadow-sm' : 'hover:bg-slate-50 border-l-4 border-transparent text-slate-600'"
                                class="w-full text-left p-3 rounded-xl transition-all duration-200 flex items-start gap-3 select-none">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 text-xs shadow-inner"
                                 :class="activeRole === '{{ $roleKey }}' ? 'bg-cyan-500 text-white' : 'bg-slate-100 text-slate-400'">
                                <i class="fas {{ $meta['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-xs" :class="activeRole === '{{ $roleKey }}' ? 'text-slate-800' : 'text-slate-700'">{{ $meta['title'] }}</h4>
                                <p class="text-[10px] text-slate-400 truncate mt-0.5">{{ $meta['desc'] }}</p>
                            </div>
                        </button>
                    @endforeach
                </div>

                <!-- RIGHT PANEL: PERMISSIONS GROUPED -->
                <div class="flex-1 w-full space-y-6">
                    
                    <!-- Search bar -->
                    <div class="relative bg-white rounded-2xl border border-slate-200/80 shadow-sm p-3">
                        <i class="fas fa-search absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" x-model="searchQuery" placeholder="Cari hak akses atau deskripsi izin..." 
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100 outline-none text-xs transition-all shadow-inner text-slate-700">
                    </div>

                    @php
                        $permissionsMap = [
                            'dashboard' => [
                                'title' => 'Dashboard & Live Monitoring',
                                'icon' => 'fa-tv',
                                'color' => 'bg-cyan-50 text-cyan-600',
                                'perms' => [
                                    'dashboard_view' => 'Mengakses Dashboard ringkasan status & visualisasi utama',
                                    'live_monitoring' => 'Menonton siaran langsung (Live Stream) CCTV dan mengatur Preset'
                                ]
                            ],
                            'playback' => [
                                'title' => 'Rekaman / Playback',
                                'icon' => 'fa-history',
                                'color' => 'bg-emerald-50 text-emerald-600',
                                'perms' => [
                                    'playback_view' => 'Mengakses menu Playback dan melihat linimasa rekaman',
                                    'playback_export' => 'Mengunduh dan mengekspor berkas rekaman (.mp4)'
                                ]
                            ],
                            'map' => [
                                'title' => 'Peta Pemantauan (Map)',
                                'icon' => 'fa-map-location-dot',
                                'color' => 'bg-blue-50 text-blue-600',
                                'perms' => [
                                    'map_view' => 'Mengakses menu Map Monitoring untuk melihat sebaran kamera di peta',
                                    'map_update_coords' => 'Menggeser marker atau mengubah titik koordinat kamera di peta'
                                ]
                            ],
                            'cctv' => [
                                'title' => 'Pengelolaan Kamera (CCTV)',
                                'icon' => 'fa-camera',
                                'color' => 'bg-purple-50 text-purple-600',
                                'perms' => [
                                    'cctv_view' => 'Melihat tabel daftar kamera di menu Master Kamera',
                                    'cctv_create' => 'Menambah data kamera CCTV baru',
                                    'cctv_edit' => 'Mengubah konfigurasi, alamat IP, atau detail RTSP/ONVIF kamera',
                                    'cctv_delete' => 'Menghapus data kamera dari sistem',
                                    'cctv_bulk_move' => 'Memindahkan kamera antar Node perekam secara masal (Bulk Move)',
                                    'cctv_import' => 'Mengimpor kamera secara masal dari berkas excel (Excel Migration)'
                                ]
                            ],
                            'user' => [
                                'title' => 'Pengelolaan Pengguna (User)',
                                'icon' => 'fa-users',
                                'color' => 'bg-amber-50 text-amber-600',
                                'perms' => [
                                    'user_view' => 'Melihat tabel daftar pengguna di menu Manage Users',
                                    'user_create' => 'Membuat akun pengguna baru secara manual',
                                    'user_edit' => 'Mengubah data pengguna (nama, email, fakultas) dan menentukan plotting role',
                                    'user_delete' => 'Menghapus akun pengguna dari aplikasi',
                                    'user_approve' => 'Menyetujui pendaftaran pengguna baru (mengubah status dari pending ke approved)'
                                ]
                            ],
                            'infrastructure' => [
                                'title' => 'Infrastruktur & Referensi',
                                'icon' => 'fa-server',
                                'color' => 'bg-indigo-50 text-indigo-600',
                                'perms' => [
                                    'server_manage' => 'Mengelola CRUD Server Node perekam & memantau service FFmpeg',
                                    'api_key_manage' => 'Mengelola CRUD API Key untuk integrasi pihak ketiga',
                                    'report_view' => 'Mengakses menu Laporan (Report CCTV) serta mengunduh CSV/PDF',
                                    'event_view' => 'Melihat log kejadian kecerdasan buatan (Intelligence Events / Motion Detection)',
                                    'notification_manage' => 'Membaca atau menandai selesai notifikasi sistem',
                                    'building_manage' => 'Mengelola CRUD Master Gedung dan Master Fakultas',
                                    'activity_log_view' => 'Mengakses menu Log Aktivitas Pengguna (Super Admin Log)'
                                ]
                            ]
                        ];
                    @endphp

                    @foreach(['admin', 'operator', 'faculty_operator', 'user', 'api_viewer'] as $roleKey)
                        <!-- Tab Content for roleKey -->
                        <div x-show="activeRole === '{{ $roleKey }}'" x-cloak class="space-y-6">
                            
                            @foreach($permissionsMap as $catId => $cat)
                                <!-- Category Card -->
                                <div id="{{ $catId }}-{{ $roleKey }}" 
                                     x-show="countMatched('{{ $catId }}') > 0"
                                     class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden animate__animated animate__fadeIn animate__faster">
                                    
                                    <!-- Card Header -->
                                    <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg {{ $cat['color'] }} flex items-center justify-center text-xs shadow-inner">
                                                <i class="fas {{ $cat['icon'] }}"></i>
                                            </div>
                                            <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider">{{ $cat['title'] }}</h3>
                                        </div>
                                        
                                        <!-- Select All Toggle -->
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="toggleCategory('{{ $catId }}', true)" class="text-[10px] font-bold text-cyan-600 hover:text-cyan-800 uppercase tracking-tighter transition-colors">Pilih Semua</button>
                                            <span class="text-slate-300">|</span>
                                            <button type="button" @click="toggleCategory('{{ $catId }}', false)" class="text-[10px] font-bold text-rose-500 hover:text-rose-700 uppercase tracking-tighter transition-colors">Clear</button>
                                        </div>
                                    </div>

                                    <!-- Card Body (Permissions List) -->
                                    <div class="p-6 divide-y divide-slate-50">
                                        @foreach($cat['perms'] as $permKey => $desc)
                                            <div class="perm-row py-3 flex items-start justify-between gap-6" 
                                                 data-perm="{{ $permKey }}" 
                                                 data-desc="{{ $desc }}"
                                                 x-show="matchesQuery('{{ $permKey }}', '{{ $desc }}')">
                                                <div class="space-y-1">
                                                    <div class="flex items-center gap-2">
                                                        <code class="px-2 py-0.5 bg-slate-100 rounded text-slate-700 font-mono text-[10px] font-bold">{{ $permKey }}</code>
                                                    </div>
                                                    <p class="text-[11px] text-slate-400 font-medium leading-relaxed">{{ $desc }}</p>
                                                </div>
                                                
                                                <!-- Custom iOS Switch Checkbox -->
                                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                                    <input type="checkbox" name="permissions[{{ $roleKey }}][]" value="{{ $permKey }}"
                                                           class="perm-checkbox sr-only peer"
                                                           {{ in_array($permKey, $rolePermissions[$roleKey] ?? []) ? 'checked' : '' }}>
                                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-cyan-500"></div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            <!-- Empty Search Alert inside Tab -->
                            <div x-show="Object.keys(roles).some(r => {
                                    let matched = false;
                                    document.querySelectorAll('.perm-row').forEach(row => {
                                        if(row.style.display !== 'none') matched = true;
                                    });
                                    return !matched;
                                })"
                                 class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center text-slate-400 flex flex-col items-center">
                                <i class="fas fa-search-minus text-4xl text-slate-300 mb-3"></i>
                                <p class="text-sm font-bold">Tidak ada izin yang cocok</p>
                                <p class="text-xs text-slate-400 mt-1">Coba saring kembali kata kunci pencarian Anda.</p>
                            </div>

                        </div>
                    @endforeach

                    <!-- Save Action Panel (Fixed Bottom Bar style or card) -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 flex justify-between items-center gap-4">
                        <div class="flex items-center gap-2 text-slate-400 text-xs">
                            <i class="fas fa-info-circle text-cyan-500"></i>
                            <span>Klik simpan untuk meresmikan semua perubahan role.</span>
                        </div>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white font-bold text-xs tracking-wide shadow-md shadow-cyan-500/20 transition-all flex items-center gap-2 select-none active:scale-95">
                            <i class="fas fa-save"></i> Simpan Semua Hak Akses
                        </button>
                    </div>

                </div>

            </div>
        </form>

    </main>

    <!-- Global Toast Alert Script -->
    <script>
        @if(session('success'))
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                background: '#fff',
                iconColor: '#06b6d4',
                showClass: { popup: 'animate__animated animate__fadeInUp' },
                hideClass: { popup: 'animate__animated animate__fadeOutDown' }
            });
        @endif
        @if(session('error'))
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
                background: '#fff',
                iconColor: '#f43f5e',
                showClass: { popup: 'animate__animated animate__fadeInUp' },
                hideClass: { popup: 'animate__animated animate__fadeOutDown' }
            });
        @endif
    </script>
</x-app-layout>
