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
            @can('user_create')
            <a href="{{ route('users.create') }}" 
               class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all duration-300 flex items-center">
                <i class="fas fa-user-plus mr-2"></i>
                Tambah User
            </a>
            @endcan
        </div>

        <div class="glass-effect rounded-2xl p-6 border border-cyan-100" x-data="{
            loading: false,
            sortBy: '{{ request('sort_by', 'created_at') }}',
            sortDir: '{{ request('sort_dir', 'desc') }}',
            handleSort(field) {
                if (this.sortBy === field) {
                    this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortBy = field;
                    this.sortDir = 'asc';
                }
                this.$nextTick(() => { this.updateTable(); });
            },
            async updateTable() {
                this.loading = true;
                const form = document.getElementById('filter-form');
                const params = new URLSearchParams(new FormData(form)).toString();
                try {
                    const res = await fetch(`{{ route('users.index') }}?${params}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    document.getElementById('user-table-body').innerHTML = doc.getElementById('user-table-body').innerHTML;
                    document.getElementById('pagination-container').innerHTML = doc.getElementById('pagination-container').innerHTML;
                    window.history.pushState({}, '', `?${params}`);
                } finally {
                    this.loading = false;
                }
            }
        }">
            
        <!-- Seamless Filter & Search Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <form id="filter-form" action="{{ route('users.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full" @submit.prevent="updateTable()">
                <input type="hidden" name="sort_by" :value="sortBy">
                <input type="hidden" name="sort_dir" :value="sortDir">
                <div class="relative flex-1 min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400 text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           @input.debounce.500ms="updateTable()"
                           placeholder="Cari nama atau email..." 
                           class="w-full pl-9 pr-4 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white/50 shadow-sm">
                    <div x-show="loading" class="absolute inset-y-0 right-3 flex items-center">
                        <i class="fas fa-circle-notch fa-spin text-cyan-500 text-xs"></i>
                    </div>
                </div>

                <div class="relative">
                    <select name="role" @change="updateTable()" 
                            class="w-48 pl-4 pr-10 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white/50 cursor-pointer shadow-sm appearance-none">
                        <option value="">Semua Role</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="operator" {{ request('role') == 'operator' ? 'selected' : '' }}>Operator</option>
                        <option value="faculty_operator" {{ request('role') == 'faculty_operator' ? 'selected' : '' }}>Operator Fakultas</option>
                        <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User (Restricted)</option>
                        <option value="api_viewer" {{ request('role') == 'api_viewer' ? 'selected' : '' }}>API Viewer</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-3 text-[10px] text-slate-400 pointer-events-none"></i>
                </div>

                <div class="relative">
                    <select name="status" @change="updateTable()" 
                            class="w-48 pl-4 pr-10 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white/50 cursor-pointer shadow-sm appearance-none">
                        <option value="">Semua Status</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aktif</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Butuh Approval</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-3 text-[10px] text-slate-400 pointer-events-none"></i>
                </div>

                @if(request()->anyFilled(['search', 'role', 'status']))
                    <a href="{{ route('users.index') }}" class="text-[10px] font-bold text-slate-400 hover:text-red-500 uppercase tracking-wider flex items-center transition-colors ml-2">
                        <i class="fas fa-times-circle mr-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-wider border-b border-cyan-100 select-none">
                            <th class="pb-4 pl-4 font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('name')">
                                Pengguna
                                <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'name' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                            </th>
                            <th class="pb-4 font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('role')">
                                Hak Akses (Role)
                                <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'role' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                            </th>
                            <th class="pb-4 font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('faculty')">
                                Fakultas / Unit
                                <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'faculty' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                            </th>
                            <th class="pb-4 font-semibold">Akses CCTV</th>
                            <th class="pb-4 font-semibold">Status</th>
                            <th class="pb-4 pr-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="user-table-body" class="text-sm text-slate-600">
                        @forelse ($users as $user)
                            <tr class="hover:bg-cyan-50/50 transition-colors group border-b border-slate-50 last:border-none">
                                <td class="py-4 pl-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-slate-200 to-slate-300 flex items-center justify-center text-slate-600 font-bold text-xs">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <span class="font-medium text-slate-800 block">{{ $user->name }}</span>
                                            <span class="text-xs text-slate-400 block">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4">
                                    @php
                                        $badgeClass = match($user->role) {
                                            'superadmin' => 'bg-red-100 text-red-700 border border-red-200',
                                            'admin' => 'bg-purple-100 text-purple-700 border border-purple-200',
                                            'operator' => 'bg-blue-100 text-blue-700 border border-blue-200',
                                            'faculty_operator' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                                            'api_viewer' => 'bg-indigo-100 text-indigo-700 border border-indigo-200',
                                            default => 'bg-slate-100 text-slate-600 border border-slate-200',
                                        };
                                        $iconClass = match($user->role) {
                                            'superadmin' => 'fa-crown',
                                            'admin' => 'fa-shield-alt',
                                            'operator' => 'fa-user-cog',
                                            'faculty_operator' => 'fa-user-shield',
                                            'api_viewer' => 'fa-key',
                                            default => 'fa-user',
                                        };
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase flex items-center w-fit {{ $badgeClass }}">
                                        <i class="fas {{ $iconClass }} mr-2"></i> {{ $user->role }}
                                    </span>
                                </td>
                                <td class="py-4">
                                    @if($user->faculty)
                                        {{-- Tampilan untuk Fakultas --}}
                                        <span class="text-xs font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded border border-slate-200">
                                            {{ $user->faculty }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="py-4">
                                    @if($user->role === 'superadmin' || $user->role === 'admin' || $user->role === 'operator')
                                        <span class="text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-1 rounded border border-purple-100 uppercase tracking-wider">
                                            Global (Semua Kamera)
                                        </span>
                                    @elseif($user->role === 'user' || $user->role === 'api_viewer')
                                        <span class="text-xs font-medium text-slate-500 bg-slate-50 px-2 py-1 rounded border border-slate-200">
                                            {{ $user->cctvs->count() }} Kamera
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Tidak ada akses</span>
                                    @endif
                                </td>
                                <td class="py-4">
                                    @php
                                        if ($user->status === 'pending') {
                                            $statusClass = 'bg-amber-100 text-amber-700 border border-amber-200';
                                            $statusLabel = 'Butuh Approval';
                                            $statusIcon = 'fa-clock animate-pulse';
                                        } elseif ($user->status === 'deactivated') {
                                            $statusClass = 'bg-red-100 text-red-700 border border-red-200';
                                            $statusLabel = 'Nonaktif';
                                            $statusIcon = 'fa-ban';
                                        } else {
                                            $statusClass = 'bg-green-100 text-green-700 border border-green-200';
                                            $statusLabel = 'Aktif';
                                            $statusIcon = 'fa-check-circle';
                                        }
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold flex items-center w-fit {{ $statusClass }}">
                                        <i class="fas {{ $statusIcon }} mr-1.5"></i> {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="py-4 pr-4 text-right space-x-2">
                                    @can('user_edit')
                                        @if(auth()->id() !== $user->id)
                                            @if($user->status !== 'deactivated')
                                                <button type="button" onclick="openDeactivateModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-orange-300 hover:text-orange-600 transition-all shadow-sm" title="Nonaktifkan User">
                                                    <i class="fas fa-ban text-xs"></i>
                                                </button>
                                            @else
                                                <form action="{{ route('users.activate', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Aktifkan kembali user ini?');">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-green-300 hover:text-green-600 transition-all shadow-sm" title="Aktifkan User">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    <a href="{{ route('users.edit', $user->id) }}" 
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-cyan-300 hover:text-cyan-600 transition-all shadow-sm">
                                        <i class="fas fa-pencil-alt text-xs"></i>
                                    </a>
                                    @endcan
                                    @if(auth()->id() !== $user->id)
                                        @can('user_delete')
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-red-300 hover:text-red-600 transition-all shadow-sm">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
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
            <div id="pagination-container" class="mt-6">{{ $users->links() }}</div>
        </div>

        <!-- Deactivate Modal -->
        <div id="deactivateModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeDeactivateModal()"></div>
            <div class="relative bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl m-4 transform scale-95 opacity-0 transition-all duration-300" id="deactivateModalContent">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-500">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Nonaktifkan User</h3>
                        <p class="text-xs text-slate-500" id="deactivateUserName"></p>
                    </div>
                </div>
                
                <form id="deactivateForm" method="POST">
                    @csrf
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Alasan Penonaktifan</label>
                        <textarea name="reason" rows="3" required
                                  placeholder="Contoh: Tidak jelas izin akses cctv nya..."
                                  class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-red-200 focus:border-red-400 transition-all text-sm resize-none"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Alasan ini akan ditampilkan kepada user ketika mereka mencoba login.</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeDeactivateModal()" 
                                class="px-5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                        <button type="submit" 
                                class="px-5 py-2 text-sm font-medium bg-red-500 hover:bg-red-600 text-white rounded-xl shadow-lg shadow-red-500/30 transition-all">Nonaktifkan</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function openDeactivateModal(userId, userName) {
                const modal = document.getElementById('deactivateModal');
                const content = document.getElementById('deactivateModalContent');
                const form = document.getElementById('deactivateForm');
                const nameLabel = document.getElementById('deactivateUserName');
                
                form.action = `/users/${userId}/deactivate`;
                nameLabel.textContent = `Menonaktifkan akun: ${userName}`;
                
                modal.classList.remove('hidden');
                // Trigger reflow
                void modal.offsetWidth;
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }

            function closeDeactivateModal() {
                const modal = document.getElementById('deactivateModal');
                const content = document.getElementById('deactivateModalContent');
                
                content.classList.remove('scale-100', 'opacity-100');
                content.classList.add('scale-95', 'opacity-0');
                
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        </script>
    </main>
</x-app-layout>