<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-500">Infrastruktur</span>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Master Server</span>
            </div>
        </div>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Server Nodes</h2>
                <p class="text-slate-500">Kelola node server perekam (NVR) terdistribusi.</p>
            </div>
            <a href="{{ route('servers.create') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium shadow-lg hover:shadow-cyan-500/50 transition-all flex items-center gap-2">
                <i class="fas fa-server"></i> Tambah Node
            </a>
        </div>

        <div class="glass-effect rounded-2xl p-6 border border-cyan-100 overflow-hidden" x-data="{
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
                    const res = await fetch(`{{ route('servers.index') }}?${params}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    document.getElementById('server-table-body').innerHTML = doc.getElementById('server-table-body').innerHTML;
                    document.getElementById('pagination-container').innerHTML = doc.getElementById('pagination-container').innerHTML;
                    window.history.pushState({}, '', `?${params}`);
                } finally {
                    this.loading = false;
                }
            }
        }">
            
        <!-- Seamless Search Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <form id="filter-form" action="{{ route('servers.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full" @submit.prevent="updateTable()">
                <input type="hidden" name="sort_by" :value="sortBy">
                <input type="hidden" name="sort_dir" :value="sortDir">
                <div class="relative flex-1 min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400 text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           @input.debounce.500ms="updateTable()"
                           placeholder="Cari nama server atau IP..." 
                           class="w-full pl-9 pr-4 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white/50 shadow-sm">
                    <div x-show="loading" class="absolute inset-y-0 right-3 flex items-center">
                        <i class="fas fa-circle-notch fa-spin text-cyan-500 text-xs"></i>
                    </div>
                </div>

                @if(request('search'))
                    <a href="{{ route('servers.index') }}" class="text-[10px] font-bold text-slate-400 hover:text-red-500 uppercase tracking-wider flex items-center transition-colors ml-2">
                        <i class="fas fa-times-circle mr-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>


            <table class="w-full text-left border-collapse">
                <thead class="text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100 select-none">
                    <tr>
                        <th class="pb-4 pl-4 font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('name')">
                            Nama Server
                            <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'name' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                        </th>
                        <th class="pb-4 font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('ip_address')">
                            IP Address
                            <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'ip_address' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                        </th>
                        <th class="pb-4 font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('location')">
                            Lokasi
                            <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'location' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                        </th>
                        <th class="pb-4 text-center font-semibold">Beban</th>
                        <th class="pb-4 text-center font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('is_active')">
                            Status
                            <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'is_active' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                        </th>
                        <th class="pb-4 pr-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody id="server-table-body" class="text-sm text-slate-600">
                    @forelse ($servers as $server)
                    <tr class="hover:bg-slate-50/50 transition border-b border-slate-50 last:border-none">
                        <td class="py-4 pl-4 font-bold text-slate-700">{{ $server->name }}</td>
                        <td class="py-4 font-mono text-cyan-600">{{ $server->ip_address }}</td>
                        <td class="py-4">{{ $server->location ?? '-' }}</td>
                        <td class="py-4 text-center">
                            <span class="px-2 py-1 bg-slate-100 rounded text-xs font-bold">{{ $server->cctvs_count }} Kamera</span>
                        </td>
                        <td class="py-4 text-center">
                            @if($server->is_active)
                                <span class="px-2 py-1 bg-green-100 text-green-600 rounded-full text-xs font-bold">Active</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-600 rounded-full text-xs font-bold">Inactive</span>
                            @endif
                        </td>
                        <td class="py-4 pr-4 text-right flex justify-end gap-2">
                            <a href="{{ route('servers.edit', $server->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border hover:border-cyan-300 hover:text-cyan-600 text-slate-400 transition shadow-sm"><i class="fas fa-pencil-alt"></i></a>
                            
                            <form action="{{ route('servers.destroy', $server->id) }}" method="POST" onsubmit="return confirm('Yakin hapus server ini? Pastikan kosong dulu.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border hover:border-red-300 hover:text-red-600 text-slate-400 transition shadow-sm"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-10 text-center text-slate-400">Belum ada server node.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div id="pagination-container" class="mt-4">{{ $servers->links() }}</div>
        </div>
    </main>
</x-app-layout>