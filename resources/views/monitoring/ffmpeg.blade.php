<x-app-layout>
    @push('scripts')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <style>
      .font-body-md { font-family: 'Inter', sans-serif; }
      .no-scrollbar::-webkit-scrollbar { display: none; }
      .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    @endpush

    <div class="font-body-md text-slate-800 pb-32" x-data="{ 
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
        
        <!-- Top AppBar -->
        <header class="sticky top-[64px] md:top-0 z-40 flex justify-between items-center px-container-margin py-stack-md w-full max-w-full bg-transparent shadow-sm">
            <div class="flex items-center gap-stack-sm">
                <span class="material-symbols-outlined text-cyan-600" data-icon="health_and_safety">health_and_safety</span>
                <h1 class="font-headline-lg-mobile text-headline-lg-mobile text-slate-800 m-0">System Health</h1>
            </div>
            <div class="flex gap-stack-sm">
                <button class="p-base rounded-full hover:bg-slate-100/50 active:scale-95 duration-100 transition-all">
                    <span class="material-symbols-outlined text-slate-500" data-icon="search">search</span>
                </button>
                <button class="p-base rounded-full hover:bg-slate-100/50 active:scale-95 duration-100 transition-all">
                    <span class="material-symbols-outlined text-slate-500" data-icon="notifications">notifications</span>
                </button>
            </div>
        </header>

        <main class="px-container-margin mt-stack-md space-y-gutter max-w-7xl mx-auto">
            
            <!-- Search & Filter Area -->
            <section class="flex flex-col gap-stack-sm">
                <form id="filter-form" action="{{ route('ffmpeg.monitor') }}" method="GET">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-stack-md flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-outline text-body-md" data-icon="search">search</span>
                        </div>
                        <input name="search" value="{{ request('search') }}" class="w-full pl-10 pr-stack-md py-stack-sm bg-white/50 glass-effect border border-cyan-100 rounded-xl focus:ring-2 focus:ring-primary focus:border-cyan-500 transition-all text-body-md outline-none" placeholder="Cari nama kamera, kode, atau IP..." type="text"/>
                    </div>
                </form>
                <p class="text-slate-500 text-body-md px-base">Status perekaman kamera real-time di setiap node server.</p>
            </section>

            <!-- Node Status Section -->
            <section class="space-y-stack-md">
                <div class="flex gap-stack-sm overflow-x-auto pb-base px-base no-scrollbar">
                    <button @click="activeNode = 'MASTER'" 
                            :class="activeNode === 'MASTER' ? 'bg-cyan-600 text-white' : 'bg-slate-100/50 text-slate-500 hover:bg-slate-200/50'"
                            class="flex-none px-stack-md py-base rounded-full font-label-caps text-label-caps transition-all">
                        MASTER
                    </button>
                    @foreach($serverStats as $s)
                        <button @click="activeNode = '{{ $s->id }}'" 
                                :class="activeNode === '{{ $s->id }}' ? 'bg-cyan-600 text-white' : 'bg-slate-100/50 text-slate-500 hover:bg-slate-200/50'"
                                class="flex-none px-stack-md py-base rounded-full font-label-caps text-label-caps transition-all">
                            NODE {{ $s->id }}
                        </button>
                    @endforeach
                </div>

                <div class="flex justify-between items-end px-base">
                    <h2 class="font-headline-md text-headline-md text-slate-800 m-0">Node Status</h2>
                    <span class="text-label-caps font-label-caps text-cyan-600">1 MASTER + {{ count($serverStats) }} NODES</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-stack-sm">
                    <!-- Master Server Detail -->
                    <div x-show="activeNode === 'MASTER'" class="bg-white/50 glass-effect border border-cyan-500 rounded-xl shadow-sm p-stack-sm">
                        <div class="flex justify-between items-start mb-base">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-base">
                                    <span class="material-symbols-outlined text-cyan-600 text-[16px]" data-icon="star">star</span>
                                    <h3 class="text-body-md font-bold text-slate-800 m-0">Master Server (Primary)</h3>
                                </div>
                                <code class="font-data-mono text-[11px] text-slate-500">{{ request()->getHost() }}</code>
                            </div>
                            <span class="px-base py-[2px] bg-cyan-600/10 text-cyan-600 text-[10px] font-bold rounded uppercase">Online</span>
                        </div>
                        <div class="grid grid-cols-2 gap-stack-sm mt-stack-sm">
                            <div class="p-base bg-white/30 rounded-lg">
                                <p class="text-[10px] text-slate-500 font-label-caps m-0">CPU LOAD</p>
                                <p class="text-body-md font-bold text-cyan-600 m-0">Normal</p>
                            </div>
                            <div class="p-base bg-white/30 rounded-lg">
                                <p class="text-[10px] text-slate-500 font-label-caps m-0">SYSTEM</p>
                                <p class="text-body-md font-bold text-cyan-600 m-0">Active</p>
                            </div>
                        </div>
                    </div>

                    <!-- Nodes Detail -->
                    @foreach($serverStats as $stat)
                        <div x-show="activeNode === '{{ $stat->id }}'" class="bg-white/50 glass-effect border border-cyan-100 rounded-xl shadow-sm p-stack-sm" style="display: none;">
                            <div class="flex justify-between items-start mb-base">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-base">
                                        <span class="material-symbols-outlined text-slate-500 text-[16px]" data-icon="dns">dns</span>
                                        <h3 class="text-body-md font-bold text-slate-800 m-0">{{ $stat->name }}</h3>
                                    </div>
                                    <code class="font-data-mono text-[11px] text-slate-500">{{ $stat->ip }}</code>
                                </div>
                                <span class="px-base py-[2px] bg-cyan-600/10 text-cyan-600 text-[10px] font-bold rounded uppercase">Online</span>
                            </div>
                            <div class="grid grid-cols-2 gap-stack-sm mt-stack-sm">
                                <div class="p-base bg-white/30 rounded-lg">
                                    <p class="text-[10px] text-slate-500 font-label-caps m-0">CAMERAS</p>
                                    <p class="text-body-md font-bold text-cyan-600 m-0">{{ $stat->total }} Total</p>
                                </div>
                                <div class="p-base bg-white/30 rounded-lg">
                                    <p class="text-[10px] text-slate-500 font-label-caps m-0">RECORDING</p>
                                    <p class="text-body-md font-bold text-cyan-600 m-0">{{ $stat->active }} Active</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter">
                <!-- Database Actions -->
                <section class="space-y-stack-md">
                    <h2 class="font-headline-md text-headline-md text-slate-800 px-base m-0">Database</h2>
                    <div class="bg-white/50 glass-effect border border-cyan-100 rounded-xl overflow-hidden">
                        <div class="p-card-padding flex flex-col gap-stack-md">
                            <div class="flex items-center gap-stack-md p-stack-sm bg-white/30 rounded-lg border border-cyan-100/30">
                                <div class="p-stack-sm bg-slate-200 text-slate-700 rounded-lg">
                                    <span class="material-symbols-outlined" data-icon="database">database</span>
                                </div>
                                <div>
                                    <p class="text-body-md font-bold m-0">SQL Cluster 01</p>
                                    <p class="text-label-caps text-slate-500 m-0">LAST BACKUP: 2 HOURS AGO</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-stack-sm">
                                <button onclick="window.location.href='{{ route('ffmpeg.backup') }}'" class="flex flex-col items-center justify-center gap-base p-stack-md bg-cyan-600 text-white rounded-lg active:scale-95 transition-all">
                                    <span class="material-symbols-outlined" data-icon="backup">backup</span>
                                    <span class="text-label-caps">Backup Database</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Nginx Configuration -->
                <section class="space-y-stack-md">
                    <h2 class="font-headline-md text-headline-md text-slate-800 px-base m-0">Infrastructure</h2>
                    <div class="bg-white/50 glass-effect border border-cyan-100 rounded-xl p-card-padding">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-stack-sm">
                                <div class="w-3 h-3 bg-cyan-600 rounded-full animate-pulse shadow-[0_0_8px_rgba(6,182,212,0.6)]"></div>
                                <div>
                                    <p class="text-body-md font-bold m-0">Nginx Configuration</p>
                                    <p class="text-body-md text-cyan-600 m-0">Active/Running</p>
                                </div>
                            </div>
                            <button class="p-stack-sm bg-slate-100/50 text-slate-500 rounded-lg hover:bg-slate-200/50 transition-colors">
                                <span class="material-symbols-outlined" data-icon="settings_ethernet">settings_ethernet</span>
                            </button>
                        </div>
                        <div class="mt-stack-md">
                            <button @click="showNginxModal = true; loadNginxConfig()" class="w-full py-stack-sm bg-white/30 border border-cyan-100 text-slate-500 rounded-lg font-label-caps transition-all hover:bg-slate-100/50">
                                VIEW CONFIG FILE
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Recording Summary (Cameras) -->
            <section class="space-y-stack-md">
                <h2 class="font-headline-md text-headline-md text-slate-800 px-base m-0">Cameras Detail</h2>
                <div class="bg-white/50 glass-effect border border-cyan-100 rounded-xl overflow-hidden">
                    <div class="overflow-x-auto no-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-cyan-100/30">
                                    <th class="p-stack-md text-label-caps font-label-caps text-slate-500 uppercase">Camera</th>
                                    <th class="p-stack-md text-label-caps font-label-caps text-slate-500 uppercase">Node</th>
                                    <th class="p-stack-md text-label-caps font-label-caps text-slate-500 uppercase">Status</th>
                                    <th class="p-stack-md text-label-caps font-label-caps text-slate-500 uppercase">Last Record</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/30">
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
                                    <tr class="hover:bg-white/30 transition-colors">
                                        <td class="p-stack-md">
                                            <p class="text-body-md font-bold m-0">{{ $cctv->nama_cctv }}</p>
                                            <p class="text-[10px] font-mono text-slate-500 m-0">{{ $cctv->kode_cctv }}</p>
                                        </td>
                                        <td class="p-stack-md text-body-md font-bold text-slate-500">
                                            {{ $cctv->server_id ? 'Node ' . $cctv->server_id : 'MASTER' }}
                                        </td>
                                        <td class="p-stack-md">
                                            @if($isRecording)
                                                <span class="px-base py-[2px] bg-cyan-600/10 text-cyan-600 text-[10px] font-bold rounded uppercase">Recording</span>
                                            @else
                                                <span class="px-base py-[2px] bg-slate-200 text-slate-700 text-[10px] font-bold rounded uppercase">Idle</span>
                                            @endif
                                        </td>
                                        <td class="p-stack-md text-body-md text-slate-500">{{ $lastUpdateText }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-stack-md text-center text-slate-500 text-body-md">Tidak ada data kamera.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($cctvs->hasPages())
                        <div class="p-stack-sm bg-white/30 border-t border-cyan-100/30 text-center">
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
            <div @click.away="showNginxModal = false" class="bg-transparent rounded-2xl w-full max-w-3xl shadow-xl flex flex-col max-h-[90vh]"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="scale-95 opacity-0 translate-y-4"
                 x-transition:enter-end="scale-100 opacity-100 translate-y-0">
                
                <div class="flex justify-between items-center p-stack-md border-b border-cyan-100/30">
                    <div class="flex items-center gap-stack-sm">
                        <span class="material-symbols-outlined text-cyan-600">description</span>
                        <h3 class="font-headline-md m-0">Nginx Configuration</h3>
                    </div>
                    <button @click="showNginxModal = false" class="p-base text-slate-500 hover:bg-slate-100/50 rounded-full transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <div class="p-stack-md overflow-y-auto font-data-mono text-[12px] bg-[#1e1e1e] text-[#d4d4d4] flex-1">
<pre class="m-0 leading-relaxed" x-text="nginxContent">
</pre>
                </div>
                
                <div class="p-stack-sm bg-white/30 border-t border-cyan-100/30 flex justify-end gap-stack-sm rounded-b-2xl">
                    <button @click="navigator.clipboard.writeText(nginxContent); alert('Copied to clipboard!')" class="px-stack-md py-stack-sm bg-slate-100/50 text-slate-500 font-label-caps rounded-lg hover:bg-slate-200/50 transition-colors">
                        COPY TO CLIPBOARD
                    </button>
                    <button @click="showNginxModal = false" class="px-stack-md py-stack-sm bg-cyan-600 text-white font-label-caps rounded-lg hover:brightness-110 transition-all">
                        DONE
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>