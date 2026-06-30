<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CCTV UNPAD Mobile</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/csp@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #09090b; /* zinc-950 */
            color: #f4f4f5; /* zinc-100 */
            -webkit-tap-highlight-color: transparent;
        }
        ::-webkit-scrollbar {
            display: none;
        }
        .custom-scrollbar {
            scrollbar-width: none;
        }
        iframe {
            background-color: #000;
        }
    </style>
</head>
<body class="h-full flex flex-col overflow-hidden" x-data="mobileMonitoring()">

    <!-- App Header -->
    <header class="h-14 bg-zinc-900 border-b border-zinc-800 flex items-center justify-between px-4 sticky top-0 z-50 shrink-0 select-none">
        <div class="flex items-center gap-2">
            <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse shadow-[0_0_8px_#10b981]"></div>
            <h1 class="text-white font-bold text-sm tracking-tight">CCTV UNPAD</h1>
            <span class="text-[8px] font-mono text-cyan-400 uppercase tracking-widest bg-cyan-950/50 border border-cyan-800 px-1.5 py-0.5 rounded-full font-bold">Mobile</span>
        </div>
        
        <a href="{{ route('dashboard') }}" class="w-8 h-8 rounded-full bg-zinc-800 border border-zinc-700 flex items-center justify-center text-zinc-300 hover:text-white transition active:scale-95">
            <i class="fas fa-home text-xs"></i>
        </a>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 flex flex-col overflow-hidden min-h-0">
        
        <!-- Video Player Grid Panel -->
        <div class="w-full bg-black aspect-video relative flex flex-col justify-center shrink-0 border-b border-zinc-950 shadow-lg">
            <div class="grid h-full w-full gap-0.5 bg-zinc-950"
                 :class="{ 'grid-cols-1 grid-rows-1': gridSize === 1, 'grid-cols-2 grid-rows-2': gridSize === 4 }">
                 
                 <!-- Grid Slots -->
                 <template x-for="i in gridSize" :key="i">
                     <div class="relative border bg-zinc-950 overflow-hidden transition-all duration-200"
                          :class="selectedSlot === i ? 'border-cyan-500 shadow-[inset_0_0_8px_rgba(6,182,212,0.4)]' : 'border-zinc-900'"
                          @click="selectSlot(i)">
                          
                          <!-- Plus icon placeholder if slot is empty -->
                          <div x-show="!activeSlots[i]" class="absolute inset-0 flex flex-col items-center justify-center text-zinc-700 pointer-events-none select-none">
                              <i class="fas fa-plus text-lg mb-1 opacity-20"></i>
                              <span class="text-[9px] font-mono text-zinc-600">Slot <span x-text="i"></span></span>
                          </div>
                          
                          <!-- Active Camera Stream Component -->
                          <template x-if="activeSlots[i]">
                              <div class="w-full h-full relative bg-black flex items-center justify-center">
                                  <!-- Live Stream Iframe -->
                                  <iframe 
                                      :id="'iframe-live-mobile-' + i"
                                      x-show="activeSlots[i].mode === 'live'"
                                      class="absolute w-full h-full object-contain border-none pointer-events-none"
                                      allow="autoplay; encrypted-media; fullscreen"
                                      playsinline>
                                  </iframe>
                                  
                                  <!-- Playback Video Element -->
                                  <video 
                                      :id="'video-playback-mobile-' + i"
                                      x-show="activeSlots[i].mode === 'playback'"
                                      class="absolute w-full h-full object-contain pointer-events-none"
                                      playsinline>
                                  </video>
                                  
                                  <!-- Overlay Badges -->
                                  <div class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-black/75 backdrop-blur flex items-center gap-1 z-10 pointer-events-none shadow-sm">
                                      <div class="w-1.5 h-1.5 rounded-full" :class="activeSlots[i].mode === 'live' ? 'bg-red-500 animate-pulse' : 'bg-green-500'"></div>
                                      <span class="text-[7px] font-mono font-bold text-white uppercase" x-text="activeSlots[i].mode === 'live' ? 'LIVE' : 'REC'"></span>
                                      <span class="text-[8px] font-bold text-zinc-300 truncate max-w-[80px] border-l border-zinc-800 pl-1 ml-0.5" x-text="activeSlots[i].name"></span>
                                  </div>
                                  
                                  <!-- Remove Button -->
                                  <button @click.stop="removeCamera(i)" 
                                          class="absolute top-1.5 right-1.5 w-5 h-5 bg-red-600/80 active:bg-red-500 text-white rounded-full flex items-center justify-center transition z-20">
                                      <i class="fas fa-times text-[9px]"></i>
                                  </button>
                              </div>
                          </template>
                     </div>
                 </template>
            </div>
        </div>

        <!-- Controls Action Strip -->
        <div class="bg-zinc-900 border-b border-zinc-800 px-4 py-2 flex items-center justify-between shrink-0 select-none">
            <!-- Grid selector -->
            <div class="flex bg-zinc-800 p-0.5 rounded-lg border border-zinc-700">
                <button @click="setGrid(1)" :class="{'bg-zinc-950 text-cyan-400 border border-zinc-700': gridSize === 1}" class="w-8 h-8 rounded flex items-center justify-center text-zinc-400 transition">
                    <i class="fas fa-square text-xs pointer-events-none"></i>
                </button>
                <button @click="setGrid(4)" :class="{'bg-zinc-950 text-cyan-400 border border-zinc-700': gridSize === 4}" class="w-8 h-8 rounded flex items-center justify-center text-zinc-400 transition">
                    <i class="fas fa-th-large text-xs pointer-events-none"></i>
                </button>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex items-center gap-2">
                <!-- Go Live Button -->
                <button @click="goLive()" 
                        x-show="activeSlots[selectedSlot] && activeSlots[selectedSlot].mode !== 'live'"
                        class="px-3 py-1.5 rounded-lg bg-red-600 active:bg-red-500 text-white font-bold text-[10px] flex items-center gap-1 shadow transition active:scale-95">
                    <i class="fas fa-broadcast-tower"></i> LIVE
                </button>
                
                <!-- Timeline Toggle -->
                <button @click="showTimeline = !showTimeline" 
                        class="w-8 h-8 rounded-lg border border-zinc-700 flex items-center justify-center text-zinc-400 transition active:scale-95"
                        :class="showTimeline ? 'bg-zinc-950 text-cyan-400 border-cyan-500/30' : 'bg-zinc-800'">
                    <i class="fas fa-history text-xs pointer-events-none"></i>
                </button>
            </div>
        </div>

        <!-- Timeline & Playback Controller Panel -->
        <div x-show="showTimeline && activeSlots[selectedSlot]" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="bg-zinc-950 border-b border-zinc-800 p-3 space-y-3 shrink-0 select-none">
             
             <!-- Header timeline (Date selector & Playhead display) -->
             <div class="flex justify-between items-center text-xs">
                 <div class="flex items-center gap-2 bg-zinc-900 border border-zinc-800 px-2.5 py-1 rounded-lg">
                     <i class="fas fa-calendar-alt text-zinc-500 text-[10px]"></i>
                     <input type="date" x-model="selectedDate" @change="fetchTimelineData()" 
                            class="border-none p-0 text-[10px] font-bold text-zinc-300 focus:ring-0 bg-transparent h-auto cursor-pointer w-24">
                 </div>
                 <span class="text-cyan-400 font-mono font-bold text-[11px] bg-cyan-950/20 px-2.5 py-1 rounded-lg border border-cyan-900/30" x-text="timelineTimeDisplay"></span>
             </div>
             
             <!-- Playback controller (if in playback mode) -->
             <div class="flex items-center justify-center gap-6 py-1" x-show="activeSlots[selectedSlot]?.mode === 'playback'">
                 <button @click="seek(-10)" class="w-9 h-9 rounded-full bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-400 active:bg-zinc-800 active:scale-90 transition">
                     <i class="fas fa-undo text-xs pointer-events-none"></i>
                 </button>
                 <button @click="togglePlayback()" class="w-11 h-11 rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 flex items-center justify-center text-white active:scale-90 transition shadow-lg shadow-cyan-500/10">
                     <i class="fas text-base pointer-events-none" :class="isPlaying ? 'fa-pause' : 'fa-play'"></i>
                 </button>
                 <button @click="seek(10)" class="w-9 h-9 rounded-full bg-zinc-900 border border-zinc-800 flex items-center justify-center text-zinc-400 active:bg-zinc-800 active:scale-90 transition">
                     <i class="fas fa-redo text-xs pointer-events-none"></i>
                 </button>
             </div>
             
             <!-- Horizontal timeline list segments -->
             <div class="space-y-1.5">
                 <span class="text-[9px] font-bold text-zinc-500 uppercase tracking-wider">Rekaman Tersedia</span>
                 <div class="flex gap-2 overflow-x-auto pb-1.5 custom-scrollbar">
                     <template x-for="seg in currentTimelineData" :key="seg.start">
                         <button @click="playRecord(selectedSlot, seg.url, 0, seg.start)"
                                 class="px-3 py-2 rounded-xl border bg-zinc-900 text-left shrink-0 transition-all flex items-center gap-2.5 active:scale-95"
                                 :class="activeSlots[selectedSlot]?.recordStartOffset === seg.start && activeSlots[selectedSlot]?.mode === 'playback' ? 'border-cyan-500 bg-cyan-950/20' : 'border-zinc-800'">
                             <div class="w-2 h-2 rounded-full" :class="seg.has_motion ? 'bg-orange-500 shadow-[0_0_6px_#f97316]' : 'bg-green-500 shadow-[0_0_6px_#22c55e]'"></div>
                             <div>
                                 <p class="text-[10px] font-bold text-zinc-200" x-text="seg.human_start"></p>
                                 <p class="text-[8px] font-medium text-zinc-500" x-text="seg.size_mb + ' MB'"></p>
                             </div>
                         </button>
                     </template>
                     <template x-if="currentTimelineData.length === 0">
                         <p class="text-[10px] text-zinc-500 italic py-1.5 pl-1">Tidak ada file rekaman untuk tanggal ini.</p>
                     </template>
                 </div>
             </div>
        </div>

        <!-- Camera Directory Listing (Bottom Half) -->
        <div class="flex-1 overflow-hidden flex flex-col bg-zinc-900/50">
            <!-- Directory Filter Bar -->
            <div class="p-3.5 border-b border-zinc-800/80 flex flex-col gap-2.5 bg-zinc-950/60 select-none">
                <!-- Search -->
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-zinc-500 text-xs"></i>
                    <input type="text" x-model="search" placeholder="Cari nama kamera..." 
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-zinc-800 bg-zinc-900 focus:bg-zinc-900 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all placeholder-zinc-500 text-zinc-200">
                </div>
                
                <!-- Filter Pills -->
                <div class="grid grid-cols-2 gap-2">
                    <div class="relative">
                        <select x-model="filterFaculty" class="w-full pl-3 pr-8 py-1.5 text-[10px] font-bold text-zinc-400 bg-zinc-900 rounded-lg border border-zinc-800 focus:border-cyan-500 focus:ring-0 appearance-none cursor-pointer truncate">
                            <option value="">Semua Fakultas</option>
                            @foreach($faculties as $fakultas)
                                <option value="{{ $fakultas }}">{{ $fakultas }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-2.5 top-1/2 transform -translate-y-1/2 text-zinc-600 text-[8px] pointer-events-none"></i>
                    </div>
                    
                    <div class="relative">
                        <select x-model="filterBuilding" class="w-full pl-3 pr-8 py-1.5 text-[10px] font-bold text-zinc-400 bg-zinc-900 rounded-lg border border-zinc-800 focus:border-cyan-500 focus:ring-0 appearance-none cursor-pointer truncate">
                            <option value="">Semua Gedung</option>
                            @foreach($buildings as $b)
                                <option value="{{ $b->nama_gedung }}">{{ $b->nama_gedung }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-2.5 top-1/2 transform -translate-y-1/2 text-zinc-600 text-[8px] pointer-events-none"></i>
                    </div>
                </div>
            </div>
            
            <!-- Camera Cards List -->
            <div class="flex-1 overflow-y-auto p-3.5 space-y-2.5 custom-scrollbar">
                @foreach($cctvs as $cctv)
                <div class="bg-zinc-900/60 p-3 rounded-2xl border border-zinc-800/80 active:bg-zinc-800/40 hover:border-cyan-500/40 cursor-pointer flex items-center justify-between shadow-sm transition-all select-none group"
                     x-show="
                         (search === '' || '{{ strtolower($cctv->nama_cctv) }}'.includes(search.toLowerCase())) &&
                         (filterFaculty === '' || '{{ $cctv->building->fakultas ?? '' }}' === filterFaculty) &&
                         (filterBuilding === '' || '{{ $cctv->building->nama_gedung ?? '' }}' === filterBuilding)
                     "
                     @click="assignCamera({ id: {{ $cctv->id }}, name: '{{ $cctv->nama_cctv }}', building: '{{ $cctv->building->nama_gedung ?? '-' }}', faculty: '{{ $cctv->building->fakultas ?? '-' }}', liveUrl: '{{ $cctv->live_stream_url }}' })">
                     
                     <div class="flex items-center gap-3 min-w-0">
                         <!-- Camera Icon Status -->
                         <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-colors
                             {{ $cctv->status === 'online' ? 'bg-emerald-500/10 text-emerald-400 group-hover:bg-emerald-500 group-hover:text-white' : 'bg-red-500/10 text-red-400 group-hover:bg-red-500 group-hover:text-white' }}">
                             <i class="fas fa-video text-sm"></i>
                         </div>
                         
                         <!-- Title & Description -->
                         <div class="min-w-0">
                             <p class="text-xs font-bold text-zinc-100 truncate flex items-center gap-1.5">
                                 <span class="truncate">{{ $cctv->nama_cctv }}</span>
                                 <span class="w-1.5 h-1.5 rounded-full {{ $cctv->status === 'online' ? 'bg-emerald-400' : 'bg-red-400' }}"></span>
                             </p>
                             <p class="text-[9px] font-medium text-zinc-500 truncate mt-0.5">
                                 {{ $cctv->building->nama_gedung ?? '-' }} &bull; {{ $cctv->building->fakultas ?? '-' }}
                             </p>
                         </div>
                     </div>
                     
                     <!-- Right Play Icon Indicator -->
                     <div class="w-8 h-8 rounded-full bg-zinc-800 border border-zinc-700/80 flex items-center justify-center text-zinc-400 transition group-hover:text-cyan-400 active:scale-90">
                         <i class="fas fa-play text-[9px] ml-0.5 pointer-events-none"></i>
                     </div>
                </div>
                @endforeach
            </div>
        </div>

    </main>

    <!-- Script Logic Mobile Monitoring -->
    <script>
        function mobileMonitoring() {
            return {
                gridSize: 1, 
                activeSlots: {}, 
                selectedSlot: 1, 
                showTimeline: false,
                search: '', 
                filterFaculty: '', 
                filterBuilding: '', 
                
                selectedDate: new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0') + '-' + String(new Date().getDate()).padStart(2, '0'),
                currentTimelineData: [], 
                timelineTimeDisplay: 'LIVE',
                isPlaying: true,

                init() {
                    const savedGrid = localStorage.getItem('cctv_live_layout_grid_size_mobile');
                    if (savedGrid) {
                        this.gridSize = parseInt(savedGrid);
                    }
                    const savedSlots = localStorage.getItem('cctv_live_layout_active_slots_mobile');
                    if (savedSlots) {
                        try {
                            this.activeSlots = JSON.parse(savedSlots);
                            this.$nextTick(() => {
                                for (let i = 1; i <= this.gridSize; i++) {
                                    if (this.activeSlots[i]) {
                                        this.playLive(i);
                                    }
                                }
                            });
                        } catch (e) {
                            console.error("Error restoring slots:", e);
                        }
                    }
                    
                    // Auto-clock for LIVE stream
                    setInterval(() => {
                        const now = new Date();
                        if (this.selectedSlot && this.activeSlots[this.selectedSlot]?.mode === 'live') {
                            const sec = (now.getHours() * 3600) + (now.getMinutes() * 60) + now.getSeconds();
                            this.timelineTimeDisplay = now.toLocaleTimeString('en-GB');
                        }
                    }, 1000);
                },

                setGrid(size) {
                    this.gridSize = size;
                    localStorage.setItem('cctv_live_layout_grid_size_mobile', size);
                    if (this.selectedSlot > size) {
                        this.selectedSlot = 1;
                    }
                    this.$nextTick(() => {
                        for (let i = 1; i <= this.gridSize; i++) {
                            if (this.activeSlots[i]) {
                                this.playLive(i);
                            }
                        }
                    });
                },

                selectSlot(index) {
                    this.selectedSlot = index;
                    this.fetchTimelineData();
                },

                assignCamera(cam) {
                    this.activeSlots[this.selectedSlot] = {
                        id: cam.id,
                        name: cam.name,
                        building: cam.building,
                        faculty: cam.faculty,
                        mode: 'live',
                        liveUrl: cam.liveUrl,
                        zoom: 1,
                        x: 0,
                        y: 0
                    };
                    
                    localStorage.setItem('cctv_live_layout_active_slots_mobile', JSON.stringify(this.activeSlots));
                    this.playLive(this.selectedSlot);
                    this.fetchTimelineData();
                },

                playLive(index) {
                    const slot = this.activeSlots[index];
                    if (!slot) return;
                    slot.mode = 'live';

                    const videoEl = document.getElementById('video-playback-mobile-' + index);
                    if (videoEl) {
                        videoEl.pause();
                        videoEl.removeAttribute('src');
                        videoEl.load();
                    }

                    const iframe = document.getElementById('iframe-live-mobile-' + index);
                    if (iframe) {
                        iframe.src = 'about:blank';
                        setTimeout(() => { iframe.src = slot.liveUrl; }, 50);
                    }
                },

                playRecord(index, fileUrl, offsetSeconds, startTs) {
                    const slot = this.activeSlots[index];
                    if (!slot) return;
                    slot.mode = 'playback';
                    slot.recordStartOffset = parseFloat(startTs);

                    const iframe = document.getElementById('iframe-live-mobile-' + index);
                    if (iframe) iframe.src = 'about:blank';

                    this.$nextTick(() => {
                        const vid = document.getElementById('video-playback-mobile-' + index);
                        if (vid) {
                            vid.onplay = () => { this.isPlaying = true; };
                            vid.onpause = () => { this.isPlaying = false; };
                            vid.src = fileUrl;
                            vid.currentTime = offsetSeconds;
                            vid.play().catch(e => console.log("Auto-play prevented:", e));
                        }
                    });
                },

                removeCamera(index) {
                    delete this.activeSlots[index];
                    localStorage.setItem('cctv_live_layout_active_slots_mobile', JSON.stringify(this.activeSlots));

                    const iframe = document.getElementById('iframe-live-mobile-' + index);
                    if (iframe) iframe.src = 'about:blank';

                    const vid = document.getElementById('video-playback-mobile-' + index);
                    if (vid) {
                        vid.pause();
                        vid.removeAttribute('src');
                        vid.load();
                    }
                },

                fetchTimelineData() {
                    const slot = this.activeSlots[this.selectedSlot];
                    if (!slot) {
                        this.currentTimelineData = [];
                        return;
                    }
                    
                    fetch(`/monitoring/timeline/${slot.id}?date=${this.selectedDate}`)
                        .then(res => res.json())
                        .then(data => {
                            this.currentTimelineData = data.segments || [];
                        })
                        .catch(e => console.error("Error loading segments", e));
                },

                togglePlayback() {
                    const vid = document.getElementById('video-playback-mobile-' + this.selectedSlot);
                    if (vid) {
                        if (vid.paused) {
                            vid.play();
                            this.isPlaying = true;
                        } else {
                            vid.pause();
                            this.isPlaying = false;
                        }
                    }
                },

                seek(seconds) {
                    const vid = document.getElementById('video-playback-mobile-' + this.selectedSlot);
                    if (vid) vid.currentTime += seconds;
                },

                goLive() {
                    this.playLive(this.selectedSlot);
                    this.timelineTimeDisplay = 'LIVE';
                }
            };
        }
    </script>

</body>
</html>
