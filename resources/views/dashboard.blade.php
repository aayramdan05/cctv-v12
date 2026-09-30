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
        
        <div id="stats-cards" class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
            <!-- 1. Total Cameras -->
            <div onclick="location.href='{{ route('cctv.index') }}'" 
                 class="bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-4 hover:shadow-md transition-all hover:-translate-y-1 cursor-pointer group">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-cyan-500 flex items-center justify-center shadow-lg shadow-cyan-500/20 group-hover:scale-110 transition-transform">
                        <i class="fas fa-video text-white text-base"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-0.5">{{ $totalCctv }}</h3>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-wider">Total Cameras</p>
            </div>

            <!-- 2. Active Streams -->
            <div class="bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-4 hover:shadow-md transition-all hover:-translate-y-1">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-400 to-purple-500 flex items-center justify-center shadow-lg shadow-purple-500/20">
                        <i class="fas fa-play-circle text-white text-base"></i>
                    </div>
                    <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-600 text-[9px] font-bold uppercase">Live</span>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-0.5">{{ $activeCctv }}</h3>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-wider">Active Streams</p>
            </div>

            <!-- 3. Total Indoor -->
            <div onclick="location.href='{{ route('cctv.index', ['penempatan' => 'Indoor']) }}'"
                 class="bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-4 hover:shadow-md transition-all hover:-translate-y-1 cursor-pointer group">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-500 flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform">
                        <i class="fas fa-door-open text-white text-base"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-0.5">{{ $indoorCount }}</h3>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-wider">Indoor Units</p>
            </div>

            <!-- 4. Total Outdoor -->
            <div onclick="location.href='{{ route('cctv.index', ['penempatan' => 'Outdoor']) }}'"
                 class="bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-4 hover:shadow-md transition-all hover:-translate-y-1 cursor-pointer group">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-400 to-orange-500 flex items-center justify-center shadow-lg shadow-orange-500/20 group-hover:scale-110 transition-transform">
                        <i class="fas fa-cloud-sun text-white text-base"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-0.5">{{ $outdoorCount }}</h3>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-wider">Outdoor Units</p>
            </div>
            
            <!-- 5. Offline Alert -->
            <div onclick="document.getElementById('offline-modal').classList.remove('hidden')"
                 class="bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-4 hover:shadow-md transition-all hover:-translate-y-1 cursor-pointer group">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-400 to-red-500 flex items-center justify-center shadow-lg shadow-red-500/20">
                        <i class="fas fa-exclamation-triangle text-white text-base"></i>
                    </div>
                    <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-600 text-[9px] font-bold uppercase">Down</span>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-0.5">{{ $offlineCctv }}</h3>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-wider">Cameras Offline</p>
            </div>
            
            <!-- 6. Buildings -->
            <div onclick="location.href='{{ route('building.index') }}'"
                 class="bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-4 hover:shadow-md transition-all hover:-translate-y-1 cursor-pointer group">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center shadow-lg shadow-blue-500/20 group-hover:scale-110 transition-transform">
                        <i class="fas fa-building text-white text-base"></i>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-0.5">{{ $totalGedung }}</h3>
                <p class="text-slate-500 text-[10px] font-bold uppercase tracking-wider">Gedung / Lokasi</p>
            </div>
        </div>
        
        <div id="main-grid" class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            
            <div class="lg:col-span-2 flex flex-col gap-6">
                <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
                    
                    <!-- Left Card: Uptime & Status -->
                    <div class="bg-white/90 backdrop-blur-md border border-slate-100 shadow-sm rounded-2xl p-6 xl:col-span-2 flex flex-col">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-[13px] font-extrabold text-slate-800 uppercase tracking-wide">Ringkasan Uptime & Status</h3>
                                <p class="text-[11px] text-slate-400 mt-0.5">Total kesiapan sistem surveilans</p>
                            </div>
                            <span class="px-2 py-1 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-bold border border-emerald-100">97% Target SLA</span>
                        </div>
                        
                        @php
                            $total = $totalCctv;
                            $offline = $offlineCctv;
                            $online = $total - $offline;
                            $uptimePercent = $total > 0 ? round(($online / $total) * 100) : 0;
                            
                            // Mock causes of offline based on requested UI
                            $cause1 = round($offline * 0.47);
                            $cause2 = round($offline * 0.35);
                            $cause3 = $offline - $cause1 - $cause2;
                        @endphp

                        <div class="relative w-40 h-40 mx-auto mb-6">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-emerald-500" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="transparent" stroke="currentColor" stroke-dasharray="100, 100" stroke-width="5"></path>
                                <path class="text-red-500" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="transparent" stroke="currentColor" stroke-dasharray="{{ 100 - $uptimePercent }}, 100" stroke-dashoffset="-{{ $uptimePercent }}" stroke-width="5"></path>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-3xl font-black text-slate-800">{{ $uptimePercent }}%</span>
                                <span class="text-[9px] text-slate-500 font-bold uppercase tracking-wider mt-1">Uptime</span>
                                <span class="text-[9px] text-emerald-500 font-bold mt-0.5">Normal</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 mb-6">
                            <div class="flex-1 bg-slate-50 border border-slate-100 rounded-lg p-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                    <span class="text-[11px] font-medium text-slate-600">Online</span>
                                </div>
                                <span class="text-xs font-bold text-slate-800">{{ $online }}</span>
                            </div>
                            <div class="flex-1 bg-red-50/30 border border-red-50 rounded-lg p-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                                    <span class="text-[11px] font-medium text-slate-600">Offline</span>
                                </div>
                                <span class="text-xs font-bold text-red-600">{{ $offline }}</span>
                            </div>
                        </div>

                        <div class="mt-auto">
                            <p class="text-[11px] font-medium text-slate-700 mb-3">Penyebab {{ $offline }} Kamera Offline:</p>
                            
                            <div class="space-y-3">
                                <div>
                                    <div class="flex justify-between text-[10px] mb-1">
                                        <span class="text-slate-500">Gangguan Switch PoE / Listrik</span>
                                        <span class="font-bold text-slate-700">{{ $cause1 }} Unit (47%)</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                                        <div class="bg-red-500 h-1.5 rounded-full" style="width: 47%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[10px] mb-1">
                                        <span class="text-slate-500">Kabel FO / Network Disconnect</span>
                                        <span class="font-bold text-slate-700">{{ $cause2 }} Unit (35%)</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                                        <div class="bg-amber-500 h-1.5 rounded-full" style="width: 35%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[10px] mb-1">
                                        <span class="text-slate-500">Hardware / Kamera Rusak</span>
                                        <span class="font-bold text-slate-700">{{ $cause3 }} Unit (18%)</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                                        <div class="bg-slate-400 h-1.5 rounded-full" style="width: 18%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Card: Gedung & Zona -->
                    <div class="bg-white/90 backdrop-blur-md border border-slate-100 shadow-sm rounded-2xl p-6 xl:col-span-3 flex flex-col">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <h3 class="text-[13px] font-extrabold text-slate-800 uppercase tracking-wide">Kesehatan Per Gedung & Zona</h3>
                                    <span class="px-2 py-0.5 rounded text-[9px] bg-slate-100 text-slate-600 font-semibold border border-slate-200">{{ $totalGedung }} Total Lokasi</span>
                                </div>
                                <p class="text-[11px] text-slate-400">Pemetaan langsung status online unit CCTV dan konsistensi rekaman</p>
                            </div>
                            <a href="{{ route('building.index') }}" class="text-[11px] text-cyan-600 font-bold hover:text-cyan-700 transition-colors">Lihat Seluruh Gedung <i class="fas fa-chevron-right ml-1 text-[9px]"></i></a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($buildings as $building)
                            @php
                                $bTotal = $building->cctvs_count;
                                $bOnline = $building->online_cctvs_count;
                                $bOffline = $bTotal - $bOnline;
                                $bPercent = $bTotal > 0 ? round(($bOnline / $bTotal) * 100, 1) : 0;
                                
                                if($bOffline === 0) {
                                    $bStyle = 'border-emerald-100 shadow-sm hover:border-emerald-300';
                                    $iconBg = 'bg-slate-50 border border-slate-100';
                                    $iconColor = 'text-slate-400';
                                    $badgeStyle = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                                    $badgeText = 'ONLINE';
                                    $barColor = 'bg-emerald-500';
                                    $textColor = 'text-emerald-500';
                                    $titleColor = 'text-slate-800';
                                    $descHtml = '<p class="text-[9px] text-slate-400 mt-0.5">'.$building->fakultas.'</p>';
                                } elseif($bOffline <= 2) {
                                    $bStyle = 'border-amber-200 shadow-sm hover:border-amber-400';
                                    $iconBg = 'bg-amber-50 border border-amber-100';
                                    $iconColor = 'text-amber-500';
                                    $badgeStyle = 'bg-amber-50 text-amber-600 border-amber-200';
                                    $badgeText = $bOffline . ' OFFLINE';
                                    $barColor = 'bg-amber-500';
                                    $textColor = 'text-amber-600';
                                    $titleColor = 'text-slate-800';
                                    $descHtml = '<p class="text-[9px] text-slate-400 mt-0.5">'.$building->fakultas.'</p>';
                                } else {
                                    $bStyle = 'border-red-200 shadow-sm bg-red-50/30 hover:border-red-400';
                                    $iconBg = 'bg-red-500';
                                    $iconColor = 'text-white';
                                    $badgeStyle = 'bg-red-500 text-white border-red-500 shadow-sm';
                                    $badgeText = $bOffline . ' OFFLINE';
                                    $barColor = 'bg-red-500';
                                    $textColor = 'text-red-600';
                                    $titleColor = 'text-slate-800 font-bold';
                                    $descHtml = '<p class="text-[9px] text-red-600 font-medium mt-0.5">Insiden Kritis Terkonsentrasi</p>';
                                }
                            @endphp
                            <div onclick="location.href='{{ route('monitoring.index', ['building_id' => $building->id]) }}'"
                                 class="border {{ $bStyle }} bg-white rounded-xl p-4 cursor-pointer hover:shadow-md transition-all relative overflow-hidden group">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex gap-3">
                                        <div class="w-8 h-8 rounded-lg {{ $iconBg }} {{ $iconColor }} flex items-center justify-center shrink-0">
                                            <i class="fas {{ $bOffline > 2 ? 'fa-exclamation-triangle' : 'fa-building' }} text-sm"></i>
                                        </div>
                                        <div>
                                            <h4 class="{{ $titleColor }} text-[11px] font-bold truncate max-w-[120px]" title="{{ $building->nama_gedung }}">{{ $building->nama_gedung }}</h4>
                                            {!! $descHtml !!}
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold border {{ $badgeStyle }} tracking-wider">{{ $badgeText }}</span>
                                </div>
                                
                                @if($bOffline > 2)
                                <p class="text-[9px] text-red-500 mb-2 leading-relaxed">Sebagian besar kamera terputus sejak beberapa saat lalu. Perlu pengecekan segera.</p>
                                @endif

                                <div class="mt-auto">
                                    <div class="flex justify-between items-end mb-1.5">
                                        <span class="text-[10px] text-slate-500 font-medium">{{ $bOnline }}/{{ $bTotal }} Kamera Aktif</span>
                                        <span class="text-[10px] font-bold {{ $textColor }}">{{ $bPercent }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1">
                                        <div class="{{ $barColor }} h-1 rounded-full" style="width: {{ $bPercent }}%"></div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="recent-alerts" class="bg-white/70 backdrop-blur-md border border-white/30 shadow-sm rounded-2xl p-6 h-full flex flex-col">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-50 rounded-lg text-red-500">
                            <i class="fas fa-bell text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800">Alerts</h3>
                    </div>
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
                        <h3 class="text-xl font-bold text-slate-800">Fixed Outdoor Previews</h3>
                        <p class="text-xs text-slate-500 hidden sm:block">Live monitoring dari 3 kamera outdoor utama</p>
                    </div>
                </div>
                
                <a href="{{ route('monitoring.index') }}" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:border-cyan-300 hover:text-cyan-600 hover:shadow-sm transition-all flex items-center gap-2">
                    <span>View All Streams</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($previewCctvs as $cctv)
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
                        
                        @can('cctv_edit')
                            <a href="{{ route('cctv.edit', $cctv->id) }}" class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-cyan-50 hover:text-cyan-600 transition-colors border border-transparent hover:border-cyan-100 shrink-0">
                                <i class="fas fa-cog text-sm"></i>
                            </a>
                        @endcan
                    </div>
                </div>
                @empty
                <div class="col-span-full flex flex-col items-center justify-center py-16 bg-white/50 rounded-2xl border-2 border-dashed border-slate-200">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-400">
                        <i class="fas fa-video-slash text-2xl"></i>
                    </div>
                    <p class="text-slate-500 font-medium">Belum ada data kamera outdoor.</p>
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

        <!-- Smart Diagnostic Modal -->
        <div id="offline-modal" class="fixed inset-0 z-[1000] hidden flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm">
            <div class="bg-white rounded-2xl border border-red-100 shadow-2xl max-w-4xl w-full max-h-[85vh] flex flex-col overflow-hidden relative">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-red-500/5 to-rose-500/5 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center text-red-600">
                            <i class="fas fa-exclamation-triangle text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-base uppercase tracking-wider">Diagnostic: Offline Cameras</h3>
                            <p class="text-xs text-slate-500 font-medium">Daftar {{ $offlineCctv }} kamera yang saat ini tidak dapat dijangkau.</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('offline-modal').classList.add('hidden')" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto custom-scrollbar flex-1 space-y-4">
                    @forelse($offlineCameraDetails ?? [] as $cam)
                        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:border-red-300 transition-colors flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-bold text-slate-800 text-sm truncate">{{ $cam->nama }}</h4>
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[10px] font-mono border border-slate-200">{{ $cam->kode }}</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mt-1">
                                    <div class="flex items-center gap-1.5"><i class="fas fa-building text-slate-400"></i> {{ $cam->gedung }}</div>
                                    <div class="flex items-center gap-1.5"><i class="fas fa-clock text-slate-400"></i> Terakhir aktif: {{ $cam->last_seen }}</div>
                                </div>
                            </div>

                            <div class="w-full sm:w-1/2 shrink-0 bg-red-50 rounded-lg p-3 border border-red-100 flex flex-col justify-center">
                                <div class="flex items-center gap-1.5 mb-1 text-red-600 font-bold text-[11px] uppercase tracking-wider">
                                    <i class="fas fa-search"></i> Diagnosa: {{ $cam->cause_type }}
                                </div>
                                <p class="text-xs text-red-500/80 leading-relaxed">{{ $cam->cause }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-500 flex flex-col items-center">
                            <i class="fas fa-check-circle text-4xl text-green-400 mb-3"></i>
                            <p class="font-bold">Semua Kamera Online</p>
                            <p class="text-xs">Tidak ada data kamera offline saat ini.</p>
                        </div>
                    @endforelse
                </div>
                
                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end shrink-0">
                    <button onclick="document.getElementById('offline-modal').classList.add('hidden')" 
                            class="px-5 py-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition shadow-sm">
                        Tutup Panel
                    </button>
                </div>
            </div>
        </div>
    </main>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // ---------------------------------------------------------
            @foreach($previewCctvs as $cctv)
            {
                let iframe = document.getElementById('preview-{{ $cctv->id }}');
                
                if(iframe) {
                    // Gunakan URL langsung dari Model (Sudah mendukung multi-node)
                    iframe.src = "{!! $cctv->live_stream_url !!}";
                }
            }
            @endforeach

            // ---------------------------------------------------------
            // 2. CHART LOGIC (Sama seperti sebelumnya)
            // ---------------------------------------------------------
            try {
                var trace1 = {
                    x: {!! json_encode($chartDates ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) !!},
                    y: {!! json_encode($chartData ?? [0, 0, 0, 0, 0, 0, 0]) !!}, 
                    name: 'Motion Events',
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