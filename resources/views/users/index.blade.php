<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-500">Manajemen</span>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Daftar Pengguna</span>
            </div>
        </div>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Manage Users</h2>
                <p class="text-slate-500">Kelola akun administrator, operator, dan user monitoring</p>
            </div>
            <a href="{{ route('users.create') }}" 
               class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all duration-300 flex items-center">
                <i class="fas fa-user-plus mr-2"></i>
                Tambah User
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <div class="glass-effect rounded-2xl p-4 mb-6 border border-cyan-100">
            <form action="{{ route('users.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[250px]">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Cari Pengguna</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-3 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau email..." 
                               class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 focus:border-cyan-400 transition-all text-sm">
                    </div>
                </div>
                <div class="w-48">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Role</label>
                    <select name="role" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 focus:border-cyan-400 transition-all text-sm appearance-none">
                        <option value="">Semua Role</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="operator" {{ request('role') == 'operator' ? 'selected' : '' }}>Operator</option>
                        <option value="faculty_operator" {{ request('role') == 'faculty_operator' ? 'selected' : '' }}>Operator Fakultas</option>
                        <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User (Restricted)</option>
                        <option value="api_viewer" {{ request('role') == 'api_viewer' ? 'selected' : '' }}>API Viewer</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-6 py-2 bg-slate-800 text-white rounded-xl font-bold hover:bg-slate-700 transition-all shadow-sm">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'role']))
                        <a href="{{ route('users.index') }}" class="px-4 py-2 bg-slate-100 text-slate-500 rounded-xl font-bold hover:bg-slate-200 transition-all flex items-center">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="glass-effect rounded-2xl p-6 border border-cyan-100">
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-wider border-b border-cyan-100">
                            <th class="pb-4 pl-4 font-semibold">Nama</th>
                            <th class="pb-4 font-semibold">Email</th>
                            <th class="pb-4 font-semibold">Role</th>
                            <th class="pb-4 font-semibold">Fakultas</th>
                            <th class="pb-4 font-semibold">Akses CCTV</th>
                            <th class="pb-4 pr-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-600">
                        @forelse ($users as $user)
                            <tr class="hover:bg-cyan-50/50 transition-colors group border-b border-slate-50 last:border-none">
                                <td class="py-4 pl-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-slate-200 to-slate-300 flex items-center justify-center text-slate-600 font-bold text-xs">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <span class="font-medium text-slate-800">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="py-4 font-medium text-slate-500">
                                    {{ $user->email }}
                                </td>
                                <td class="py-4">
                                    @php
                                        $badgeClass = match($user->role) {
                                            'admin' => 'bg-purple-100 text-purple-700 border border-purple-200',
                                            'operator' => 'bg-blue-100 text-blue-700 border border-blue-200',
                                            default => 'bg-slate-100 text-slate-600 border border-slate-200',
                                        };
                                        $iconClass = match($user->role) {
                                            'admin' => 'fa-shield-alt',
                                            'operator' => 'fa-user-cog',
                                            default => 'fa-user',
                                        };
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase flex items-center w-fit {{ $badgeClass }}">
                                        <i class="fas {{ $iconClass }} mr-2"></i> {{ $user->role }}
                                    </span>
                                </td>
                                <td class="py-4">
                                    @if($user->role === 'admin' || $user->role === 'operator')
                                        {{-- Tampilan untuk Admin/Pusat --}}
                                        <span class="text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-1 rounded border border-purple-100 uppercase tracking-wider">
                                            Global
                                        </span>
                                    @elseif($user->faculty)
                                        {{-- Tampilan untuk Fakultas --}}
                                        <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded border border-slate-200">
                                            {{ $user->faculty }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="py-4">
                                    @if($user->role === 'user')
                                        <span class="text-xs font-medium text-slate-500 bg-slate-50 px-2 py-1 rounded border border-slate-200">
                                            {{ $user->cctvs->count() }} Kamera
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Full Access</span>
                                    @endif
                                </td>
                                <td class="py-4 pr-4 text-right space-x-2">
                                    <a href="{{ route('users.edit', $user->id) }}" 
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-cyan-300 hover:text-cyan-600 transition-all shadow-sm">
                                        <i class="fas fa-pencil-alt text-xs"></i>
                                    </a>

                                    @if(auth()->id() !== $user->id)
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-red-300 hover:text-red-600 transition-all shadow-sm">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <i class="fas fa-users text-4xl mb-3 opacity-50"></i>
                                        <p>Belum ada data user.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $users->links() }}
            </div>

        </div>
    </main>
</x-app-layout>