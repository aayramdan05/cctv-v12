<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Dashboard</span>
            </div>
        </div>
        
        <div id="page-header" class="mb-8">
            <h2 class="text-3xl font-bold text-slate-800 mb-2">System Overview</h2>
            <p class="text-slate-500">Real-time monitoring and analytics dashboard</p>
        </div>
        
        <div id="stats-cards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-6 hover:shadow-md transition-all hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-400 to-cyan-500 flex items-center justify-center shadow-lg shadow-cyan-500/20">
                        <i class="fas fa-video text-white text-xl"></i>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-600 text-xs font-bold uppercase tracking-wide">Active</span>
                </div>
                <h3 class="text-3xl font-bold text-slate-800 mb-1">{{ $totalCctv }}</h3>
                <p class="text-slate-500 text-sm font-medium">Total Cameras</p>
            </div>
            
            <div class="bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-6 hover:shadow-md transition-all hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-400 to-red-500 flex items-center justify-center shadow-lg shadow-red-500/20">
                        <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-xs font-bold uppercase tracking-wide">Alert</span>
                </div>
                <h3 class="text-3xl font-bold text-slate-800 mb-1">{{ $offlineCctv }}</h3>
                <p class="text-slate-500 text-sm font-medium">Cameras Offline</p>
            </div>
            
            <div class="bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-6 hover:shadow-md transition-all hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-400 to-purple-500 flex items-center justify-center shadow-lg shadow-purple-500/20">
                        <i class="fas fa-play-circle text-white text-xl"></i>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-600 text-xs font-bold uppercase tracking-wide">Live</span>
                </div>
                <h3 class="text-3xl font-bold text-slate-800 mb-1">{{ $activeCctv }}</h3>
                <p class="text-slate-500 text-sm font-medium">Active Streams</p>
            </div>
            
            <div class="bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-6 hover:shadow-md transition-all hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center shadow-lg shadow-blue-500/20">
                        <i class="fas fa-building text-white text-xl"></i>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-600 text-xs font-bold uppercase tracking-wide">Total</span>
                </div>
                <h3 class="text-3xl font-bold text-slate-800 mb-1">{{ $totalGedung }}</h3>
                <p class="text-slate-500 text-sm font-medium">Buildings Covered</p>
            </div>
        </div>
        
        <div id="main-grid" class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            
            <div id="campus-map" class="lg:col-span-2 bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-cyan-50 rounded-lg text-cyan-600">
                            <i class="fas fa-map-marked-alt text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Campus Overview</h3>
                    </div>
                    <a href="{{ route('building.index') }}" class="text-xs text-cyan-600 font-bold hover:text-cyan-700 hover:underline transition-colors">
                        View All
                    </a>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($buildings as $building)
                    <div class="bg-white border border-slate-100 rounded-xl p-4 hover:border-cyan-300 hover:shadow-md transition-all group cursor-pointer">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400 group-hover:bg-cyan-50 group-hover:text-cyan-600 group-hover:border-cyan-200 transition-colors">
                                <i class="fas fa-building text-lg"></i>
                            </div>
                            <span class="px-2 py-1 rounded-md bg-green-50 text-green-600 text-[10px] font-bold border border-green-100 uppercase tracking-wide">
                                Online
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <h4 class="font-bold text-slate-800 text-sm truncate" title="{{ $building->nama_gedung }}">
                                {{ $building->nama_gedung }}
                            </h4>
                            <div class="flex items-center gap-2 text-xs text-slate-500 mt-1">
                                <i class="fas fa-video text-slate-300"></i>
                                <span>{{ $building->cctvs_count }} Cameras</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-50 flex items-center justify-between text-xs">
                            <span class="text-slate-400 font-medium truncate max-w-[120px]" title="{{ $building->fakultas }}">
                                {{ $building->fakultas }}
                            </span>
                            <i class="fas fa-chevron-right text-slate-300 group-hover:text-cyan-500 transition-colors"></i>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <div id="recent-alerts" class="bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-6 h-fit">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-50 rounded-lg text-red-500">
                            <i class="fas fa-bell text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Alerts</h3>
                    </div>
                    <button class="text-xs text-cyan-600 font-bold hover:underline">View Log</button>
                </div>

                <div class="space-y-3">
                    @forelse($alerts as $alert)
                        @php
                            $colorClass = ''; $iconColor = ''; $bgHover = '';
                            if($alert['type'] == 'new') {
                                $colorClass = '!border-purple-400'; $iconColor = 'text-purple-500'; $bgHover = 'hover:bg-purple-50/50';
                            } elseif($alert['type'] == 'offline') {
                                $colorClass = '!border-red-400'; $iconColor = 'text-red-500'; $bgHover = 'hover:bg-red-50/50';
                            } elseif($alert['type'] == 'online') {
                                $colorClass = '!border-green-400'; $iconColor = 'text-green-500'; $bgHover = 'hover:bg-green-50/50';
                            }
                        @endphp

                        <div class="bg-white/80 rounded-xl p-3 border-l-4 {{ $colorClass }} {{ $bgHover }} transition-colors shadow-sm">
                            <div class="flex items-start justify-between mb-1">
                                <div class="flex items-center space-x-2">
                                    <i class="fas {{ $alert['icon'] }} {{ $iconColor }} text-xs"></i>
                                    <span class="text-xs font-bold text-slate-700">{{ $alert['title'] }}</span>
                                </div>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $alert['time'] }}</span>
                            </div>
                            <p class="text-xs text-slate-500 ml-5 leading-relaxed">{{ $alert['message'] }}</p>
                        </div>
                    @empty
                        <div class="bg-green-50/50 rounded-xl p-6 text-center border border-green-100">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-800">Semua Sistem Normal</p>
                            <p class="text-xs text-slate-500 mt-1">Tidak ada notifikasi baru.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <div id="live-feeds-section" class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                        <i class="fas fa-play-circle text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Live Feed Preview</h3>
                        <p class="text-xs text-slate-500 hidden sm:block">Monitoring realtime dari kamera terbaru</p>
                    </div>
                </div>
                
                <a href="{{ route('monitoring.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:border-cyan-300 hover:text-cyan-600 hover:shadow-sm transition-all flex items-center gap-2">
                    <span>View All Streams</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($latestCctvs as $cctv)
                <div class="bg-white rounded-2xl p-3 shadow-sm border border-slate-200 hover:border-cyan-300 hover:shadow-md transition-all group">
                    
                    <div class="bg-slate-900 rounded-xl aspect-video flex items-center justify-center mb-3 relative overflow-hidden">
                        
                        <iframe 
                            id="preview-{{ $cctv->id }}"
                            class="w-full h-full object-cover border-none pointer-events-none"
                            allowfullscreen
                            scrolling="no"
                            loading="lazy">
                        </iframe>

                        <div class="absolute top-3 left-3 px-2.5 py-1 rounded-lg bg-red-600/90 backdrop-blur-sm flex items-center gap-2 shadow-lg z-10">
                            <span class="relative flex h-2 w-2">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                            </span>
                            <span class="text-white text-[10px] font-bold tracking-wider">LIVE</span>
                        </div>
                        
                        <div class="absolute bottom-3 left-3 px-2 py-1 rounded-md bg-black/60 backdrop-blur text-white/90 text-[10px] font-mono border border-white/10 z-10">
                            {{ $cctv->kode_cctv }}
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between px-1">
                        <div class="flex flex-col min-w-0 pr-2">
                            <span class="text-sm font-bold text-slate-800 truncate block" title="{{ $cctv->nama_cctv }}">
                                {{ $cctv->nama_cctv }}
                            </span>
                            <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-0.5">
                                <i class="fas fa-map-marker-alt text-slate-300"></i>
                                <span class="truncate block">{{ $cctv->building->nama_gedung ?? 'Unknown' }}</span>
                            </div>
                        </div>
                        
                        @if(auth()->user()->role !== 'user')
                            <a href="{{ route('cctv.edit', $cctv->id) }}" class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-cyan-50 hover:text-cyan-600 transition-colors border border-transparent hover:border-cyan-100 shrink-0">
                                <i class="fas fa-cog text-sm"></i>
                            </a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="col-span-full flex flex-col items-center justify-center py-16 bg-white/50 rounded-2xl border-2 border-dashed border-slate-200">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-400">
                        <i class="fas fa-video-slash text-2xl"></i>
                    </div>
                    <p class="text-slate-500 font-medium">Belum ada data kamera.</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <div id="analytics-chart" class="bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                    <i class="fas fa-chart-bar text-lg"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800">Daily Activity</h3>
            </div>
            <div id="activity-chart" style="height: 300px"></div>
        </div>
    </main>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // ---------------------------------------------------------
            // 1. VIDEO PLAYER LOOP (Javascript Auto-Detect Version)
            // ---------------------------------------------------------
            // Kita gunakan JS untuk mendeteksi prefix URL (/node1/, /node2/)
            // Cara ini menghindari Error 500 jika Model PHP belum dikonfigurasi sempurna.

            // Deteksi path saat ini: misal "/node1/dashboard" -> prefix "/node1"
            const pathSegments = window.location.pathname.split('/');
            let urlPrefix = "";
            
            // Cek apakah segmen URL mengandung 'node' (support reverse proxy)
            if (pathSegments.length > 1 && pathSegments[1].toLowerCase().startsWith('node')) {
                urlPrefix = "/" + pathSegments[1];
            }

            @foreach($latestCctvs as $cctv)
            {
                let iframe = document.getElementById('preview-{{ $cctv->id }}');
                
                if(iframe) {
                    // Kita bangun URL secara manual di JS agar tidak error di PHP
                    let alias = "camera_{{ $cctv->id }}";
                    
                    // Format: /node1/stream.html?src=camera_1&mode=...
                    let finalUrl = urlPrefix + "/stream.html?src=" + alias + "&mode=webrtc,mse,hls,mjpeg";
                    
                    // Set src iframe
                    iframe.src = finalUrl;
                }
            }
            @endforeach

            // ---------------------------------------------------------
            // 2. CHART LOGIC (Sama seperti sebelumnya)
            // ---------------------------------------------------------
            try {
                var trace1 = {
                    x: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    y: [245, 248, 242, 250, 248, 246, 248], 
                    name: 'Cameras Online',
                    type: 'scatter',
                    mode: 'lines',
                    line: { color: '#06b6d4', width: 3 },
                    fill: 'tozeroy',
                    fillcolor: 'rgba(6, 182, 212, 0.1)'
                };
                var layout = {
                    title: { text: '', font: { size: 16 } },
                    xaxis: { title: '' },
                    yaxis: { title: 'Count' },
                    margin: { t: 20, r: 20, b: 40, l: 50 },
                    plot_bgcolor: 'rgba(255, 255, 255, 0.5)',
                    paper_bgcolor: 'rgba(255, 255, 255, 0)',
                    showlegend: true,
                    legend: { x: 0, y: 1.1, orientation: 'h' }
                };
                var config = { responsive: true, displayModeBar: false, displaylogo: false };
                Plotly.newPlot('activity-chart', [trace1], layout, config);
            } catch(e) {
                console.error("Chart Error:", e);
            }
        });
    </script>
@endpush
</x-app-layout>