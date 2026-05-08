<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-500">Master Data</span>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Kamera CCTV</span>
            </div>
        </div>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Master Kamera</h2>
                <p class="text-slate-500">Daftar seluruh perangkat CCTV yang terdaftar</p>
            </div>
            <div class="flex items-center gap-3">
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('cctv.migration') }}" class="px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-medium shadow-sm hover:border-cyan-400 hover:text-cyan-600 transition-all duration-300 flex items-center">
                    <i class="fas fa-file-excel mr-2 text-green-600"></i> Import Excel
                </a>
                @endif
                <a href="{{ route('cctv.create') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all duration-300 flex items-center">
                    <i class="fas fa-plus mr-2"></i> Tambah Kamera
                </a>
            </div>
        </div>

        <!-- Seamless Filter & Search Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6" x-data="{
            loading: false,
            async updateTable() {
                this.loading = true;
                const form = document.getElementById('filter-form');
                const formData = new FormData(form);
                const params = new URLSearchParams(formData).toString();
                
                try {
                    const res = await fetch(`{{ route('cctv.index') }}?${params}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    document.getElementById('cctv-table-body').innerHTML = doc.getElementById('cctv-table-body').innerHTML;
                    document.getElementById('pagination-container').innerHTML = doc.getElementById('pagination-container').innerHTML;
                    
                    // Update URL tanpa reload
                    window.history.pushState({}, '', `?${params}`);
                } catch (e) {
                    console.error('Filter error:', e);
                } finally {
                    this.loading = false;
                }
            }
        }">
            <form id="filter-form" action="{{ route('cctv.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full" @submit.prevent="updateTable()">
                <!-- Search Input Slim -->
                <div class="relative flex-1 min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400 text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           @input.debounce.500ms="updateTable()"
                           placeholder="Cari kamera..." 
                           class="w-full pl-9 pr-4 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white/50 shadow-sm">
                    
                    <!-- Loading Spinner (Seamless) -->
                    <div x-show="loading" class="absolute inset-y-0 right-3 flex items-center">
                        <i class="fas fa-circle-notch fa-spin text-cyan-500 text-xs"></i>
                    </div>
                </div>

                <!-- Dropdown Slims -->
                <div class="relative">
                    <select name="building_id" @change="updateTable()" 
                            class="w-44 pl-4 pr-10 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white/50 cursor-pointer shadow-sm appearance-none">
                        <option value="">Semua Gedung</option>
                        @foreach($buildings as $b)
                            <option value="{{ $b->id }}" {{ request('building_id') == $b->id ? 'selected' : '' }}>{{ $b->nama_gedung }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-3 text-[10px] text-slate-400 pointer-events-none"></i>
                </div>

                <div class="relative">
                    <select name="server_id" @change="updateTable()" 
                            class="w-44 pl-4 pr-10 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white/50 cursor-pointer shadow-sm appearance-none">
                        <option value="">Semua Node</option>
                        @foreach($servers as $s)
                            <option value="{{ $s->id }}" {{ request('server_id') == $s->id ? 'selected' : '' }}>Node {{ $s->id }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-4 top-3 text-[10px] text-slate-400 pointer-events-none"></i>
                </div>

                @if(request()->anyFilled(['search', 'building_id', 'server_id']))
                    <a href="{{ route('cctv.index') }}" class="text-[10px] font-bold text-slate-400 hover:text-red-500 uppercase tracking-wider flex items-center transition-colors ml-2">
                        <i class="fas fa-times-circle mr-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>

        <div x-data="{ 
            selectedIds: [],
            selectAll: false,
            toggleAll() {
                this.selectAll = !this.selectAll;
                if(this.selectAll) {
                    this.selectedIds = Array.from(document.querySelectorAll('.cctv-checkbox')).map(el => el.value);
                } else {
                    this.selectedIds = [];
                }
            }
        }">
            <!-- Bulk Action Bar (Floating) -->
            <div x-show="selectedIds.length > 0" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-10"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 bg-slate-900 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-6 border border-slate-700">
                <div class="flex items-center gap-2 border-r border-slate-700 pr-6">
                    <span class="bg-cyan-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold" x-text="selectedIds.length"></span>
                    <span class="text-sm font-medium">Kamera Terpilih</span>
                </div>
                
                <form action="{{ route('cctv.bulkMove') }}" method="POST" class="flex items-center gap-4">
                    @csrf
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="cctv_ids[]" :value="id">
                    </template>

                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">Pindahkan ke:</span>
                        <select name="target_server_id" required class="bg-slate-800 border-slate-700 text-white text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 p-2">
                            <option value="">Pilih Server Node...</option>
                            @foreach($servers as $s)
                                <option value="{{ $s->id }}">Node {{ $s->id }} ({{ $s->name }})</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="px-5 py-2 bg-cyan-500 hover:bg-cyan-600 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                        <i class="fas fa-exchange-alt"></i> Pindahkan Sekarang
                    </button>
                    
                    <button type="button" @click="selectedIds = []; selectAll = false" class="text-slate-400 hover:text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </form>
            </div>

            <div class="glass-effect rounded-2xl p-6 border border-cyan-100">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-slate-400 text-xs uppercase tracking-wider border-b border-cyan-100">
                                <th class="pb-4 pl-4 font-semibold w-10">
                                    <input type="checkbox" @click="toggleAll()" :checked="selectAll" class="rounded border-slate-300 text-cyan-500 focus:ring-cyan-200">
                                </th>
                                <th class="pb-4 font-semibold">Kode</th>
                                <th class="pb-4 font-semibold">Nama / Lokasi</th>
                                <th class="pb-4 font-semibold">IP Address</th>
                                <th class="pb-4 font-semibold">Server Node</th>
                                <th class="pb-4 pr-4 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="cctv-table-body" class="text-sm text-slate-600">
                            @forelse ($cctvs as $cctv)
                                <tr class="hover:bg-cyan-50/50 transition-colors border-b border-slate-50 last:border-none" :class="selectedIds.includes('{{ $cctv->id }}') ? 'bg-cyan-50/30' : ''">
                                    <td class="py-4 pl-4">
                                        <input type="checkbox" value="{{ $cctv->id }}" x-model="selectedIds" class="cctv-checkbox rounded border-slate-300 text-cyan-500 focus:ring-cyan-200">
                                    </td>
                                    <td class="py-4 font-medium text-cyan-600">{{ $cctv->kode_cctv }}</td>
                                    <td class="py-4">
                                        <div class="font-medium text-slate-800">{{ $cctv->nama_cctv }}</div>
                                        <div class="text-xs text-slate-400 mt-1">
                                            <i class="fas fa-building mr-1"></i> {{ $cctv->building->nama_gedung }}
                                        </div>
                                    </td>
                                    <td class="py-4 font-mono text-xs">{{ $cctv->ip ?? '-' }}</td>
                                    <td class="py-4">
                                        <span class="px-2 py-1 bg-slate-100 rounded text-[10px] font-bold text-slate-500">
                                            NODE {{ $cctv->server_id }}
                                        </span>
                                    </td>
                                    <td class="py-4 pr-4 text-right space-x-2">
                                        <a href="{{ route('cctv.edit', $cctv->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-cyan-300 hover:text-cyan-600 transition-all shadow-sm">
                                            <i class="fas fa-pencil-alt text-xs"></i>
                                        </a>
                                        <form action="{{ route('cctv.destroy', $cctv->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus kamera ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-red-300 hover:text-red-600 transition-all shadow-sm">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-12 text-center text-slate-400">Belum ada data kamera.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div id="pagination-container" class="mt-6">{{ $cctvs->links() }}</div>
            </div>
        </div>
    </main>
</x-app-layout>