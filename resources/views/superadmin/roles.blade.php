<x-app-layout>
    <!-- Tambahkan SweetAlert2 & AnimateCSS untuk UI interaktif -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <main id="main-content" class="pt-20 p-6 md:p-8 min-h-screen bg-slate-50">
        
        <!-- Breadcrumb -->
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-xs md:text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-500">Super Admin</span>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Manajemen Role</span>
            </div>
        </div>

        <!-- Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mb-2 flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-cyan-500 text-white flex items-center justify-center text-sm shadow-md">
                        <i class="fas fa-user-tag"></i>
                    </span>
                    Manajemen Role
                </h2>
                <p class="text-slate-500 text-sm">Kelola (Tambah & Hapus) Role/Peran custom yang dapat diassign ke user.</p>
            </div>
            <div>
                <button type="button" onclick="document.getElementById('createRoleModal').classList.remove('hidden')" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white font-bold text-sm tracking-wide shadow-md shadow-cyan-500/20 transition-all flex items-center gap-2 select-none active:scale-95">
                    <i class="fas fa-plus"></i> Tambah Role Baru
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($roles as $slug => $meta)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col group transition-all hover:border-cyan-300 hover:shadow-md">
                    <div class="p-6 flex-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $meta['color'] }} text-white flex items-center justify-center text-lg shadow-inner">
                                <i class="fas {{ $meta['icon'] }}"></i>
                            </div>
                            @if($meta['is_system'])
                                <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-slate-200" title="Role Sistem (Tidak dapat dihapus)">
                                    <i class="fas fa-lock mr-1"></i> Sistem
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-cyan-50 text-cyan-600 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-cyan-100">
                                    Custom
                                </span>
                            @endif
                        </div>
                        
                        <h3 class="text-lg font-bold text-slate-800 mb-1 group-hover:text-cyan-600 transition-colors">{{ $meta['title'] }}</h3>
                        <p class="text-xs font-mono text-slate-400 mb-3 bg-slate-50 p-1 px-2 rounded-md inline-block border border-slate-100">{{ $slug }}</p>
                        <p class="text-sm text-slate-500">{{ $meta['desc'] }}</p>
                    </div>
                    
                    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ route('superadmin.rbac.index') }}" class="text-xs font-bold text-cyan-600 hover:text-cyan-800 flex items-center gap-1 transition-colors">
                            <i class="fas fa-sliders-h"></i> Atur Hak Akses
                        </a>
                        
                        @if(!$meta['is_system'])
                            <form action="{{ route('superadmin.roles.destroy', $slug) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus role ini? Pastikan tidak ada user yang menggunakannya.');" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:border-red-300 hover:text-red-600 hover:bg-red-50 flex items-center justify-center transition-all" title="Hapus Role">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </form>
                        @else
                            <button type="button" disabled class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 text-slate-300 flex items-center justify-center cursor-not-allowed" title="Role Sistem tidak dapat dihapus">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Create Role Modal -->
        <div id="createRoleModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('createRoleModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl p-6 md:p-8 w-full max-w-lg shadow-2xl m-4 animate__animated animate__fadeInUp animate__faster">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-plus-circle text-cyan-500"></i>
                        Tambah Role Baru
                    </h3>
                    <button type="button" onclick="document.getElementById('createRoleModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                
                <form action="{{ route('superadmin.roles.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Nama Role (ID Unik)</label>
                        <input type="text" name="slug" required pattern="^[a-z0-9_]+$" placeholder="misal: satpam, manajer_area" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 focus:border-cyan-400 transition-all text-sm">
                        <p class="text-[10px] text-slate-400">Hanya boleh huruf kecil, angka, dan underscore (_). Tidak boleh ada spasi.</p>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Title (Nama Tampilan)</label>
                        <input type="text" name="title" required placeholder="misal: Satpam Gedung Utama" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 focus:border-cyan-400 transition-all text-sm">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Deskripsi Singkat</label>
                        <textarea name="desc" rows="2" placeholder="Tugas dan fungsi role ini..." 
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 focus:border-cyan-400 transition-all text-sm resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Ikon (FontAwesome)</label>
                            <input type="text" name="icon" value="fa-user-tag" 
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 focus:border-cyan-400 transition-all text-sm">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Warna (Tailwind)</label>
                            <select name="color" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 focus:border-cyan-400 transition-all text-sm appearance-none bg-white">
                                <option value="from-slate-500 to-slate-700">Slate (Default)</option>
                                <option value="from-blue-500 to-indigo-600">Blue-Indigo</option>
                                <option value="from-emerald-500 to-green-600">Emerald-Green</option>
                                <option value="from-amber-500 to-orange-500">Amber-Orange</option>
                                <option value="from-rose-500 to-pink-600">Rose-Pink</option>
                                <option value="from-purple-500 to-fuchsia-600">Purple-Fuchsia</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" onclick="document.getElementById('createRoleModal').classList.add('hidden')" class="px-5 py-2 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-colors">Batal</button>
                        <button type="submit" class="px-6 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-600 text-white font-bold text-sm shadow-md shadow-cyan-500/30 transition-all">Simpan Role</button>
                    </div>
                </form>
            </div>
        </div>

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
        @if($errors->any())
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                icon: 'error',
                title: 'Gagal!',
                text: "{{ $errors->first() }}",
                background: '#fff',
                iconColor: '#f43f5e',
                showClass: { popup: 'animate__animated animate__fadeInUp' },
                hideClass: { popup: 'animate__animated animate__fadeOutDown' }
            });
        @endif
    </script>
</x-app-layout>
