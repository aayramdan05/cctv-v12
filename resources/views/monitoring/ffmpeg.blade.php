<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-800">System Health Monitor</h2>
            <p class="text-slate-500 text-sm">Pemantauan performa server node, penyimpanan rekaman, dan status layanan inti.</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT SIDEBAR: RESOURCE MONITORING -->
            <div class="xl:col-span-3 space-y-6">
                <div class="glass-effect rounded-2xl p-6 border border-cyan-100 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-20 h-20 bg-cyan-500/5 rounded-full blur-xl"></div>
                    
                    <h3 class="text-sm font-bold text-slate-800 mb-6 flex items-center gap-2 uppercase tracking-wider">
                        <i class="fas fa-server text-cyan-500"></i> Resource Status
                    </h3>

                    <div class="space-y-6">
                        <!-- HDD -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-bold text-slate-500 uppercase">Penyimpanan (HDD)</span>
                                <span class="text-xs font-mono font-bold text-cyan-600">{{ $resources->disk_usage }}</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-gradient-to-r from-cyan-500 to-blue-500 h-full rounded-full" style="width: 65%"></div>
                            </div>
                        </div>

                        <!-- Bandwidth -->
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                                    <i class="fas fa-network-wired text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase leading-none mb-1">Bandwidth</p>
                                    <p class="text-sm font-bold text-slate-700 leading-none">{{ $resources->bandwidth }}</p>
                                </div>
                            </div>
                            <i class="fas fa-chart-line text-blue-300"></i>
                        </div>

                        <!-- Services List -->
                        <div class="space-y-3 pt-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-600 font-medium">FFMPEG Record</span>
                                <span class="px-2 py-0.5 rounded bg-green-100 text-green-700 text-[10px] font-bold uppercase tracking-tighter border border-green-200">
                                    {{ $resources->ffmpeg_status }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-600 font-medium">ONVIF Agent</span>
                                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-700 text-[10px] font-bold uppercase tracking-tighter border border-blue-200">
                                    {{ $resources->onvif_status }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-600 font-medium">Go2RTC Engine</span>
                                <span class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 text-[10px] font-bold uppercase tracking-tighter border border-indigo-200">
                                    {{ $resources->go2rtc_status }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400 italic">Server Nodes Active:</span>
                                <span class="font-bold text-slate-700">{{ $resources->active_nodes }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mini Legend/Note -->
                <div class="bg-cyan-900 text-white rounded-2xl p-5 shadow-lg relative overflow-hidden">
                    <i class="fas fa-shield-halved absolute -bottom-4 -right-4 text-7xl opacity-10"></i>
                    <h4 class="text-xs font-bold mb-2 uppercase tracking-widest text-cyan-300">System Note</h4>
                    <p class="text-[11px] leading-relaxed opacity-80 italic">Status 'Active' menunjukkan sinkronisasi database dengan file fisik di node server terakhir dilaporkan kurang dari 25 menit yang lalu.</p>
                </div>
            </div>

            <!-- RIGHT SECTION: FILTERS & TABLE -->
            <div class="xl:col-span-9">
                <!-- Advanced Filters -->
                <div class="glass-effect rounded-2xl p-6 border border-cyan-100 shadow-sm mb-6">
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
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:ring-2 focus:ring-cyan-200 transition-all">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        </div>

                        <!-- Building Searchable (Combobox Style) -->
                        <div class="relative">
                            <input type="hidden" name="building_id" :value="selectedBuildingId">
                            <button type="button" @click="open = !open" 
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-left flex justify-between items-center focus:ring-2 focus:ring-cyan-200">
                                <span x-text="selectedBuildingName || 'Semua Gedung'" class="truncate text-sm text-slate-700 font-medium"></span>
                                <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-cloak 
                                 class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden">
                                <div class="p-2 border-b border-slate-50">
                                    <input type="text" x-model="search" placeholder="Ketik nama gedung..." 
                                           class="w-full px-3 py-1.5 text-xs rounded-lg border-slate-200 focus:ring-2 focus:ring-cyan-200">
                                </div>
                                <div class="max-h-48 overflow-y-auto">
                                    <button type="button" @click="selectedBuildingId = ''; selectedBuildingName = ''; open = false; $nextTick(() => $refs.filterForm.submit())" 
                                            class="w-full px-4 py-2 text-left text-xs hover:bg-slate-50 border-b border-slate-50 font-bold text-cyan-600">
                                        SEMUA GEDUNG
                                    </button>
                                    <template x-for="b in filteredBuildings" :key="b.id">
                                        <button type="button" @click="selectBuilding(b)" 
                                                class="w-full px-4 py-2.5 text-left text-xs hover:bg-cyan-50 transition-colors block border-b border-slate-50 last:border-none">
                                            <span class="font-bold text-slate-700" x-text="b.name"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Node Filter -->
                        <select name="server_id" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:ring-2 focus:ring-cyan-200 font-medium text-slate-700">
                            <option value="">Semua Server Node</option>
                            @foreach($servers as $srv)
                                <option value="{{ $srv->id }}" {{ request('server_id') == $srv->id ? 'selected' : '' }}>
                                    Node {{ $srv->id }} ({{ $srv->ip_address }})
                                </option>
                            @endforeach
                        </select>

                        <!-- Placement Filter -->
                        <select name="penempatan" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:ring-2 focus:ring-cyan-200 font-medium text-slate-700">
                            <option value="">Semua Penempatan</option>
                            <option value="Indoor" {{ request('penempatan') == 'Indoor' ? 'selected' : '' }}>Indoor</option>
                            <option value="Outdoor" {{ request('penempatan') == 'Outdoor' ? 'selected' : '' }}>Outdoor</option>
                        </select>
                    </form>
                </div>

                <!-- Table Content -->
                <div class="glass-effect rounded-2xl border border-cyan-100 overflow-hidden shadow-sm">
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
            </div>

        </div>

    </main>
</x-app-layout>