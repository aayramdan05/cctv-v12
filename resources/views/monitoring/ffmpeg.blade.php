<x-app-layout>
    <x-slot name="head">
        <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
        <script>
            tailwind.config = {
              theme: {
                extend: {
                  colors: {
                    tealprimary: {
                      DEFAULT: '#00838f',
                      dark: '#006064',
                      light: '#e0f7fa',
                      hover: '#0097a7',
                    }
                  }
                }
              }
            }
        </script>
        <style>
            @keyframes pulse-subtle {
              0%, 100% { opacity: 1; transform: scale(1); }
              50% { opacity: 0.6; transform: scale(1.1); }
            }
            .status-pulse {
              animation: pulse-subtle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }
            .custom-scrollbar::-webkit-scrollbar {
              height: 6px;
              width: 6px;
            }
            .custom-scrollbar::-webkit-scrollbar-track {
              background: #f1f5f9;
              border-radius: 9999px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb {
              background: #cbd5e1;
              border-radius: 9999px;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
              background: #94a3b8;
            }
            [x-cloak] { display: none !important; }
        </style>
    </x-slot>

    <div class="bg-slate-50 text-slate-800 antialiased min-h-screen font-sans" x-data="{ 
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
        <div class="max-w-[1580px] mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            
            <header class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 pb-4">
                    <nav aria-label="Breadcrumb" class="flex items-center space-x-2 text-xs md:text-sm font-medium text-slate-500">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center hover:text-tealprimary transition-colors">
                            <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                            </svg>
                            Monitoring
                        </a>
                        <span class="text-slate-300">/</span>
                        <span class="text-slate-800 font-semibold flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-teal-500 inline-block"></span>
                            System Health
                        </span>
                    </nav>
                    <div class="flex items-center gap-3">
                        <div class="hidden sm:flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200/80 px-3 py-1.5 rounded-full text-xs font-semibold shadow-xs">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            Kesehatan Klaster: Normal
                        </div>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 flex items-center gap-3">
                            System Health
                            <span class="text-xs tracking-normal font-medium bg-teal-100 text-teal-800 px-2.5 py-0.5 rounded-md border border-teal-200 hidden sm:inline-block">
                                Klaster Terdistribusi
                            </span>
                        </h1>
                        <p class="text-sm text-slate-500 mt-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                            Status perekaman kamera real-time di setiap node server CCTV.
                        </p>
                    </div>
                    <form id="filter-form" action="{{ route('ffmpeg.monitor') }}" method="GET" class="relative w-full lg:w-96">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                        </div>
                        <input name="search" value="{{ request('search') }}" class="w-full pl-10 pr-16 py-2.5 bg-white border border-slate-200 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 shadow-xs transition-all" placeholder="Cari nama kamera, kode, atau IP..." type="text">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <kbd class="px-2 py-0.5 text-[11px] font-semibold text-slate-400 bg-slate-100 border border-slate-200 rounded-md">Enter</kbd>
                        </div>
                    </form>
                </div>

                <div class="flex items-center justify-between flex-wrap gap-3 pt-2">
                    <div class="flex items-center gap-1.5 overflow-x-auto custom-scrollbar pb-1 max-w-full">
                        <button @click="activeNode = 'MASTER'" :class="activeNode === 'MASTER' ? 'bg-tealprimary text-white shadow-sm ring-2 ring-tealprimary/20 hover:bg-tealprimary-hover' : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300 hover:bg-slate-100/70'" class="px-4 py-1.5 rounded-full text-xs font-semibold transition-all flex items-center gap-1.5 shrink-0" type="button">
                            <span class="w-1.5 h-1.5 rounded-full" :class="activeNode === 'MASTER' ? 'bg-white' : 'bg-teal-500'"></span>
                            MASTER
                        </button>
                        @foreach($serverStats as $stat)
                        <button @click="activeNode = '{{ $stat->id }}'" :class="activeNode === '{{ $stat->id }}' ? 'bg-tealprimary text-white shadow-sm ring-2 ring-tealprimary/20 hover:bg-tealprimary-hover' : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300 hover:bg-slate-100/70'" class="px-3.5 py-1.5 rounded-full text-xs font-medium transition-all shrink-0 flex items-center gap-1.5" type="button">
                            <span class="w-1.5 h-1.5 rounded-full" :class="activeNode === '{{ $stat->id }}' ? 'bg-white' : 'bg-teal-500'"></span>
                            NODE {{ $stat->id }}
                        </button>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-bold text-teal-800 bg-teal-50/80 px-3 py-1 rounded-lg border border-teal-200 shrink-0">
                        <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                        1 MASTER + {{ count($serverStats) }} NODES ONLINE
                    </div>
                </div>
            </header>

            <section class="grid grid-cols-1 md:grid-cols-12 gap-5">
                
                <!-- Master Node Hero Card -->
                <article x-show="activeNode === 'MASTER'" class="md:col-span-12 lg:col-span-7 bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
                    <div class="absolute -right-16 -top-16 w-60 h-60 bg-teal-50 rounded-full blur-3xl pointer-events-none -z-0"></div>
                    <div class="relative z-10">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl bg-teal-50 border border-teal-200/80 flex items-center justify-center text-teal-700 shadow-inner">
                                    <svg class="w-6 h-6 fill-teal-600 text-teal-600" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"></path></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-base font-bold text-slate-900">Master Server (Primary)</h2>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100/90 text-emerald-800 border border-emerald-300/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 status-pulse"></span>ONLINE
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 font-mono mt-0.5 flex items-center gap-1.5">
                                        <span>{{ request()->getHost() }}</span>
                                        <span class="text-slate-300">•</span>
                                        <span class="text-teal-600 font-medium">Uptime: Optimal</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6">
                            <div class="bg-slate-50/90 border border-slate-200/80 rounded-xl p-3">
                                <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                                    <span class="flex items-center gap-1"><i class="fas fa-microchip text-teal-600"></i> CPU LOAD</span>
                                </div>
                                <div class="text-sm font-bold text-slate-800">Normal</div>
                                <div class="w-full bg-slate-200 rounded-full h-1.5 mt-2"><div class="bg-teal-500 h-1.5 rounded-full w-[24%]"></div></div>
                            </div>
                            <div class="bg-slate-50/90 border border-slate-200/80 rounded-xl p-3">
                                <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                                    <span class="flex items-center gap-1"><i class="fas fa-server text-teal-600"></i> SYSTEM</span>
                                </div>
                                <div class="text-sm font-bold text-teal-700">Active</div>
                                <div class="w-full bg-slate-200 rounded-full h-1.5 mt-2"><div class="bg-teal-500 h-1.5 rounded-full w-[100%]"></div></div>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Dynamic Node Hero Cards -->
                @foreach($serverStats as $stat)
                <article x-show="activeNode === '{{ $stat->id }}'" style="display: none;" class="md:col-span-12 lg:col-span-7 bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between">
                    <div class="absolute -right-16 -top-16 w-60 h-60 bg-teal-50 rounded-full blur-3xl pointer-events-none -z-0"></div>
                    <div class="relative z-10">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-500 shadow-inner">
                                    <i class="fas fa-server text-lg"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h2 class="text-base font-bold text-slate-900">{{ $stat->name }}</h2>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100/90 text-emerald-800 border border-emerald-300/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 status-pulse"></span>ONLINE
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 font-mono mt-0.5 flex items-center gap-1.5">
                                        <span>{{ $stat->ip }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mt-6">
                            <div class="bg-slate-50/90 border border-slate-200/80 rounded-xl p-3">
                                <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                                    <span class="flex items-center gap-1"><i class="fas fa-video text-teal-600"></i> CAMERAS</span>
                                </div>
                                <div class="text-sm font-bold text-slate-800">{{ $stat->total }} Total</div>
                            </div>
                            <div class="bg-slate-50/90 border border-slate-200/80 rounded-xl p-3">
                                <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                                    <span class="flex items-center gap-1"><i class="fas fa-record-vinyl text-teal-600"></i> RECORDING</span>
                                </div>
                                <div class="text-sm font-bold text-teal-700">{{ $stat->active }} Active</div>
                            </div>
                        </div>
                    </div>
                </article>
                @endforeach

                <!-- Infrastructure & Web Server -->
                <article class="md:col-span-12 lg:col-span-5 bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="p-2 rounded-lg bg-teal-50 text-teal-700 border border-teal-200"><i class="fas fa-sitemap"></i></div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-900">Infrastruktur Server</h2>
                                    <p class="text-xs text-slate-400">Reverse proxy & gateway</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/70 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500 status-pulse"></span>
                                    <span class="text-xs font-bold text-slate-800">Nginx Configuration</span>
                                </div>
                                <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">Active/Running</span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-2 mt-4">
                        <button @click="showNginxModal = true; loadNginxConfig()" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-200/90 rounded-lg hover:bg-teal-100 transition-colors" type="button">
                            <i class="fas fa-file-code"></i> VIEW CONFIG FILE
                        </button>
                    </div>
                </article>

                <!-- Database Backup Cluster -->
                <article class="md:col-span-12 lg:col-span-4 bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="p-2 rounded-lg bg-teal-50 text-teal-700 border border-teal-200"><i class="fas fa-database"></i></div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-900">Database</h2>
                                    <p class="text-xs text-slate-400">PostgreSQL Metadata</p>
                                </div>
                            </div>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        </div>
                        <div class="bg-slate-50/90 rounded-xl p-3.5 border border-slate-200/70 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-900">SQL Cluster 01</span>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-md">Healthy</span>
                            </div>
                        </div>
                    </div>
                    <button onclick="window.location.href='{{ route('ffmpeg.backup') }}'" class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-teal-800 bg-teal-50 border border-teal-300 rounded-xl hover:bg-teal-100 transition-colors shadow-xs" type="button">
                        <i class="fas fa-download text-teal-700"></i> BACKUP DATABASE SEKARANG
                    </button>
                </article>

                <!-- Global Summary -->
                <article class="md:col-span-12 lg:col-span-8 bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs hover:shadow-md transition-shadow">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3 mb-4">
                        <div>
                            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <i class="fas fa-chart-pie text-tealprimary"></i> Ringkasan Node & Storage
                            </h2>
                            <p class="text-xs text-slate-400">Kapasitas dan Perekaman Kamera</p>
                        </div>
                        <span class="inline-flex items-center text-xs font-semibold text-slate-700 bg-slate-100 px-3 py-1 rounded-full self-start">Retensi: Dinamis</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-2 gap-3 mb-4">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/70">
                            <span class="text-xs text-slate-500 font-medium">Total Kamera Sistem</span>
                            <div class="text-xl font-extrabold text-slate-900 mt-1">{{ $cctvs->total() }} Kamera</div>
                        </div>
                        <div class="p-3 bg-emerald-50/70 rounded-xl border border-emerald-200/70">
                            <span class="text-xs text-emerald-700 font-medium">Total Online Server</span>
                            <div class="text-xl font-extrabold text-emerald-800 mt-1">{{ count($serverStats) + 1 }} Server</div>
                        </div>
                    </div>
                </article>
            </section>

            <!-- Cameras Detail Table -->
            <section class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-200/80 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-slate-900">Detail Kamera (Cameras Detail)</h2>
                            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-teal-100 text-teal-800">
                                {{ $cctvs->count() }} ditampilkan
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Pemantauan umpan video dan sinkronisasi</p>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3.5 pl-6 pr-4">KAMERA</th>
                                <th class="py-3.5 px-4">NODE SERVER</th>
                                <th class="py-3.5 px-4">STATUS</th>
                                <th class="py-3.5 px-4">REKAMAN TERAKHIR</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
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
                                <tr class="hover:bg-slate-50/70 transition-colors group">
                                    <td class="py-4 pl-6 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg bg-teal-50 border border-teal-200/80 flex items-center justify-center text-tealprimary shrink-0 group-hover:bg-tealprimary group-hover:text-white transition-colors">
                                                <i class="fas fa-video"></i>
                                            </div>
                                            <div>
                                                <span class="font-bold text-slate-900 block leading-tight">{{ $cctv->nama_cctv }}</span>
                                                <span class="text-xs font-mono text-slate-400 mt-0.5 inline-block">{{ $cctv->kode_cctv }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $cctv->server_id ? 'Node ' . $cctv->server_id : 'Master' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        @if($isRecording)
                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-700 bg-teal-50 px-2.5 py-1 rounded-full border border-teal-200">
                                                <span class="w-2 h-2 rounded-full bg-teal-500 status-pulse"></span>
                                                RECORDING
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full border border-slate-200">
                                                <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                                                IDLE
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap text-xs text-slate-500">
                                        <span class="flex items-center gap-1.5 font-medium {{ $isRecording ? 'text-emerald-600' : '' }}">
                                            <i class="fas fa-clock text-slate-400"></i>
                                            {{ $lastUpdateText }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-500">Tidak ada data kamera.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($cctvs->hasPages())
                    <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-200/80">
                        {{ $cctvs->links() }}
                    </div>
                @endif
            </section>

        </div>

        <!-- Nginx Config Modal -->
        <div x-show="showNginxModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm"
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
                <div class="flex justify-between items-center p-6 border-b border-teal-100/50 bg-slate-50 rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-file-alt text-teal-600 text-xl"></i>
                        <h3 class="text-lg font-bold text-slate-800 m-0">Nginx Configuration</h3>
                    </div>
                    <button @click="showNginxModal = false" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto font-mono text-sm bg-slate-900 text-slate-300 flex-1">
                    <pre class="m-0 leading-relaxed" x-text="nginxContent"></pre>
                </div>
                <div class="p-4 bg-slate-50 border-t border-teal-100/50 flex justify-end gap-3 rounded-b-2xl">
                    <button @click="navigator.clipboard.writeText(nginxContent); alert('Copied to clipboard!')" class="px-4 py-2 bg-white border border-teal-200 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-50 shadow-sm transition-colors">
                        COPY TO CLIPBOARD
                    </button>
                    <button @click="showNginxModal = false" class="px-4 py-2 bg-tealprimary text-white text-xs font-bold rounded-lg hover:bg-tealprimary-hover shadow-sm transition-all">
                        DONE
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>