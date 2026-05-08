<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-500">Master Data</span>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Fakultas</span>
            </div>
        </div>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Master Fakultas</h2>
                <p class="text-slate-500">Kelola data fakultas dan unit kerja di Unpad</p>
            </div>
            <a href="{{ route('faculties.create') }}" 
               class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all duration-300 flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Tambah Fakultas
            </a>
        </div>

        <div class="glass-effect rounded-2xl p-6 border border-cyan-100">
            
        <!-- Seamless Filter & Search Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6" x-data="{
            loading: false,
            sortBy: '{{ request('sort_by', 'name') }}',
            sortDir: '{{ request('sort_dir', 'asc') }}',
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
                    const res = await fetch(`{{ route('faculties.index') }}?${params}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    document.getElementById('faculty-table-body').innerHTML = doc.getElementById('faculty-table-body').innerHTML;
                    document.getElementById('pagination-container').innerHTML = doc.getElementById('pagination-container').innerHTML;
                    window.history.pushState({}, '', `?${params}`);
                } finally {
                    this.loading = false;
                }
            }
        }">
            <form id="filter-form" action="{{ route('faculties.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full" @submit.prevent="updateTable()">
                <input type="hidden" name="sort_by" :value="sortBy">
                <input type="hidden" name="sort_dir" :value="sortDir">
                <div class="relative flex-1 min-w-[200px] max-w-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400 text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           @input.debounce.500ms="updateTable()"
                           placeholder="Cari nama fakultas..." 
                           class="w-full pl-9 pr-4 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white/50 shadow-sm">
                    <div x-show="loading" class="absolute inset-y-0 right-3 flex items-center">
                        <i class="fas fa-circle-notch fa-spin text-cyan-500 text-xs"></i>
                    </div>
                </div>

                @if(request()->anyFilled(['search']))
                    <a href="{{ route('faculties.index') }}" class="text-[10px] font-bold text-slate-400 hover:text-red-500 uppercase tracking-wider flex items-center transition-colors ml-2">
                        <i class="fas fa-times-circle mr-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-wider border-b border-cyan-100 select-none">
                            <th class="pb-4 pl-4 font-semibold w-16">No</th>
                            <th class="pb-4 font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('name')">
                                Nama Fakultas / Unit Kerja
                                <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'name' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                            </th>
                            <th class="pb-4 pr-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="faculty-table-body" class="text-sm text-slate-600">
                        @forelse ($faculties as $index => $faculty)
                            <tr class="hover:bg-cyan-50/50 transition-colors group border-b border-slate-50 last:border-none">
                                <td class="py-4 pl-4 font-medium text-slate-500">
                                    {{ $faculties->firstItem() + $index }}
                                </td>
                                <td class="py-4">
                                    <span class="px-3 py-1.5 rounded-xl bg-blue-50/80 border border-blue-100 text-blue-700 font-semibold shadow-sm inline-block">
                                        {{ $faculty->name }}
                                    </span>
                                </td>
                                <td class="py-4 pr-4 text-right space-x-2">
                                    <a href="{{ route('faculties.edit', $faculty->id) }}" 
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-cyan-300 hover:text-cyan-600 transition-all shadow-sm">
                                        <i class="fas fa-pencil-alt text-xs"></i>
                                    </a>
                                    <form action="{{ route('faculties.destroy', $faculty->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus fakultas ini? Pastikan tidak ada gedung atau user yang masih menggunakan nama fakultas ini sebelum menghapus.');">
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
                                <td colspan="3" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <i class="fas fa-university text-4xl mb-3 opacity-50"></i>
                                        <p>Belum ada data fakultas.</p>
                                        <p class="text-xs">Silakan tambah data baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="pagination-container" class="mt-6">{{ $faculties->links() }}</div>
        </div>

    </main>
</x-app-layout>
