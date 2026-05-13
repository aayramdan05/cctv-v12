<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-800">System Health Monitor</h2>
            <p class="text-slate-500 text-sm">Pemantauan performa server node, penyimpanan rekaman, dan status layanan inti secara real-time.</p>
        </div>

        <!-- TOP RESOURCE MONITORING: GRID PER NODE -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            @foreach($nodeStats as $stat)
            <div class="glass-effect rounded-2xl p-6 border border-cyan-100 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i class="fas fa-server text-6xl text-cyan-600"></i>
                </div>
                
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500 text-white flex items-center justify-center shadow-lg shadow-cyan-500/20">
                        <i class="fas fa-microchip"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 leading-none mb-1">Node {{ $stat->id }}</h3>
                        <p class="text-xs font-mono text-cyan-600 font-bold tracking-wider">{{ $stat->ip }}</p>
                    </div>
                    <div class="ml-auto">
                        <span class="px-3 py-1 rounded-full bg-green-50 text-green-600 text-[10px] font-bold uppercase tracking-widest border border-green-100">
                            {{ $stat->go2rtc_status }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-8">
                    <!-- Storage Info -->
                    <div class="space-y-3 border-r border-slate-100 pr-4">
                        <div class="flex justify-between items-end">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Storage Cluster</span>
                            <span class="text-xs font-mono font-bold text-slate-700">{{ $stat->disk_text }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-50">
                            <div class="bg-gradient-to-r from-cyan-500 to-blue-500 h-full rounded-full transition-all duration-1000" style="width: {{ $stat->disk_percent }}%"></div>
                        </div>
                        <p class="text-[10px] text-slate-400 text-right">{{ $stat->disk_percent }}% Full</p>
                        
                        <div class="mt-4 flex items-center justify-between p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">Est. Bandwidth</span>
                            <span class="text-xs font-bold text-cyan-700">{{ $stat->bandwidth }}</span>
                        </div>
                    </div>

                    <!-- SYNC VERIFICATION (NEW) -->
                    <div class="space-y-3">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Sync Verification</span>
                        
                        <!-- DB vs Go2RTC -->
                        <div class="flex items-center justify-between p-2 rounded-xl border {{ $stat->db_count == $stat->go2rtc_count ? 'bg-emerald-50 border-emerald-100' : 'bg-amber-50 border-amber-200' }}">
                            <span class="text-[9px] font-bold {{ $stat->db_count == $stat->go2rtc_count ? 'text-emerald-600' : 'text-amber-600' }}">DB vs Go2RTC</span>
                            <span class="text-xs font-bold {{ $stat->db_count == $stat->go2rtc_count ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $stat->db_count }} / {{ $stat->go2rtc_count }}
                            </span>
                        </div>

                        <!-- FFMPEG Process -->
                        <div class="flex items-center justify-between p-2 rounded-xl border {{ $stat->ffmpeg_count == $stat->db_count ? 'bg-blue-50 border-blue-100' : 'bg-rose-50 border-rose-200' }}">
                            <span class="text-[9px] font-bold {{ $stat->ffmpeg_count == $stat->db_count ? 'text-blue-600' : 'text-rose-600' }}">Active Recording</span>
                            <span class="text-xs font-bold {{ $stat->ffmpeg_count == $stat->db_count ? 'text-blue-700' : 'text-rose-700' }}">
                                {{ $stat->ffmpeg_count }} Proc
                            </span>
                        </div>

                        @if(count($stat->missing_ids) > 0)
                        <div class="mt-2 p-2 bg-rose-50 border border-rose-100 rounded-xl">
                            <span class="text-[8px] font-bold text-rose-500 uppercase block mb-1">Missing Cam IDs (In DB but not recording):</span>
                            <div class="flex flex-wrap gap-1">
                                @foreach($stat->missing_ids as $id)
                                    <span class="px-1.5 py-0.5 bg-rose-100 text-rose-700 text-[9px] font-bold rounded">#{{ $id }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="flex gap-2">
                             <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[8px] font-bold uppercase border border-slate-200">
                                API 1985: {{ $stat->status }}
                            </span>
                             <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[8px] font-bold uppercase border border-slate-200">
                                RTC 1984: {{ $stat->go2rtc_status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- FILTERS SECTION (Full Width) -->
        <div class="glass-effect rounded-2xl p-6 border border-cyan-100 shadow-sm mb-6 relative z-[100]">
            <form action="{{ route('ffmpeg.monitor') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4" x-data="{ 
                open: false, 
                search: '{{ request('search') }}',
                selectedBuildingId: '{{ request('building_id') }}',
                selectedBuildingName: '{{ request('building_id') ? $buildings->firstWhere('id', request('building_id'))->nama_gedung : '' }}',
                buildings: [
                    @foreach($buildings as $b)
                        { id: '{{ $b->id }}', name: '{{ $b->nama_gedung }}' },
                    @endforeach
                ],
                get filteredBuildings() {
                    if (this.search === '') return this.buildings;
                    return this.buildings.filter(b => b.name.toLowerCase().includes(this.search.toLowerCase()));
                },
                selectBuilding(b) {
                    this.selectedBuildingId = b.id;
                    this.selectedBuildingName = b.name;
                    this.open = false;
                    $nextTick(() => $refs.filterForm.submit());
                }
            }" x-ref="filterForm">
                
                <!-- Search Box -->
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Kamera / IP..." 
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm focus:ring-2 focus:ring-cyan-200 transition-all shadow-sm">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                </div>

                <!-- Building Searchable (Combobox Style) -->
                <div class="relative">
                    <input type="hidden" name="building_id" :value="selectedBuildingId">
                    <button type="button" @click="open = !open" 
                            class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-left flex justify-between items-center focus:ring-2 focus:ring-cyan-200 shadow-sm">
                        <span x-text="selectedBuildingName || 'Semua Gedung'" class="truncate text-sm text-slate-700 font-bold"></span>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" x-cloak 
                         class="absolute z-[110] w-full mt-2 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden shadow-cyan-500/20">
                        <div class="p-2 border-b border-slate-50 bg-slate-50/50">
                            <input type="text" x-model="search" placeholder="Ketik nama gedung..." 
                                   class="w-full px-3 py-1.5 text-xs rounded-lg border-slate-200 focus:ring-2 focus:ring-cyan-200">
                        </div>
                        <div class="max-h-64 overflow-y-auto custom-scrollbar">
                            <button type="button" @click="selectedBuildingId = ''; selectedBuildingName = ''; open = false; $nextTick(() => $refs.filterForm.submit())" 
                                    class="w-full px-4 py-3 text-left text-xs hover:bg-slate-50 border-b border-slate-50 font-bold text-cyan-600 uppercase tracking-widest">
                                <i class="fas fa-list-ul mr-2"></i> SEMUA GEDUNG
                            </button>
                            <template x-for="b in filteredBuildings" :key="b.id">
                                <button type="button" @click="selectBuilding(b)" 
                                        class="w-full px-4 py-3 text-left text-xs hover:bg-cyan-50 transition-colors block border-b border-slate-50 last:border-none">
                                    <span class="font-bold text-slate-700" x-text="b.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Node Filter -->
                <select name="server_id" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm focus:ring-2 focus:ring-cyan-200 font-bold text-slate-700 shadow-sm">
                    <option value="">Semua Server Node</option>
                    @foreach($servers as $srv)
                        <option value="{{ $srv->id }}" {{ request('server_id') == $srv->id ? 'selected' : '' }}>
                            Node {{ $srv->id }} ({{ $srv->ip_address }})
                        </option>
                    @endforeach
                </select>

                <!-- Placement Filter -->
                <select name="penempatan" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-sm focus:ring-2 focus:ring-cyan-200 font-bold text-slate-700 shadow-sm">
                    <option value="">Semua Penempatan</option>
                    <option value="Indoor" {{ request('penempatan') == 'Indoor' ? 'selected' : '' }}>Indoor</option>
                    <option value="Outdoor" {{ request('penempatan') == 'Outdoor' ? 'selected' : '' }}>Outdoor</option>
                </select>
            </form>
        </div>

        <!-- TABLE CONTENT (Full Width) -->
        <div class="glass-effect rounded-2xl border border-cyan-100 overflow-hidden shadow-sm relative z-10">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/50 border-b border-slate-100 text-[10px] uppercase text-slate-500 font-bold tracking-widest">
                        <tr>
                            <th class="p-4">Kamera & Kode</th>
                            <th class="p-4">Resource Node</th>
                            <th class="p-4">Gedung & Unit</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4">Update Terakhir</th>
                            <th class="p-4 text-right">Data Rate</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse($statusData as $cam)
                        <tr class="border-b border-slate-50 hover:bg-white/40 transition group">
                            <td class="p-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-700 group-hover:text-cyan-600 transition-colors">{{ $cam->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $cam->kode }}</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-server text-xs text-slate-300"></i>
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded border border-slate-200 text-[10px] font-mono">
                                        {{ $cam->server_ip }}
                                    </span>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex flex-col">
                                    <span class="text-slate-600 text-xs font-medium">{{ $cam->building }}</span>
                                    <span class="text-[9px] text-slate-400 uppercase tracking-tighter">{{ $cam->penempatan }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                @if($cam->is_recording)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-50 text-green-700 text-[9px] font-bold uppercase tracking-wider border border-green-100">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.5)]"></span>
                                        Recording
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-50 text-slate-400 text-[9px] font-bold uppercase tracking-wider border border-slate-100">
                                        <span class="w-1.5 h-1.5 bg-slate-300 rounded-full"></span>
                                        Idle
                                    </span>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="flex flex-col">
                                    <span class="text-slate-600 text-xs">{{ $cam->last_update }}</span>
                                    <span class="text-[9px] text-slate-400 font-mono truncate max-w-[120px]">{{ $cam->filename }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-right">
                                <span class="font-mono font-bold text-slate-700 bg-slate-50 px-2 py-1 rounded border border-slate-100">
                                    {{ $cam->file_size }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center">
                                <div class="flex flex-col items-center opacity-30">
                                    <i class="fas fa-search-minus text-4xl mb-4"></i>
                                    <p class="text-sm font-medium">Tidak ada data kamera yang cocok dengan filter.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</x-app-layout>