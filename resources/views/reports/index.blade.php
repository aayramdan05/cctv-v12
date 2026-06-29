<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        <!-- Breadcrumb -->
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-500">Manajemen</span>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Report CCTV</span>
            </div>
        </div>

        <!-- Title & Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Report CCTV</h2>
                <p class="text-slate-500">Unduh data master CCTV lengkap beserta relasi gedung, fakultas, server, dan statusnya.</p>
            </div>
            
            <!-- Export Actions -->
            <div class="flex items-center gap-3" x-data="{
                getExportUrl(type) {
                    const form = document.getElementById('filter-form');
                    const params = form ? new URLSearchParams(new FormData(form)).toString() : '';
                    const baseRoute = type === 'csv' ? '{{ route('reports.export.csv') }}' : '{{ route('reports.export.pdf') }}';
                    return `${baseRoute}?${params}`;
                }
            }">
                <a :href="getExportUrl('csv')" class="px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-medium shadow-sm hover:border-green-400 hover:text-green-600 hover:bg-green-50/20 transition-all duration-300 flex items-center">
                    <i class="fas fa-file-csv mr-2 text-green-600 text-lg"></i> Export CSV
                </a>
                <a :href="getExportUrl('pdf')" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-500 to-orange-500 text-white font-medium shadow-lg shadow-red-500/20 hover:shadow-red-500/40 transition-all duration-300 flex items-center">
                    <i class="fas fa-file-pdf mr-2 text-lg"></i> Export PDF
                </a>
            </div>
        </div>

        <!-- Filter & Table Wrapper (Alpine JS Context) -->
        <div x-data="{
            loading: false,
            async updateTable() {
                this.loading = true;
                const form = document.getElementById('filter-form');
                const params = new URLSearchParams(new FormData(form)).toString();
                
                try {
                    const res = await fetch(`{{ route('reports.index') }}?${params}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    
                    document.getElementById('cctv-table-body').innerHTML = doc.getElementById('cctv-table-body').innerHTML;
                    document.getElementById('pagination-container').innerHTML = doc.getElementById('pagination-container').innerHTML;
                    
                    // Update URL without page reload
                    window.history.pushState({}, '', `?${params}`);
                } catch (e) {
                    console.error('Filter error:', e);
                } finally {
                    this.loading = false;
                }
            }
        }">
            <!-- Filter Form -->
            <div class="glass-effect rounded-2xl p-6 border border-cyan-100 mb-6">
                <form id="filter-form" action="{{ route('reports.index') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full" @submit.prevent="updateTable()">
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[240px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400 text-xs"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               @input.debounce.500ms="updateTable()"
                               placeholder="Cari nama cctv, kode, atau IP..." 
                               class="w-full pl-9 pr-10 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white/50 shadow-sm">
                        
                        <div x-show="loading" class="absolute inset-y-0 right-3 flex items-center">
                            <i class="fas fa-circle-notch fa-spin text-cyan-500 text-xs"></i>
                        </div>
                    </div>

                    <!-- Dropdown Searchable Gedung -->
                    <div class="relative" x-data="{ 
                        open: false, 
                        search: '', 
                        selectedId: '{{ request('building_id') }}',
                        selectedName: '{{ request('building_id') ? $buildings->find(request('building_id'))->nama_gedung : 'Semua Gedung' }}',
                        buildings: [
                            @foreach($buildings as $b)
                                { id: '{{ $b->id }}', name: '{{ $b->nama_gedung }}' },
                            @endforeach
                        ],
                        get filtered() {
                            if (this.search === '') return this.buildings;
                            return this.buildings.filter(b => b.name.toLowerCase().includes(this.search.toLowerCase()));
                        }
                    }">
                        <input type="hidden" name="building_id" :value="selectedId">
                        <button type="button" @click="open = !open" 
                                class="w-48 pl-4 pr-10 py-2 rounded-xl border border-slate-200 text-left text-sm bg-white shadow-sm focus:ring-2 focus:ring-cyan-100 flex justify-between items-center">
                            <span x-text="selectedName" class="truncate"></span>
                            <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak 
                             class="absolute z-50 w-64 mt-2 bg-white rounded-xl shadow-2xl border border-slate-100 overflow-hidden">
                            <div class="p-2 border-b border-slate-50 bg-slate-50">
                                <input type="text" x-model="search" placeholder="Cari gedung..." 
                                       class="w-full px-3 py-1.5 text-xs rounded-lg border-slate-200 focus:ring-2 focus:ring-cyan-100">
                            </div>
                            <div class="max-h-60 overflow-y-auto custom-scrollbar">
                                <button type="button" @click="selectedId = ''; selectedName = 'Semua Gedung'; open = false; updateTable()" 
                                        class="w-full px-4 py-2 text-left text-xs hover:bg-cyan-50 font-medium text-slate-500 italic">
                                    -- Semua Gedung --
                                </button>
                                <template x-for="b in filtered" :key="b.id">
                                    <button type="button" @click="selectedId = b.id; selectedName = b.name; open = false; updateTable()" 
                                            class="w-full px-4 py-2 text-left text-xs hover:bg-cyan-50 transition-colors">
                                        <span x-text="b.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Server Node Dropdown -->
                    <div class="relative">
                        <select name="server_id" @change="updateTable()" 
                                class="w-40 pl-4 pr-10 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white cursor-pointer shadow-sm appearance-none">
                            <option value="">Semua Node</option>
                            @foreach($servers as $s)
                                <option value="{{ $s->id }}" {{ request('server_id') == $s->id ? 'selected' : '' }}>Node {{ $s->id }} ({{ $s->name }})</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-3 text-[10px] text-slate-400 pointer-events-none"></i>
                    </div>

                    <!-- Penempatan Dropdown -->
                    <div class="relative">
                        <select name="penempatan" @change="updateTable()" 
                                class="w-40 pl-4 pr-10 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white cursor-pointer shadow-sm appearance-none">
                            <option value="">Semua Lokasi</option>
                            <option value="Indoor" {{ request('penempatan') == 'Indoor' ? 'selected' : '' }}>Indoor</option>
                            <option value="Outdoor" {{ request('penempatan') == 'Outdoor' ? 'selected' : '' }}>Outdoor</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-3 text-[10px] text-slate-400 pointer-events-none"></i>
                    </div>

                    <!-- Status Dropdown -->
                    <div class="relative">
                        <select name="status" @change="updateTable()" 
                                class="w-40 pl-4 pr-10 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white cursor-pointer shadow-sm appearance-none">
                            <option value="">Semua Status</option>
                            <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>Offline</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-3 text-[10px] text-slate-400 pointer-events-none"></i>
                    </div>

                    <!-- Reset Button -->
                    @if(request()->anyFilled(['search', 'building_id', 'server_id', 'penempatan', 'status']))
                        <a href="{{ route('reports.index') }}" class="text-[10px] font-bold text-slate-400 hover:text-red-500 uppercase tracking-wider flex items-center transition-colors ml-2">
                            <i class="fas fa-times-circle mr-1"></i> Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- CCTV Table Card -->
            <div class="glass-effect rounded-2xl p-6 border border-cyan-100">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-slate-400 text-xs uppercase tracking-wider border-b border-cyan-100 select-none">
                                <th class="pb-4 pl-4 font-semibold w-12">No.</th>
                                <th class="pb-4 font-semibold w-32">Kode CCTV</th>
                                <th class="pb-4 font-semibold w-64">Nama CCTV</th>
                                <th class="pb-4 font-semibold w-36">IP Address</th>
                                <th class="pb-4 font-semibold w-40">Penempatan</th>
                                <th class="pb-4 font-semibold w-56">Gedung & Fakultas</th>
                                <th class="pb-4 font-semibold w-40">Server Node</th>
                                <th class="pb-4 font-semibold w-44">Koordinat</th>
                                <th class="pb-4 pr-4 font-semibold w-24 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="cctv-table-body" class="text-sm text-slate-600">
                            @forelse ($cctvs as $index => $cctv)
                                <tr class="hover:bg-cyan-50/50 transition-colors border-b border-slate-50 last:border-none">
                                    <td class="py-4 pl-4 font-medium text-slate-400">
                                        {{ ($cctvs->currentPage() - 1) * $cctvs->perPage() + $index + 1 }}
                                    </td>
                                    <td class="py-4 font-semibold text-cyan-600 font-mono">{{ $cctv->kode_cctv }}</td>
                                    <td class="py-4">
                                        <div class="font-semibold text-slate-800">{{ $cctv->nama_cctv }}</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">ID: #{{ $cctv->id }}</div>
                                    </td>
                                    <td class="py-4 font-mono text-xs">{{ $cctv->ip ?? '-' }}</td>
                                    <td class="py-4">
                                        @if($cctv->penempatan === 'Indoor')
                                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded text-[10px] font-bold inline-flex items-center">
                                                <i class="fas fa-door-open mr-1"></i> INDOOR
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 bg-orange-100 text-orange-700 rounded text-[10px] font-bold inline-flex items-center">
                                                <i class="fas fa-cloud-sun mr-1"></i> OUTDOOR
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4">
                                        <div class="font-medium text-slate-700">{{ $cctv->building ? $cctv->building->nama_gedung : '-' }}</div>
                                        <div class="text-xs text-slate-400 mt-1 flex items-center">
                                            <i class="fas fa-university mr-1"></i> {{ $cctv->building ? $cctv->building->fakultas : '-' }}
                                        </div>
                                    </td>
                                    <td class="py-4">
                                        @if($cctv->server)
                                            <div class="font-semibold text-slate-700 text-xs">Node {{ $cctv->server_id }}</div>
                                            <div class="text-[10px] text-slate-400 mt-0.5 font-mono">{{ $cctv->server->ip_address }}</div>
                                        @else
                                            <span class="px-2 py-1 bg-slate-100 text-slate-500 rounded text-[10px] font-semibold">
                                                SINGLE / MASTER
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4">
                                        @if($cctv->lat && $cctv->lng)
                                            <div class="text-xs text-slate-700 font-mono">{{ round($cctv->lat, 5) }}</div>
                                            <div class="text-xs text-slate-400 font-mono mt-0.5">{{ round($cctv->lng, 5) }}</div>
                                        @else
                                            <span class="text-slate-300 italic text-xs">Belum diplot</span>
                                        @endif
                                    </td>
                                    <td class="py-4 pr-4 text-center">
                                        @if($cctv->status === 'online')
                                            <span class="px-2 py-0.5 bg-green-500 text-white rounded-full text-[10px] font-bold tracking-wide shadow-sm shadow-green-500/20 inline-block w-16">
                                                ONLINE
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 bg-red-500 text-white rounded-full text-[10px] font-bold tracking-wide shadow-sm shadow-red-500/20 inline-block w-16">
                                                OFFLINE
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-12 text-center text-slate-400">
                                        <i class="fas fa-camera-retro text-4xl text-slate-200 mb-3 block"></i>
                                        Belum ada data kamera atau pencarian tidak cocok.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Container -->
                <div id="pagination-container" class="mt-6">
                    {{ $cctvs->links() }}
                </div>
            </div>
        </div>
    </main>
</x-app-layout>
