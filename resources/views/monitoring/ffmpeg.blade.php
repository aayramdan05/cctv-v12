<x-app-layout>
    @push('scripts')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
      try{
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              colors: {
                      "surface-dim": "#cbdbf5",
                      "on-secondary-container": "#5c647a",
                      "on-surface-variant": "#3d4947",
                      "on-tertiary": "#ffffff",
                      "surface-container-low": "#eff4ff",
                      "secondary-container": "#dae2fd",
                      "on-tertiary-container": "#fffbff",
                      "on-primary": "#ffffff",
                      "inverse-surface": "#213145",
                      "tertiary-container": "#b05e3d",
                      "on-secondary-fixed-variant": "#3f465c",
                      "on-error": "#ffffff",
                      "on-error-container": "#93000a",
                      "tertiary": "#924628",
                      "background": "#f8f9ff",
                      "primary": "#00685f",
                      "surface-bright": "#f8f9ff",
                      "on-tertiary-fixed-variant": "#773215",
                      "secondary-fixed-dim": "#bec6e0",
                      "primary-fixed": "#89f5e7",
                      "error": "#ba1a1a",
                      "primary-container": "#008378",
                      "surface-container-highest": "#d3e4fe",
                      "on-surface": "#0b1c30",
                      "secondary": "#565e74",
                      "secondary-fixed": "#dae2fd",
                      "surface-container-lowest": "#ffffff",
                      "outline": "#6d7a77",
                      "tertiary-fixed-dim": "#ffb59a",
                      "inverse-primary": "#6bd8cb",
                      "surface": "#f8f9ff",
                      "on-tertiary-fixed": "#370e00",
                      "on-primary-fixed-variant": "#005049",
                      "surface-tint": "#006a61",
                      "outline-variant": "#bcc9c6",
                      "surface-container-high": "#dce9ff",
                      "error-container": "#ffdad6",
                      "surface-variant": "#d3e4fe",
                      "on-primary-fixed": "#00201d",
                      "inverse-on-surface": "#eaf1ff",
                      "surface-container": "#e5eeff",
                      "on-secondary-fixed": "#131b2e",
                      "tertiary-fixed": "#ffdbce",
                      "on-background": "#0b1c30",
                      "on-secondary": "#ffffff",
                      "on-primary-container": "#f4fffc",
                      "primary-fixed-dim": "#6bd8cb"
              },
              borderRadius: {
                      "DEFAULT": "0.125rem",
                      "lg": "0.25rem",
                      "xl": "0.5rem",
                      "full": "0.75rem"
              },
              spacing: {
                      "gutter": "20px",
                      "base": "4px",
                      "stack-md": "16px",
                      "stack-sm": "8px",
                      "container-margin": "24px",
                      "card-padding": "20px"
              },
              fontFamily: {
                      "body-md": ["Inter"],
                      "body-lg": ["Inter"],
                      "headline-md": ["Inter"],
                      "headline-lg-mobile": ["Inter"],
                      "data-mono": ["JetBrains Mono"],
                      "headline-lg": ["Inter"],
                      "label-caps": ["Inter"]
              },
              fontSize: {
                      "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                      "body-lg": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                      "headline-md": ["18px", {"lineHeight": "24px", "letterSpacing": "-0.01em", "fontWeight": "600"}],
                      "headline-lg-mobile": ["20px", {"lineHeight": "28px", "fontWeight": "700"}],
                      "data-mono": ["13px", {"lineHeight": "16px", "fontWeight": "500"}],
                      "headline-lg": ["24px", {"lineHeight": "32px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                      "label-caps": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}]
              }
            }
          }
        }
      }catch(_e){}
    </script>
    <style>
      .font-body-md { font-family: 'Inter', sans-serif; }
      .no-scrollbar::-webkit-scrollbar { display: none; }
      .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    @endpush

    <div class="font-body-md text-on-surface pb-32" x-data="{ activeNode: 'MASTER', showNginxModal: false }">
        
        <!-- Top AppBar -->
        <header class="sticky top-[64px] md:top-0 z-40 flex justify-between items-center px-container-margin py-stack-md w-full max-w-full bg-surface shadow-sm">
            <div class="flex items-center gap-stack-sm">
                <span class="material-symbols-outlined text-primary" data-icon="health_and_safety">health_and_safety</span>
                <h1 class="font-headline-lg-mobile text-headline-lg-mobile text-on-surface m-0">System Health</h1>
            </div>
            <div class="flex gap-stack-sm">
                <button class="p-base rounded-full hover:bg-surface-container-high active:scale-95 duration-100 transition-all">
                    <span class="material-symbols-outlined text-on-surface-variant" data-icon="search">search</span>
                </button>
                <button class="p-base rounded-full hover:bg-surface-container-high active:scale-95 duration-100 transition-all">
                    <span class="material-symbols-outlined text-on-surface-variant" data-icon="notifications">notifications</span>
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
                        <input name="search" value="{{ request('search') }}" class="w-full pl-10 pr-stack-md py-stack-sm bg-surface-container-lowest border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition-all text-body-md outline-none" placeholder="Cari nama kamera, kode, atau IP..." type="text"/>
                    </div>
                </form>
                <p class="text-on-surface-variant text-body-md px-base">Status perekaman kamera real-time di setiap node server.</p>
            </section>

            <!-- Node Status Section -->
            <section class="space-y-stack-md">
                <div class="flex gap-stack-sm overflow-x-auto pb-base px-base no-scrollbar">
                    <button @click="activeNode = 'MASTER'" 
                            :class="activeNode === 'MASTER' ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest'"
                            class="flex-none px-stack-md py-base rounded-full font-label-caps text-label-caps transition-all">
                        MASTER
                    </button>
                    @foreach($serverStats as $s)
                        <button @click="activeNode = '{{ $s->id }}'" 
                                :class="activeNode === '{{ $s->id }}' ? 'bg-primary text-on-primary' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest'"
                                class="flex-none px-stack-md py-base rounded-full font-label-caps text-label-caps transition-all">
                            NODE {{ $s->id }}
                        </button>
                    @endforeach
                </div>

                <div class="flex justify-between items-end px-base">
                    <h2 class="font-headline-md text-headline-md text-on-surface m-0">Node Status</h2>
                    <span class="text-label-caps font-label-caps text-primary">1 MASTER + {{ count($serverStats) }} NODES</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-stack-sm">
                    <!-- Master Server Detail -->
                    <div x-show="activeNode === 'MASTER'" class="bg-surface-container-lowest border border-primary rounded-xl shadow-sm p-stack-sm">
                        <div class="flex justify-between items-start mb-base">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-base">
                                    <span class="material-symbols-outlined text-primary text-[16px]" data-icon="star">star</span>
                                    <h3 class="text-body-md font-bold text-on-surface m-0">Master Server (Primary)</h3>
                                </div>
                                <code class="font-data-mono text-[11px] text-on-surface-variant">{{ request()->getHost() }}</code>
                            </div>
                            <span class="px-base py-[2px] bg-primary/10 text-primary text-[10px] font-bold rounded uppercase">Online</span>
                        </div>
                        <div class="grid grid-cols-2 gap-stack-sm mt-stack-sm">
                            <div class="p-base bg-surface-container-low rounded-lg">
                                <p class="text-[10px] text-on-surface-variant font-label-caps m-0">CPU LOAD</p>
                                <p class="text-body-md font-bold text-primary m-0">Normal</p>
                            </div>
                            <div class="p-base bg-surface-container-low rounded-lg">
                                <p class="text-[10px] text-on-surface-variant font-label-caps m-0">SYSTEM</p>
                                <p class="text-body-md font-bold text-primary m-0">Active</p>
                            </div>
                        </div>
                    </div>

                    <!-- Nodes Detail -->
                    @foreach($serverStats as $stat)
                        <div x-show="activeNode === '{{ $stat->id }}'" class="bg-surface-container-lowest border border-outline-variant rounded-xl shadow-sm p-stack-sm" style="display: none;">
                            <div class="flex justify-between items-start mb-base">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-base">
                                        <span class="material-symbols-outlined text-on-surface-variant text-[16px]" data-icon="dns">dns</span>
                                        <h3 class="text-body-md font-bold text-on-surface m-0">{{ $stat->name }}</h3>
                                    </div>
                                    <code class="font-data-mono text-[11px] text-on-surface-variant">{{ $stat->ip }}</code>
                                </div>
                                <span class="px-base py-[2px] bg-primary/10 text-primary text-[10px] font-bold rounded uppercase">Online</span>
                            </div>
                            <div class="grid grid-cols-2 gap-stack-sm mt-stack-sm">
                                <div class="p-base bg-surface-container-low rounded-lg">
                                    <p class="text-[10px] text-on-surface-variant font-label-caps m-0">CAMERAS</p>
                                    <p class="text-body-md font-bold text-primary m-0">{{ $stat->total }} Total</p>
                                </div>
                                <div class="p-base bg-surface-container-low rounded-lg">
                                    <p class="text-[10px] text-on-surface-variant font-label-caps m-0">RECORDING</p>
                                    <p class="text-body-md font-bold text-primary m-0">{{ $stat->active }} Active</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-gutter">
                <!-- Database Actions -->
                <section class="space-y-stack-md">
                    <h2 class="font-headline-md text-headline-md text-on-surface px-base m-0">Database</h2>
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
                        <div class="p-card-padding flex flex-col gap-stack-md">
                            <div class="flex items-center gap-stack-md p-stack-sm bg-surface-container-low rounded-lg border border-outline-variant/30">
                                <div class="p-stack-sm bg-secondary-container text-on-secondary-container rounded-lg">
                                    <span class="material-symbols-outlined" data-icon="database">database</span>
                                </div>
                                <div>
                                    <p class="text-body-md font-bold m-0">SQL Cluster 01</p>
                                    <p class="text-label-caps text-on-surface-variant m-0">LAST BACKUP: 2 HOURS AGO</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-stack-sm">
                                <button onclick="alert('Memulai proses backup... \n(Fitur ini akan segera diimplementasikan)')" class="flex flex-col items-center justify-center gap-base p-stack-md bg-primary text-on-primary rounded-lg active:scale-95 transition-all">
                                    <span class="material-symbols-outlined" data-icon="backup">backup</span>
                                    <span class="text-label-caps">Backup Database</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Nginx Configuration -->
                <section class="space-y-stack-md">
                    <h2 class="font-headline-md text-headline-md text-on-surface px-base m-0">Infrastructure</h2>
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-card-padding">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-stack-sm">
                                <div class="w-3 h-3 bg-primary rounded-full animate-pulse shadow-[0_0_8px_rgba(0,104,95,0.6)]"></div>
                                <div>
                                    <p class="text-body-md font-bold m-0">Nginx Configuration</p>
                                    <p class="text-body-md text-primary m-0">Active/Running</p>
                                </div>
                            </div>
                            <button class="p-stack-sm bg-surface-container-high text-on-surface-variant rounded-lg hover:bg-surface-container-highest transition-colors">
                                <span class="material-symbols-outlined" data-icon="settings_ethernet">settings_ethernet</span>
                            </button>
                        </div>
                        <div class="mt-stack-md">
                            <button @click="showNginxModal = true" class="w-full py-stack-sm bg-surface-container-low border border-outline-variant text-on-surface-variant rounded-lg font-label-caps transition-all hover:bg-surface-container-high">
                                VIEW CONFIG FILE
                            </button>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Recording Summary (Cameras) -->
            <section class="space-y-stack-md">
                <h2 class="font-headline-md text-headline-md text-on-surface px-base m-0">Cameras Detail</h2>
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
                    <div class="overflow-x-auto no-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-outline-variant/30">
                                    <th class="p-stack-md text-label-caps font-label-caps text-on-surface-variant uppercase">Camera</th>
                                    <th class="p-stack-md text-label-caps font-label-caps text-on-surface-variant uppercase">Node</th>
                                    <th class="p-stack-md text-label-caps font-label-caps text-on-surface-variant uppercase">Status</th>
                                    <th class="p-stack-md text-label-caps font-label-caps text-on-surface-variant uppercase">Last Record</th>
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
                                    <tr class="hover:bg-surface-container-low transition-colors">
                                        <td class="p-stack-md">
                                            <p class="text-body-md font-bold m-0">{{ $cctv->nama_cctv }}</p>
                                            <p class="text-[10px] font-mono text-on-surface-variant m-0">{{ $cctv->kode_cctv }}</p>
                                        </td>
                                        <td class="p-stack-md text-body-md font-bold text-on-surface-variant">
                                            {{ $cctv->server_id ? 'Node ' . $cctv->server_id : 'MASTER' }}
                                        </td>
                                        <td class="p-stack-md">
                                            @if($isRecording)
                                                <span class="px-base py-[2px] bg-primary/10 text-primary text-[10px] font-bold rounded uppercase">Recording</span>
                                            @else
                                                <span class="px-base py-[2px] bg-secondary-container text-on-secondary-container text-[10px] font-bold rounded uppercase">Idle</span>
                                            @endif
                                        </td>
                                        <td class="p-stack-md text-body-md text-on-surface-variant">{{ $lastUpdateText }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-stack-md text-center text-on-surface-variant text-body-md">Tidak ada data kamera.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($cctvs->hasPages())
                        <div class="p-stack-sm bg-surface-container-low border-t border-outline-variant/30 text-center">
                            {{ $cctvs->links() }}
                        </div>
                    @endif
                </div>
            </section>
        </main>

        <!-- Nginx Config Modal -->
        <div x-show="showNginxModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-inverse-surface/50 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div @click.away="showNginxModal = false" class="bg-surface rounded-2xl w-full max-w-3xl shadow-xl flex flex-col max-h-[90vh]"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="scale-95 opacity-0 translate-y-4"
                 x-transition:enter-end="scale-100 opacity-100 translate-y-0">
                
                <div class="flex justify-between items-center p-stack-md border-b border-outline-variant/30">
                    <div class="flex items-center gap-stack-sm">
                        <span class="material-symbols-outlined text-primary">description</span>
                        <h3 class="font-headline-md m-0">Nginx Configuration</h3>
                    </div>
                    <button @click="showNginxModal = false" class="p-base text-on-surface-variant hover:bg-surface-container-high rounded-full transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <div class="p-stack-md overflow-y-auto font-data-mono text-[12px] bg-[#1e1e1e] text-[#d4d4d4] flex-1">
<pre class="m-0 leading-relaxed">
<span class="text-[#569cd6]">server</span> {
    <span class="text-[#9cdcfe]">listen</span> <span class="text-[#b5cea8]">80</span>;
    <span class="text-[#9cdcfe]">server_name</span> <span class="text-[#ce9178]">cctv.unpad.ac.id</span>;

    <span class="text-[#9cdcfe]">root</span> <span class="text-[#ce9178]">/var/www/cctv-v12/public</span>;
    <span class="text-[#9cdcfe]">index</span> <span class="text-[#ce9178]">index.php index.html</span>;

    <span class="text-[#569cd6]">location</span> <span class="text-[#ce9178]">/</span> {
        <span class="text-[#9cdcfe]">try_files</span> <span class="text-[#ce9178]">$uri $uri/ /index.php?$query_string</span>;
    }

    <span class="text-[#569cd6]">location</span> ~ \.php$ {
        <span class="text-[#9cdcfe]">include</span> <span class="text-[#ce9178]">snippets/fastcgi-php.conf</span>;
        <span class="text-[#9cdcfe]">fastcgi_pass</span> <span class="text-[#ce9178]">unix:/var/run/php/php8.2-fpm.sock</span>;
        <span class="text-[#9cdcfe]">fastcgi_param</span> <span class="text-[#ce9178]">SCRIPT_FILENAME $realpath_root$fastcgi_script_name</span>;
    }

    <span class="text-[#569cd6]">location</span> <span class="text-[#ce9178]">/api/stream</span> {
        <span class="text-[#9cdcfe]">proxy_pass</span> <span class="text-[#ce9178]">http://127.0.0.1:1984</span>;
        <span class="text-[#9cdcfe]">proxy_set_header</span> <span class="text-[#ce9178]">Host $host</span>;
        <span class="text-[#9cdcfe]">proxy_set_header</span> <span class="text-[#ce9178]">X-Real-IP $remote_addr</span>;
    }
}
</pre>
                </div>
                
                <div class="p-stack-sm bg-surface-container-low border-t border-outline-variant/30 flex justify-end gap-stack-sm rounded-b-2xl">
                    <button class="px-stack-md py-stack-sm bg-surface-container-high text-on-surface-variant font-label-caps rounded-lg hover:bg-surface-container-highest transition-colors">
                        COPY TO CLIPBOARD
                    </button>
                    <button @click="showNginxModal = false" class="px-stack-md py-stack-sm bg-primary text-on-primary font-label-caps rounded-lg hover:brightness-110 transition-all">
                        DONE
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>