<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        
        <div id="breadcrumb" class="mb-6">
            <!-- Breadcrumb tetap sama -->
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <a href="{{ route('users.index') }}" class="text-slate-500 hover:text-cyan-600">Manajemen User</a>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Tambah User Baru</span>
            </div>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="glass-effect rounded-2xl p-8 border border-cyan-100">
                <h2 class="text-2xl font-bold text-slate-800 mb-6">Tambah User Baru</h2>

                <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Nama & Email -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap / Nama Aplikasi</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 @error('name') border-red-500 @enderror" required>
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 @error('email') border-red-500 @enderror" required>
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                            <input type="password" name="password" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 @error('password') border-red-500 @enderror" required>
                            <p class="text-[10px] text-slate-400 mt-1">Untuk API Client, password ini tidak digunakan untuk login, tapi wajib diisi.</p>
                            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200" required>
                        </div>
                    </div>

                    <!-- Role Selection -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Role (Hak Akses)</label>
                        <select name="role" id="role_select" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200" onchange="toggleSections()">
                            <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User Biasa (View Only)</option>
                            
                            <!-- OPSI BARU: API VIEWER -->
                            <option value="api_viewer" {{ old('role') == 'api_viewer' ? 'selected' : '' }} class="font-bold text-indigo-600">API Client / 3rd Party App</option>
                            
                            @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'operator']))
                                <option value="faculty_operator" {{ old('role') == 'faculty_operator' ? 'selected' : '' }}>Operator Fakultas</option>
                                <option value="operator" {{ old('role') == 'operator' ? 'selected' : '' }}>Operator Pusat</option>
                            @endif
                            
                            @if(in_array(auth()->user()->role, ['admin', 'superadmin']))
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                            @endif

                            @if(auth()->user()->role === 'superadmin')
                                <option value="superadmin" {{ old('role') == 'superadmin' ? 'selected' : '' }}>Super Administrator</option>
                            @endif
                        </select>
                        @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Fakultas Section -->
                    @if(auth()->user()->role === 'faculty_operator')
                        <input type="hidden" name="faculty" value="{{ auth()->user()->faculty }}">
                    @else
                        <div id="faculty_section" style="display: none;">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Fakultas</label>
                            <select name="faculty" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 @error('faculty') border-red-500 @enderror">
                                <option value="" disabled selected>Pilih Fakultas...</option>
                                @foreach($faculties as $fakultas)
                                    <option value="{{ $fakultas }}" {{ old('faculty') == $fakultas ? 'selected' : '' }}>{{ $fakultas }}</option>
                                @endforeach
                            </select>
                            @error('faculty') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <!-- CCTV Access (Assignment) -->
                    <div id="cctv_access_section" 
                         class="bg-slate-50 p-6 rounded-2xl border border-slate-200" 
                         style="display: none;"
                         x-data="{ search: '' }">
                         <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700">Akses Kamera Spesifik</label>
                                <p class="text-[10px] text-slate-500">Pilih kamera yang boleh dilihat oleh user/aplikasi ini.</p>
                            </div>
                            <!-- SEARCH INPUT -->
                            <div class="relative w-full md:w-64">
                                <input type="text" x-model="search" placeholder="Cari Kode / Nama Kamera..." 
                                       class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-cyan-200 shadow-sm transition-all">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                            </div>
                         </div>
                         
                         <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 max-h-72 overflow-y-auto custom-scrollbar p-1">
                            @foreach($cctvs as $cctv)
                                <label x-show="search === '' || '{{ strtolower($cctv->kode_cctv) }}'.includes(search.toLowerCase()) || '{{ strtolower($cctv->nama_cctv) }}'.includes(search.toLowerCase())"
                                       class="flex items-center space-x-3 p-3 rounded-xl bg-white border border-slate-100 hover:border-cyan-200 hover:shadow-sm transition cursor-pointer group">
                                    <input type="checkbox" name="cctv_access[]" value="{{ $cctv->id }}" 
                                           class="rounded text-cyan-500 focus:ring-cyan-200 transition-all"
                                           {{ (is_array(old('cctv_access')) && in_array($cctv->id, old('cctv_access'))) ? 'checked' : '' }}>
                                    <div class="text-[10px] leading-tight">
                                        <span class="block font-bold text-slate-700 group-hover:text-cyan-600">{{ $cctv->kode_cctv }}</span>
                                        <span class="block text-slate-400 truncate max-w-[120px]">{{ $cctv->nama_cctv }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <div x-show="search !== ''" class="mt-4 text-center">
                            <p class="text-[10px] text-slate-400 italic" x-text="'Menampilkan hasil pencarian untuk \'' + search + '\''"></p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit" class="px-6 py-2 bg-cyan-500 text-white rounded-xl hover:bg-cyan-600 font-medium shadow-lg shadow-cyan-500/30">Simpan User</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function toggleSections() {
            const roleSelect = document.getElementById('role_select');
            if (!roleSelect) return;

            const role = roleSelect.value;
            const facultySection = document.getElementById('faculty_section');
            const cctvSection = document.getElementById('cctv_access_section');
            
            // Tampilkan pilihan CCTV jika Role = User Biasa ATAU API Viewer
            if (cctvSection) {
                // UPDATE: Tampilkan untuk 'api_viewer' juga
                cctvSection.style.display = (role === 'user' || role === 'api_viewer') ? 'block' : 'none';
            }

            if (facultySection) {
                // API Viewer tidak wajib pilih fakultas (bisa null), tapi User/Operator wajib
                if (role === 'faculty_operator' || role === 'user') {
                    facultySection.style.display = 'block';
                } else {
                    facultySection.style.display = 'none';
                }
            }
        }
        
        toggleSections();
    </script>
</x-app-layout>