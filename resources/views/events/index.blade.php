<x-app-layout>
    <div class="font-body-md text-slate-800 pb-32 pt-6 px-6 max-w-[1600px] mx-auto" x-data="{ 
        activeTab: '{{ $activeTab }}',
        camFilter: 'all', 
        page: 1, 
        perPage: 10,
        cameras: [
            @foreach($cameras as $cam)
            {
                id: {{ $cam->id }},
                nama: '{{ addslashes($cam->nama_cctv) }}',
                ip: '{{ $cam->ip }}',
                port: '{{ $cam->onvif_port ?? 80 }}',
                hasOnvif: {{ (!empty($cam->onvif_user) || !empty($cam->onvif_password)) ? 'true' : 'false' }},
                editUrl: '{{ route('cctv.edit', $cam->id) }}'
            },
            @endforeach
        ],
        get filteredCameras() {
            return this.cameras.filter(c => {
                if (this.camFilter === 'configured') return c.hasOnvif;
                if (this.camFilter === 'missing') return !c.hasOnvif;
                return true;
            });
        },
        get paginatedCameras() {
            let start = (this.page - 1) * this.perPage;
            return this.filteredCameras.slice(start, start + this.perPage);
        },
        get totalPages() {
            return Math.ceil(this.filteredCameras.length / this.perPage) || 1;
        }
    }">
        <!-- Header -->
        <header class="mb-6">
            <div class="flex items-center text-xs text-slate-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-cyan-600"><i class="fas fa-home"></i></a>
                <span class="mx-2">/</span>
                <span class="text-slate-500">Manajemen</span>
                <span class="mx-2">/</span>
                <span class="text-slate-700 font-medium">ONVIF Status</span>
            </div>
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">ONVIF Status</h1>
                    <p class="text-slate-500 mt-1 text-sm">Pantau status konfigurasi ONVIF dan riwayat kejadian (Event Logs) kamera.</p>
                </div>
                @if($onvifEvents->count() > 0 || $intelEvents->count() > 0)
                    <form action="{{ route('events.markAllRead') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-cyan-200 rounded-lg text-xs font-semibold shadow-sm transition-all flex items-center gap-2">
                            <i class="fas fa-check-double text-cyan-500"></i> Mark All Read
                        </button>
                    </form>
                @endif
            </div>
        </header>

        <main class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- Total -->
                <div class="bg-white/50 glass-effect p-4 rounded-xl border border-cyan-100 flex flex-col shadow-sm">
                    <span class="text-[10px] font-semibold text-slate-500 mb-1 tracking-wider uppercase">TOTAL KAMERA</span>
                    <div class="flex items-end justify-between">
                        <span class="text-2xl font-bold text-slate-800">{{ $totalCameras }}</span>
                        <i class="fas fa-video text-slate-300 text-xl"></i>
                    </div>
                </div>
                <!-- Configured -->
                <div class="bg-white/50 glass-effect p-4 rounded-xl border-l-4 border-l-emerald-500 border-cyan-100 flex flex-col shadow-sm">
                    <span class="text-[10px] font-semibold text-emerald-600 mb-1 tracking-wider uppercase">ONVIF CONFIGURED</span>
                    <div class="flex items-end justify-between">
                        <span class="text-2xl font-bold text-emerald-600">{{ $hasOnvifCount }}</span>
                        <i class="fas fa-check-circle text-emerald-200 text-xl"></i>
                    </div>
                </div>
                <!-- Not Configured -->
                <div class="bg-white/50 glass-effect p-4 rounded-xl border-l-4 border-l-amber-500 border-cyan-100 flex flex-col shadow-sm">
                    <span class="text-[10px] font-semibold text-amber-600 mb-1 tracking-wider uppercase">NOT CONFIGURED</span>
                    <div class="flex items-end justify-between">
                        <span class="text-2xl font-bold text-amber-600">{{ $noOnvifCount }}</span>
                        <i class="fas fa-exclamation-circle text-amber-200 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Cameras ONVIF Status Table -->
            <section class="bg-white/50 glass-effect border border-cyan-100 rounded-xl overflow-hidden shadow-sm">
                <div class="px-5 py-3 border-b border-cyan-100 bg-white/30 flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <h2 class="text-base font-bold text-slate-700">ONVIF Status</h2>
                        <div class="flex bg-white border border-cyan-100 rounded text-[10px] font-bold shadow-sm overflow-hidden">
                            <button @click="camFilter = 'all'; page = 1" :class="camFilter === 'all' ? 'bg-cyan-600 text-white' : 'text-slate-600 hover:bg-slate-50'" class="px-3 py-1 transition-colors">ALL</button>
                            <button @click="camFilter = 'configured'; page = 1" :class="camFilter === 'configured' ? 'bg-cyan-600 text-white' : 'text-slate-600 hover:bg-slate-50'" class="px-3 py-1 transition-colors border-l border-r border-cyan-100">CONFIGURED</button>
                            <button @click="camFilter = 'missing'; page = 1" :class="camFilter === 'missing' ? 'bg-cyan-600 text-white' : 'text-slate-600 hover:bg-slate-50'" class="px-3 py-1 transition-colors">MISSING</button>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-slate-400">
                        <button @click="camFilter = 'all'; page = 1" class="hover:text-cyan-600 transition-colors" title="Reload"><i class="fas fa-redo-alt text-sm"></i></button>
                        <button class="hover:text-cyan-600 transition-colors" title="Expand"><i class="fas fa-expand text-sm"></i></button>
                    </div>
                </div>
                <div class="overflow-x-auto min-h-[300px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-slate-50/90 backdrop-blur-sm shadow-sm z-10">
                            <tr class="text-[11px] text-slate-500 uppercase border-b border-cyan-100 font-medium">
                                <th class="px-5 py-2.5 font-medium">Status ONVIF</th>
                                <th class="px-4 py-2.5 font-medium">Nama Kamera</th>
                                <th class="px-4 py-2.5 font-medium">IP Address</th>
                                <th class="px-4 py-2.5 font-medium">ONVIF Port</th>
                                <th class="px-5 py-2.5 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cyan-100/50 text-sm">
                            <template x-for="cam in paginatedCameras" :key="cam.id">
                                <tr class="hover:bg-cyan-50/50 transition-colors" :class="!cam.hasOnvif ? 'bg-amber-50/30' : ''">
                                    <td class="px-5 py-2">
                                        <div x-show="cam.hasOnvif" class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full shadow-[0_0_5px_rgba(16,185,129,0.5)]"></span>
                                            <span class="text-[11px] text-emerald-700">CONFIGURED</span>
                                        </div>
                                        <div x-show="!cam.hasOnvif" class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse shadow-[0_0_5px_rgba(245,158,11,0.5)]"></span>
                                            <span class="text-[11px] text-amber-700">MISSING</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-video text-xs" :class="cam.hasOnvif ? 'text-cyan-600' : 'text-amber-500'"></i>
                                            <span class="text-slate-700" x-text="cam.nama"></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <code class="font-mono text-xs text-slate-600" x-text="cam.ip"></code>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="bg-slate-100 px-1.5 py-0.5 rounded text-[10px] text-slate-600" x-text="cam.port"></span>
                                    </td>
                                    <td class="px-5 py-2 text-right">
                                        <a :href="cam.editUrl" class="p-1.5 text-slate-400 hover:text-cyan-600 hover:bg-cyan-50 rounded-lg transition-colors" title="Edit Kamera">
                                            <i class="fas fa-cog text-xs"></i>
                                        </a>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredCameras.length === 0">
                                <td colspan="5" class="px-5 py-8 text-center text-slate-500 text-sm">
                                    Tidak ada kamera yang cocok dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-5 py-3 bg-white/30 border-t border-cyan-100 flex items-center justify-between text-xs text-slate-500">
                    <div>
                        Menampilkan <span x-text="filteredCameras.length > 0 ? (page - 1) * perPage + 1 : 0"></span> - <span x-text="Math.min(page * perPage, filteredCameras.length)"></span> dari <span x-text="filteredCameras.length"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="page--" :disabled="page === 1" class="px-2 py-1 bg-white border border-cyan-200 rounded hover:bg-slate-50 disabled:opacity-50 transition-colors">&laquo; Prev</button>
                        <button @click="page++" :disabled="page === totalPages || totalPages === 0" class="px-2 py-1 bg-white border border-cyan-200 rounded hover:bg-slate-50 disabled:opacity-50 transition-colors">Next &raquo;</button>
                    </div>
                </div>
            </section>

            <!-- Logs Section -->
            <section class="bg-white/50 glass-effect border border-cyan-100 rounded-xl overflow-hidden shadow-sm mt-8">
                <div class="px-5 py-3 border-b border-cyan-100 bg-white/30 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h2 class="text-base font-bold text-slate-700">Riwayat Kejadian (Logs)</h2>
                    <div class="flex items-center gap-3">
                        <div class="flex bg-white border border-cyan-100 rounded-lg p-1 shadow-sm">
                            <button @click="activeTab = 'onvif'" :class="activeTab === 'onvif' ? 'bg-cyan-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50'" class="px-3 py-1 rounded text-[10px] font-bold transition-all uppercase">Log ONVIF Event</button>
                            <button @click="activeTab = 'intelligence'" :class="activeTab === 'intelligence' ? 'bg-cyan-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50'" class="px-3 py-1 rounded text-[10px] font-bold transition-all uppercase">Log Intelligence Event</button>
                        </div>
                        <a :href="`{{ route('events.exportCsv') }}?type=${activeTab}`" class="px-3 py-1 bg-white border border-cyan-200 text-cyan-700 rounded text-[10px] font-bold hover:bg-cyan-50 shadow-sm transition-all flex items-center gap-2">
                            <i class="fas fa-file-csv text-cyan-600"></i> EXPORT CSV
                        </a>
                    </div>
                </div>
                
                <!-- ONVIF Table -->
                <div x-show="activeTab === 'onvif'" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 shadow-sm">
                            <tr class="text-[11px] text-slate-500 uppercase border-b border-cyan-100">
                                <th class="px-5 py-2.5 font-medium w-16">Status</th>
                                <th class="px-4 py-2.5 font-medium">Waktu</th>
                                <th class="px-4 py-2.5 font-medium">Kamera</th>
                                <th class="px-4 py-2.5 font-medium">Lokasi</th>
                                <th class="px-4 py-2.5 font-medium">Tipe Event</th>
                                <th class="px-5 py-2.5 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cyan-100/50 text-sm">
                            @forelse($onvifEvents as $event)
                                <tr class="hover:bg-cyan-50/50 transition-colors {{ !$event->is_read ? 'bg-cyan-50/30' : '' }}">
                                    <td class="px-5 py-2 text-center">
                                        @if(!$event->is_read)
                                            <span class="w-2 h-2 bg-orange-500 rounded-full inline-block animate-pulse shadow-[0_0_5px_rgba(249,115,22,0.6)]" title="Unread"></span>
                                        @else
                                            <span class="w-2 h-2 bg-slate-300 rounded-full inline-block" title="Read"></span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-slate-800">{{ $event->created_at->format('d M Y') }}</span>
                                            <span class="text-[11px] text-slate-500">{{ $event->created_at->format('H:i:s') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="text-slate-700 flex items-center gap-2">
                                            <i class="fas fa-video text-[10px] opacity-70"></i>
                                            {{ $event->cctv->nama_cctv ?? 'Unknown' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="text-slate-600">
                                            {{ $event->cctv->building->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="bg-slate-100 px-2 py-0.5 rounded-md text-[10px] text-slate-600 uppercase border border-slate-200 shadow-sm">
                                            {{ $event->event_type }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-2 text-right">
                                        @if(!$event->is_read)
                                            <form action="{{ route('events.read', $event->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 bg-white border border-cyan-200 text-cyan-600 hover:bg-cyan-50 hover:border-cyan-300 rounded-lg shadow-sm transition-all" title="Tandai Dibaca">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-slate-500 text-sm">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fas fa-folder-open text-3xl text-slate-300"></i>
                                            <p>Tidak ada log event ONVIF saat ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($onvifEvents->hasPages())
                    <div x-show="activeTab === 'onvif'" class="px-5 py-3 bg-white/30 border-t border-cyan-100 text-xs">
                        {{ $onvifEvents->links() }}
                    </div>
                @endif

                <!-- Intelligence Table -->
                <div x-show="activeTab === 'intelligence'" x-cloak class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 shadow-sm">
                            <tr class="text-[11px] text-slate-500 uppercase border-b border-cyan-100">
                                <th class="px-5 py-2.5 font-medium w-16">Status</th>
                                <th class="px-4 py-2.5 font-medium">Waktu</th>
                                <th class="px-4 py-2.5 font-medium">Kamera</th>
                                <th class="px-4 py-2.5 font-medium">Lokasi</th>
                                <th class="px-4 py-2.5 font-medium">Tipe Event</th>
                                <th class="px-5 py-2.5 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cyan-100/50 text-sm">
                            @forelse($intelEvents as $event)
                                <tr class="hover:bg-cyan-50/50 transition-colors {{ !$event->is_read ? 'bg-cyan-50/30' : '' }}">
                                    <td class="px-5 py-2 text-center">
                                        @if(!$event->is_read)
                                            <span class="w-2 h-2 bg-orange-500 rounded-full inline-block animate-pulse shadow-[0_0_5px_rgba(249,115,22,0.6)]" title="Unread"></span>
                                        @else
                                            <span class="w-2 h-2 bg-slate-300 rounded-full inline-block" title="Read"></span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-slate-800">{{ $event->created_at->format('d M Y') }}</span>
                                            <span class="text-[11px] text-slate-500">{{ $event->created_at->format('H:i:s') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="text-slate-700 flex items-center gap-2">
                                            <i class="fas fa-video text-[10px] opacity-70"></i>
                                            {{ $event->cctv->nama_cctv ?? 'Unknown' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="text-slate-600">
                                            {{ $event->cctv->building->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="bg-slate-100 px-2 py-0.5 rounded-md text-[10px] text-slate-600 uppercase border border-slate-200 shadow-sm">
                                            {{ $event->event_type }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-2 text-right">
                                        @if(!$event->is_read)
                                            <form action="{{ route('events.read', $event->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 bg-white border border-cyan-200 text-cyan-600 hover:bg-cyan-50 hover:border-cyan-300 rounded-lg shadow-sm transition-all" title="Tandai Dibaca">
                                                    <i class="fas fa-check text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-slate-500 text-sm">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fas fa-folder-open text-3xl text-slate-300"></i>
                                            <p>Tidak ada log event Intelligence saat ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($intelEvents->hasPages())
                    <div x-show="activeTab === 'intelligence'" x-cloak class="px-5 py-3 bg-white/30 border-t border-cyan-100 text-xs">
                        {{ $intelEvents->links() }}
                    </div>
                @endif
            </section>
        </main>
    </div>
</x-app-layout>
