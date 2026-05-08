<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-500">Master Data</span>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Gedung</span>
            </div>
        </div>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Master Gedung</h2>
                <p class="text-slate-500">Kelola data gedung dan lokasi fakultas</p>
            </div>
            <a href="{{ route('building.create') }}" 
               class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all duration-300 flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Tambah Gedung
            </a>
        </div>

        <div class="glass-effect rounded-2xl p-6 border border-cyan-100">
            
        <!-- Seamless Filter & Search Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6" x-data="{
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
                    const res = await fetch(`{{ route('building.index') }}?${params}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    document.getElementById('building-table-body').innerHTML = doc.getElementById('building-table-body').innerHTML;
                    document.getElementById('pagination-container').innerHTML = doc.getElementById('pagination-container').innerHTML;
                    window.history.pushState({}, '', `?${params}`);
                } finally {
                    this.loading = false;
                }
            }
        }">
            <form id="filter-form" action="{{ route('building.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full" @submit.prevent="updateTable()">
                <input type="hidden" name="sort_by" :value="sortBy">
                <input type="hidden" name="sort_dir" :value="sortDir">
                <div class="relative flex-1 min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400 text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           @input.debounce.500ms="updateTable()"
                           placeholder="Cari nama atau kode gedung..." 
                           class="w-full pl-9 pr-4 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white/50 shadow-sm">
                    <div x-show="loading" class="absolute inset-y-0 right-3 flex items-center">
                        <i class="fas fa-circle-notch fa-spin text-cyan-500 text-xs"></i>
                    </div>
                </div>

                <div class="relative">
                    <select name="fakultas" @change="updateTable()" 
                            class="w-64 pl-4 pr-10 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white/50 cursor-pointer shadow-sm appearance-none">
                        <option value="">Semua Fakultas</option>
                        @foreach($faculties as $f)
                            <option value="{{ $f }}" {{ request('fakultas') == $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-3 text-[10px] text-slate-400 pointer-events-none"></i>
                </div>

                @if(request()->anyFilled(['search', 'fakultas']))
                    <a href="{{ route('building.index') }}" class="text-[10px] font-bold text-slate-400 hover:text-red-500 uppercase tracking-wider flex items-center transition-colors ml-2">
                        <i class="fas fa-times-circle mr-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-wider border-b border-cyan-100 select-none">
                            <th class="pb-4 pl-4 font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('kode_gedung')">
                                Kode
                                <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'kode_gedung' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                            </th>
                            <th class="pb-4 font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('nama_gedung')">
                                Nama Gedung
                                <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'nama_gedung' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                            </th>
                            <th class="pb-4 font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('fakultas')">
                                Fakultas
                                <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'fakultas' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                            </th>
                            <th class="pb-4 pr-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="building-table-body" class="text-sm text-slate-600">
                        @forelse ($buildings as $building)
                            <tr class="hover:bg-cyan-50/50 transition-colors group border-b border-slate-50 last:border-none">
                                <td class="py-4 pl-4 font-medium text-cyan-600">
                                    {{ $building->kode_gedung }}
                                </td>
                                <td class="py-4 font-medium text-slate-800">
                                    {{ $building->nama_gedung }}
                                </td>
                                <td class="py-4">
                                    <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-medium">
                                        {{ $building->fakultas ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 pr-4 text-right space-x-2">
                                    <a href="{{ route('building.edit', $building->id) }}" 
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-cyan-300 hover:text-cyan-600 transition-all shadow-sm">
                                        <i class="fas fa-pencil-alt text-xs"></i>
                                    </a>
                                    <form action="{{ route('building.destroy', $building->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus gedung ini? Semua CCTV di dalamnya juga akan terhapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-red-300 hover:text-red-600 transition-all shadow-sm">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <i class="fas fa-building text-4xl mb-3 opacity-50"></i>
                                        <p>Belum ada data gedung.</p>
                                        <p class="text-xs">Silakan tambah data baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="pagination-container" class="mt-6">{{ $buildings->links() }}</div>
        </div>

        </div>
    </main>
</x-app-layout>