<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <a href="{{ route('users.index') }}" class="text-slate-500 hover:text-cyan-600">Manajemen User</a>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Edit User</span>
            </div>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="glass-effect rounded-2xl p-8 border border-cyan-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-slate-800">Edit User: {{ $user->name }}</h2>
                </div>

                <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 {{ auth()->user()->role === 'operator' ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '' }}" {{ auth()->user()->role === 'operator' ? 'readonly' : 'required' }}>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 {{ auth()->user()->role === 'operator' ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '' }}" {{ auth()->user()->role === 'operator' ? 'readonly' : 'required' }}>
                        </div>
                    </div>

                    @if(auth()->user()->role !== 'operator')
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Password Baru (Opsional)</label>
                            <input type="password" name="password" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200" placeholder="Biarkan kosong jika tidak diubah">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200" placeholder="Ulangi password baru">
                        </div>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Role (Hak Akses)</label>
                        <select name="role" id="role_select" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 {{ auth()->user()->role === 'operator' ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '' }}" onchange="toggleSections()" {{ auth()->user()->role === 'operator' ? 'disabled' : '' }}>
                            <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>User Biasa (View Only)</option>
                            
                            <!-- PERBAIKAN 1: Tambahkan Opsi API Viewer -->
                            <option value="api_viewer" {{ $user->role == 'api_viewer' ? 'selected' : '' }} class="font-bold text-indigo-600">API Client / 3rd Party App</option>
                            
                            @if(in_array(auth()->user()->role, ['admin', 'operator']))
                                <option value="faculty_operator" {{ $user->role == 'faculty_operator' ? 'selected' : '' }}>Operator Fakultas (Manage Fakultasnya)</option>
                                <option value="operator" {{ $user->role == 'operator' ? 'selected' : '' }}>Operator Pusat (Manage Semua CCTV)</option>
                            @endif
                            
                            @if(auth()->user()->role === 'admin')
                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Administrator</option>
                            @endif
                        </select>
                    </div>

                    @if(auth()->user()->role === 'faculty_operator')
                        <input type="hidden" name="faculty" value="{{ auth()->user()->faculty }}">
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Fakultas</label>
                            <input type="text" value="{{ auth()->user()->faculty }}" readonly class="w-full px-4 py-2 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 cursor-not-allowed">
                        </div>
                    @else
                        <div id="faculty_section" style="display: none;">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Fakultas</label>
                            <select name="faculty" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 {{ auth()->user()->role === 'operator' ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '' }}" {{ auth()->user()->role === 'operator' ? 'disabled' : '' }}>
                                <option value="" disabled>Pilih Fakultas...</option>
                                {{-- Pastikan Controller mengirim $faculties --}}
                                @foreach($faculties ?? \App\Models\Building::distinct()->pluck('fakultas') as $fakultas)
                                    @if($fakultas) 
                                        <option value="{{ $fakultas }}" {{ $user->faculty == $fakultas ? 'selected' : '' }}>
                                            {{ $fakultas }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-400 mt-1">Wajib diisi untuk Operator Fakultas dan User Biasa.</p>
                        </div>
                    @endif

                    <div id="cctv_access_section" class="bg-slate-50 p-4 rounded-xl border border-slate-200" style="display: none;">
                        <h3 class="text-sm font-bold text-slate-700 mb-3">Hak Akses CCTV</h3>
                        <p class="text-xs text-slate-500 mb-4">Centang kamera yang boleh dipantau oleh user/aplikasi ini.</p>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-60 overflow-y-auto custom-scrollbar">
                            @foreach($cctvs as $cctv)
                                <label class="flex items-center space-x-3 p-2 rounded hover:bg-white transition cursor-pointer border border-transparent hover:border-cyan-100">
                                    <input type="checkbox" name="cctv_access[]" value="{{ $cctv->id }}" 
                                           class="rounded text-cyan-500 focus:ring-cyan-200"
                                           {{-- PERBAIKAN 2: Gunakan in_array untuk mengecek data pivot --}}
                                           {{ in_array($cctv->id, $assignedCctvs) ? 'checked' : '' }}>
                                    
                                    <div class="text-xs">
                                        <span class="block font-bold text-slate-700">{{ $cctv->kode_cctv }}</span>
                                        <span class="block text-slate-500 truncate max-w-[150px]">{{ $cctv->nama_cctv }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 pt-4">
                        <a href="{{ route('users.index') }}" class="px-6 py-2 text-slate-500 hover:bg-slate-100 rounded-xl font-medium transition-colors">Batal</a>
                        <button type="submit" class="px-6 py-2 bg-cyan-500 text-white rounded-xl hover:bg-cyan-600 font-medium shadow-lg shadow-cyan-500/30">Update User</button>
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
            
            // PERBAIKAN 3: Logika JavaScript untuk menampilkan CCTV Section
            if (cctvSection) {
                // Tampilkan jika role User atau API Viewer
                cctvSection.style.display = (role === 'user' || role === 'api_viewer') ? 'block' : 'none';
            }

            if (facultySection) {
                if (role === 'faculty_operator' || role === 'user') {
                    facultySection.style.display = 'block';
                } else {
                    facultySection.style.display = 'none';
                }
            }
        }
        
        // Jalankan saat load agar form terisi sesuai data user yg diedit
        toggleSections();
    </script>
</x-app-layout>