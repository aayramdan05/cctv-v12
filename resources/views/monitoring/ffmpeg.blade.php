<x-app-layout>
    <div class="text-slate-800 pb-32 pt-6 px-6 max-w-[1600px] mx-auto" x-data="{ 
        activeNode: 'MASTER', 
        showNginxModal: false,
        nginxContent: 'Loading...',
        async loadNginxConfig() {
            try {
                this.nginxContent = 'Mengambil konfigurasi dari server...';
                const res = await fetch(`{{ route('ffmpeg.nginx') }}`);
                const data = await res.json();
                this.nginxContent = data.config;
            } catch (e) {
                this.nginxContent = 'Gagal memuat file Nginx.';
            }
        }
    }">
        
        <!-- Header -->
        <header class="mb-6">
            <div class="flex items-center text-xs text-slate-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-cyan-600"><i class="fas fa-home"></i></a>
                <span class="mx-2">/</span>
                <span class="text-slate-500">Monitoring</span>
                <span class="mx-2">/</span>
                <span class="text-slate-700 font-medium">System Health</span>
            </div>
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">System Health</h1>
                    <p class="text-slate-500 mt-1 text-sm">Status perekaman kamera real-time di setiap node server.</p>
                </div>
            </div>
        </header>

        <main class="space-y-6">
            
            <!-- Search & Filter Area -->
            <section class="flex flex-col gap-4">
                <form id="filter-form" action="{{ route('ffmpeg.monitor') }}" method="GET">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400 text-sm"></i>
                        </div>
                        <input name="search" value="{{ request('search') }}" class="w-full pl-10 pr-6 py-3 bg-white/50 glass-effect border border-cyan-100 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all text-sm outline-none shadow-sm" placeholder="Cari nama kamera, kode, atau IP..." type="text"/>
                    </div>
                </form>
            </section>

            <!-- Node Status Section -->
            <section class="space-y-6">
                <div class="flex gap-4 overflow-x-auto pb-2 px-2 no-scrollbar">
                    <button @click="activeNode = 'MASTER'" 
                            :class="activeNode === 'MASTER' ? 'bg-cyan-600 text-white shadow-md' : 'bg-white border border-cyan-100 text-slate-500 hover:bg-slate-50'"
                            class="flex-none px-6 py-2 rounded-full text-xs font-bold tracking-wider transition-all uppercase">
                        MASTER
                    </button>
                    @foreach($serverStats as $s)
                        <button @click="activeNode = '{{ $s->id }}'" 
                                :class="activeNode === '{{ $s->id }}' ? 'bg-cyan-600 text-white shadow-md' : 'bg-white border border-cyan-100 text-slate-500 hover:bg-slate-50'"
                                class="flex-none px-6 py-2 rounded-full text-xs font-bold tracking-wider transition-all uppercase">
                            NODE {{ $s->id }}
                        </button>
                    @endforeach
                </div>

                <div class="flex justify-between items-end px-2">
                    <h2 class="text-xl font-bold text-slate-800 m-0">Node Status</h2>
                    <span class="text-xs font-bold tracking-wider text-cyan-600">1 MASTER + {{ count($serverStats) }} NODES</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Master Server Detail -->
                    <div x-show="activeNode === 'MASTER'" class="bg-white/50 glass-effect border border-cyan-200 rounded-xl shadow-sm p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-star text-cyan-600"></i>
                                    <h3 class="text-sm font-bold text-slate-800 m-0">Master Server (Primary)</h3>
                                </div>
                                <code class="font-mono text-xs text-slate-500">{{ request()->getHost() }}</code>
                            </div>
                            <span class="px-2 py-0.5 bg-cyan-50 text-cyan-700 border border-cyan-200 text-[10px] font-bold rounded uppercase">Online</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div class="p-2 bg-white/50 rounded-lg border border-cyan-50">
                                <p class="text-[10px] text-slate-500 font-bold tracking-wider m-0">CPU LOAD</p>
                                <p class="text-sm font-bold text-cyan-600 m-0">Normal</p>
                            </div>
                            <div class="p-2 bg-white/50 rounded-lg border border-cyan-50">
                                <p class="text-[10px] text-slate-500 font-bold tracking-wider m-0">SYSTEM</p>
                                <p class="text-sm font-bold text-cyan-600 m-0">Active</p>
                            </div>
                        </div>
                    </div>

                    <!-- Nodes Detail -->
                    @foreach($serverStats as $stat)
                        <div x-show="activeNode === '{{ $stat->id }}'" class="bg-white/50 glass-effect border border-cyan-200 rounded-xl shadow-sm p-4" style="display: none;">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-server text-slate-500"></i>
                                        <h3 class="text-sm font-bold text-slate-800 m-0">{{ $stat->name }}</h3>
                                    </div>
                                    <code class="font-mono text-xs text-slate-500">{{ $stat->ip }}</code>
                                </div>
                                <span class="px-2 py-0.5 bg-cyan-50 text-cyan-700 border border-cyan-200 text-[10px] font-bold rounded uppercase">Online</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div class="p-2 bg-white/50 rounded-lg border border-cyan-50">
                                    <p class="text-[10px] text-slate-500 font-bold tracking-wider m-0">CAMERAS</p>
                                    <p class="text-sm font-bold text-cyan-600 m-0">{{ $stat->total }} Total</p>
                                </div>
                                <div class="p-2 bg-white/50 rounded-lg border border-cyan-50">
                                    <p class="text-[10px] text-slate-500 font-bold tracking-wider m-0">RECORDING</p>
                                    <p class="text-sm font-bold text-cyan-600 m-0">{{ $stat->active }} Active</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Database Actions -->
                <section class="space-y-4">
                    <h2 class="text-xl font-bold text-slate-800 px-2 m-0">Database</h2>
                    <div class="bg-white/50 glass-effect border border-cyan-100 rounded-xl overflow-hidden shadow-sm">
                        <div class="p-6 flex flex-col gap-6">
                            <div class="flex items-center gap-6 p-4 bg-white/50 rounded-lg border border-cyan-100">
                                <div class="p-4 bg-slate-100 text-slate-700 rounded-lg">
                                    <i class="fas fa-database text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold m-0">SQL Cluster 01</p>
                                    <p class="text-xs font-bold tracking-wider text-slate-500 m-0">LAST BACKUP: 2 HOURS AGO</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-4">
                                <button onclick="window.location.href='{{ route('ffmpeg.backup') }}'" class="flex items-center justify-center gap-2 p-4 bg-white hover:bg-slate-50 text-cyan-700 border border-cyan-200 rounded-lg font-bold shadow-sm transition-all text-xs">
                                    <i class="fas fa-download"></i>
                                    BACKUP DATABASE
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Nginx Configuration -->
                <section class="space-y-4">
                    <h2 class="text-xl font-bold text-slate-800 px-2 m-0">Infrastructure</h2>
                    <div class="bg-white/50 glass-effect border border-cyan-100 rounded-xl p-6 shadow-sm">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-4">
                                <div class="w-3 h-3 bg-cyan-600 rounded-full animate-pulse shadow-[0_0_8px_rgba(6,182,212,0.6)]"></div>
                                <div>
                                    <p class="text-sm font-bold m-0">Nginx Configuration</p>
                                    <p class="text-xs text-cyan-600 m-0 font-medium">Active/Running</p>
                                </div>
                            </div>
                            <button class="p-2 bg-slate-100 text-slate-500 rounded-lg hover:bg-slate-200 transition-colors">
                                <i class="fas fa-server"></i>
                            </button>
                        </div>
                        <div class="mt-6">
                            <button @click="showNginxModal = true; loadNginxConfig()" class="w-full py-3 bg-white border border-cyan-200 text-cyan-700 rounded-lg font-bold shadow-sm transition-all hover:bg-cyan-50 text-xs flex items-center justify-center gap-2">
                                <i class="fas fa-file-code"></i> VIEW CONFIG FILE
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Recording Summary (Cameras) -->
            <section class="space-y-4">
                <h2 class="text-xl font-bold text-slate-800 px-2 m-0">Cameras Detail</h2>
                <div class="bg-white/50 glass-effect border border-cyan-100 rounded-xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto no-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50/90 backdrop-blur-sm shadow-sm">
                                <tr class="text-[11px] text-slate-500 uppercase border-b border-cyan-100 font-medium">
                                    <th class="px-5 py-3 font-medium">Camera</th>
                                    <th class="px-5 py-3 font-medium">Node</th>
                                    <th class="px-5 py-3 font-medium">Status</th>
                                    <th class="px-5 py-3 font-medium">Last Record</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-cyan-100/50 text-sm">
                                @forelse($cctvs as $cctv)
                                    @php
                                        $isRecording = false;
                                        $lastUpdateText = 'Never';

                                        if ($cctv->latest_rec_created_at) {
                                            $createdTime = \Carbon\Carbon::parse($cctv->latest_rec_created_at);
                                            if ($createdTime->diffInMinutes(now()) < 25) {
                                                $isRecording = true;
                                            }
                                            $lastUpdateText = $createdTime->diffForHumans();
                                        }
                                    @endphp
                                    <tr class="hover:bg-cyan-50/50 transition-colors">
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-video text-xs text-slate-400"></i>
                                                <div>
                                                    <p class="font-bold text-slate-700 m-0">{{ $cctv->nama_cctv }}</p>
                                                    <p class="text-[10px] font-mono text-slate-500 m-0">{{ $cctv->kode_cctv }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 font-bold text-slate-600">
                                            {{ $cctv->server_id ? 'Node ' . $cctv->server_id : 'MASTER' }}
                                        </td>
                                        <td class="px-5 py-3">
                                            @if($isRecording)
                                                <div class="flex items-center gap-2">
                                                    <span class="w-1.5 h-1.5 bg-cyan-500 rounded-full animate-pulse shadow-[0_0_5px_rgba(6,182,212,0.5)]"></span>
                                                    <span class="text-[11px] text-cyan-700 font-medium uppercase">Recording</span>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-2">
                                                    <span class="w-1.5 h-1.5 bg-slate-300 rounded-full"></span>
                                                    <span class="text-[11px] text-slate-500 font-medium uppercase">Idle</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-slate-500">{{ $lastUpdateText }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-5 py-8 text-center text-slate-500">Tidak ada data kamera.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($cctvs->hasPages())
                        <div class="px-5 py-3 bg-white/30 border-t border-cyan-100 text-xs">
                            {{ $cctvs->links() }}
                        </div>
                    @endif
                </div>
            </section>
        </main>

        <!-- Nginx Config Modal -->
        <div x-show="showNginxModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div @click.away="showNginxModal = false" class="bg-white rounded-2xl w-full max-w-3xl shadow-xl flex flex-col max-h-[90vh] mx-4"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="scale-95 opacity-0 translate-y-4"
                 x-transition:enter-end="scale-100 opacity-100 translate-y-0">
                
                <div class="flex justify-between items-center p-6 border-b border-cyan-100/50 bg-slate-50 rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-alt text-cyan-600 text-xl"></i>
                        <h3 class="text-lg font-bold text-slate-800 m-0">Nginx Configuration</h3>
                    </div>
                    <button @click="showNginxModal = false" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto font-mono text-sm bg-slate-900 text-slate-300 flex-1">
<pre class="m-0 leading-relaxed" x-text="nginxContent">
</pre>
                </div>
                
                <div class="p-4 bg-slate-50 border-t border-cyan-100/50 flex justify-end gap-3 rounded-b-2xl">
                    <button @click="navigator.clipboard.writeText(nginxContent); alert('Copied to clipboard!')" class="px-4 py-2 bg-white border border-cyan-200 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-50 shadow-sm transition-colors">
                        COPY TO CLIPBOARD
                    </button>
                    <button @click="showNginxModal = false" class="px-4 py-2 bg-cyan-600 text-white text-xs font-bold rounded-lg hover:bg-cyan-700 shadow-sm transition-all">
                        DONE
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>