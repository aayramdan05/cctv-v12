<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    <!-- X-DATA HARUS BERADA DI WADAH UTAMA -->
    <main id="main-content" 
          x-data="hybridMonitoring()"
          class="flex flex-col h-screen pt-20 p-4 gap-4 bg-slate-100 transition-all duration-300"
          :class="isFullscreen ? 'fixed inset-0 z-50 bg-slate-900 p-0 pt-0' : ''">
        
        <!-- HEADER & TOOLBAR (Sama seperti sebelumnya) -->
        <div class="flex justify-between items-center shrink-0 h-12 gap-2" x-show="!isFullscreen" x-transition>
            <div class="flex items-center gap-4 min-w-0 shrink">
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 truncate">Live Monitoring</h2>
                <p class="text-xs text-slate-500 hidden md:block">WebRTC Realtime & Instant Playback.</p>
            </div>

            <!-- Toolbar -->
            <div class="flex gap-2 overflow-x-auto no-scrollbar py-1 shrink-0 max-w-full">
                <!-- Date Picker -->
                <div class="flex items-center bg-white rounded-lg border border-slate-200 px-3 py-1.5 shadow-sm gap-2 shrink-0">
                    <i class="fas fa-calendar-alt text-slate-400 text-xs pointer-events-none"></i>
                    <input type="date" x-model="selectedDate" @change="refreshTimeline()" 
                           class="border-none p-0 text-xs font-bold text-slate-700 focus:ring-0 bg-transparent h-full cursor-pointer w-24">
                </div>

                <!-- Grid Selector -->
                <div class="bg-white p-1 rounded-lg border border-slate-200 flex shadow-sm shrink-0">
                    <button @click="setGrid(1)" :class="{'bg-cyan-100 text-cyan-700': gridSize===1}" class="p-1.5 rounded transition w-8 h-8 flex items-center justify-center hover:bg-slate-50"><i class="fas fa-square pointer-events-none"></i></button>
                    <button @click="setGrid(4)" :class="{'bg-cyan-100 text-cyan-700': gridSize===4}" class="p-1.5 rounded transition w-8 h-8 flex items-center justify-center hover:bg-slate-50"><i class="fas fa-th-large pointer-events-none"></i></button>
                    <button @click="setGrid(9)" :class="{'bg-cyan-100 text-cyan-700': gridSize===9}" class="p-1.5 rounded transition w-8 h-8 flex items-center justify-center hover:bg-slate-50"><i class="fas fa-th pointer-events-none"></i></button>
                </div>
                
                <!-- Toggle Timeline -->
                <button @click="showTimeline = !showTimeline" 
                        class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition flex items-center gap-2 shadow-sm shrink-0"
                        :class="{'bg-cyan-50 border-cyan-200 text-cyan-600': showTimeline}"
                        title="Toggle Timeline">
                    <i class="fas pointer-events-none" :class="showTimeline ? 'fa-chart-area' : 'fa-chart-bar'"></i>
                </button>

                <!-- Kiosk Mode -->
                <button @click="toggleFullscreen()" class="px-4 py-1.5 rounded-lg bg-slate-800 text-white text-xs font-bold hover:bg-slate-700 transition flex items-center gap-2 shadow select-none shrink-0">
                    <i class="fas pointer-events-none" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
                    <span x-text="isFullscreen ? 'Exit' : 'Kiosk'" class="hidden sm:inline"></span>
                </button>

                <!-- Sidebar Toggle -->
                <button @click="showSidebar = !showSidebar" class="w-10 h-10 bg-white border rounded-lg shadow-sm hover:bg-slate-50 text-slate-600 flex items-center justify-center transition select-none shrink-0">
                    <i class="fas pointer-events-none" :class="showSidebar ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
                </button>
            </div>
        </div>

        <!-- MAIN AREA -->
        <div class="flex flex-col lg:flex-row flex-1 gap-6 overflow-hidden min-h-0 relative">
            
            <!-- KIRI: VIDEO WALL + TIMELINE -->
            <div class="flex-1 flex flex-col min-w-0 gap-4 z-10 min-h-0">
                
                <!-- 1. GRID VIDEO -->
                <div class="flex-1 bg-slate-900 rounded-2xl overflow-hidden shadow-lg border border-slate-700 relative min-h-[50vh] lg:min-h-0">
                    <div class="grid h-full w-full gap-0.5 bg-black"
                        :class="{ 'grid-cols-1 grid-rows-1': gridSize === 1, 'grid-cols-2 grid-rows-2': gridSize === 4, 'grid-cols-3 grid-rows-3': gridSize === 9 }">
                        
                        <template x-for="i in gridSize">
                            <div class="relative border border-slate-800 bg-black group overflow-hidden cursor-pointer"
                                :class="{'ring-2 ring-cyan-400 z-20': selectedSlot === i}"
                                @click="selectSlot(i)"
                                oncontextmenu="return false;"> 
                                
                                <div x-show="!activeSlots[i]" class="absolute inset-0 flex flex-col items-center justify-center text-slate-700 pointer-events-none">
                                    <i class="fas fa-plus text-3xl mb-2 opacity-20"></i>
                                    <span class="text-xs font-mono text-slate-600">Slot <span x-text="i"></span></span>
                                </div>

                                <!-- Overlay Drag & Drop -->
                                <div x-show="isDragging" 
                                     class="absolute inset-0 z-50 bg-cyan-500/20 border-2 border-dashed border-cyan-400 flex items-center justify-center text-white font-bold backdrop-blur-sm transition-opacity"
                                     @dragover.prevent 
                                     @drop="handleDrop($event, i)">
                                    <span class="bg-black/50 px-2 py-1 rounded">Drop Here</span>
                                </div>

                                <template x-if="activeSlots[i]">
                                    <div class="w-full h-full relative bg-black overflow-hidden flex items-center justify-center"
                                            @wheel.prevent="handleWheel($event, i)"
                                            @mousedown.prevent="startPan($event, i)"
                                            @mousemove.prevent="doPan($event, i)"
                                            @mouseup.prevent="endPan(i)"
                                            @mouseleave.prevent="endPan(i)"
                                            :class="(activeSlots[i].zoom > 1) ? 'cursor-move' : ''">
                                        
                                        <!-- A. IFRAME LIVE -->
                                        <iframe 
                                            :id="'iframe-live-' + i"
                                            x-show="activeSlots[i].mode === 'live'"
                                            class="absolute w-full h-full object-contain border-none transition-transform duration-75 ease-out origin-center"
                                            allow="autoplay; encrypted-media; fullscreen; picture-in-picture"
                                            playsinline
                                            allowfullscreen>
                                        </iframe>

                                        <!-- B. VIDEO TAG (PLAYBACK MP4) -->
                                        <!-- preload="auto" PENTING untuk 8x speed -->
                                        <video 
                                            :id="'video-playback-' + i"
                                            x-show="activeSlots[i].mode === 'playback'"
                                            class="absolute w-full h-full object-contain transition-transform duration-75 ease-out origin-center" 
                                            autoplay 
                                            controlsList="nodownload noremoteplayback" 
                                            oncontextmenu="return false;"
                                            playsinline
                                            preload="auto"
                                            @play="syncControls()"
                                            @pause="syncControls()"
                                            @ratechange="syncControls()"
                                            @waiting="handleWaiting(i)"
                                            @playing="handlePlaying(i)"
                                            @timeupdate="handleTimeUpdate(i)"
                                            @ended="handleVideoEnded(i)">
                                        </video>

                                        <!-- Status Buffering Overlay -->
                                        <div x-show="activeSlots[i].isBuffering"
                                             class="absolute inset-0 bg-black/70 flex flex-col items-center justify-center z-30 transition-opacity duration-300">
                                            <i class="fas fa-sync-alt fa-spin text-3xl text-cyan-400 mb-3"></i>
                                            <span class="text-white text-xs font-mono">Buffering...</span>
                                            <span class="text-gray-400 text-[10px]" x-show="playbackSpeed > 1">Auto-recovering speed...</span>
                                        </div>

                                        <!-- Zoom Overlay -->
                                        <div class="absolute bottom-2 right-2 px-2 py-1 rounded bg-black/60 backdrop-blur z-20 pointer-events-none transition-opacity duration-300"
                                                x-show="activeSlots[i].zoom > 1">
                                            <span class="text-[10px] font-bold text-white font-mono" x-text="Math.round(activeSlots[i].zoom * 100) + '%'"></span>
                                        </div>

                                        <!-- Info Overlay -->
                                        <div class="absolute top-2 left-2 px-2 py-1 rounded bg-black/60 backdrop-blur flex items-center gap-2 z-20 pointer-events-auto">
                                            <div class="w-2 h-2 rounded-full" :class="activeSlots[i].mode === 'live' ? 'bg-red-500 animate-pulse' : 'bg-green-500'"></div>
                                            <span class="text-[10px] font-bold text-white uppercase" x-text="activeSlots[i].mode === 'live' ? 'LIVE' : 'REC'"></span>
                                            <span class="text-[10px] text-gray-300 border-l border-gray-600 pl-2 ml-1 truncate max-w-[100px]" x-text="activeSlots[i].name"></span>
                                            
                                            <!-- Tombol Refresh Live -->
                                            <button @click.stop="reconnectLive(i)" 
                                                    x-show="activeSlots[i].mode === 'live'"
                                                    class="ml-2 bg-slate-700 hover:bg-cyan-600 text-white w-6 h-6 rounded flex items-center justify-center transition shadow-sm" 
                                                    title="Refresh Stream">
                                                <i class="fas fa-sync-alt text-[10px]"></i>
                                            </button>
                                        </div>

                                        <!-- Tombol Close -->
                                        <button @click.stop="removeCamera(i)" class="absolute top-2 right-2 w-6 h-6 bg-red-600/80 hover:bg-red-500 text-white rounded flex items-center justify-center opacity-0 group-hover:opacity-100 transition z-30 pointer-events-auto"><i class="fas fa-times text-xs"></i></button>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 2. TIMELINE BAR -->
                <div class="h-24 bg-white border border-slate-300 p-3 flex flex-col shrink-0 z-30 transition-all rounded-xl shadow-lg relative"
                        x-show="selectedSlot && activeSlots[selectedSlot] && showTimeline"
                        x-transition>
                    
                    <!-- HEADER -->
                    <div class="flex justify-between items-center px-1 mb-2 relative z-40 h-10">
                        <div class="flex items-center gap-3 z-10">
                            <span class="text-cyan-600 font-bold text-sm truncate max-w-[100px] sm:max-w-xs" x-text="activeSlots[selectedSlot]?.name"></span>
                            <span class="text-gray-300">|</span>
                            <span class="text-slate-500 text-xs font-bold" x-text="selectedDate"></span>
                            <span class="text-white text-xs font-mono bg-slate-800 px-2 py-0.5 rounded border border-slate-600" x-text="timelineTimeDisplay"></span>
                        </div>
                        
                        <!-- CONTROLS -->
                        <div class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 flex items-center gap-4 z-50" 
                                x-show="activeSlots[selectedSlot]?.mode === 'playback'"
                                x-transition>
                            
                            <button @click.stop.prevent="seek(-10)" class="text-slate-400 hover:text-cyan-600 transition transform hover:scale-110 active:scale-95"><i class="fas fa-undo text-sm"></i></button>
                            <button @click.stop.prevent="togglePlayback()" class="text-cyan-600 hover:text-cyan-500 transition transform hover:scale-110 active:scale-95"><i class="fas text-3xl" :class="isPlaying ? 'fa-pause' : 'fa-play'"></i></button>
                            <button @click.stop.prevent="seek(10)" class="text-slate-400 hover:text-cyan-600 transition transform hover:scale-110 active:scale-95"><i class="fas fa-redo text-sm"></i></button>

                            <div class="hidden sm:flex items-center gap-3 border-l border-slate-200 pl-3">
                                <!-- Speed -->
                                <div class="relative" x-data="{ speedOpen: false }" @click.outside="speedOpen = false">
                                    <button @click.stop.prevent="speedOpen = !speedOpen" 
                                            class="flex items-center gap-0.5 text-xs font-bold text-slate-500 hover:text-cyan-600 transition active:scale-95">
                                        <!-- UI Tampilkan targetSpeed, bukan current playbackRate yang mungkin drop -->
                                        <span x-text="targetSpeed + 'x'"></span>
                                    </button>
                                    <div x-show="speedOpen" class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-12 bg-white border border-slate-200 rounded shadow-lg z-[100] py-0.5">
                                        <template x-for="speed in [0.5, 1.0, 2.0, 4.0, 8.0]">
                                            <button @click.stop="setSpeed(speed); speedOpen = false" 
                                                    class="block w-full text-center py-1.5 text-[10px] font-bold hover:bg-cyan-50 transition border-b border-slate-50 last:border-none"
                                                    :class="targetSpeed == speed ? 'bg-cyan-100 text-cyan-700' : 'text-slate-600'"
                                                    x-text="speed + 'x'">
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <!-- Zoom -->
                                <div class="relative" x-data="{ zoomOpen: false }" @click.outside="zoomOpen = false">
                                    <button @click.stop.prevent="zoomOpen = !zoomOpen" class="flex items-center gap-0.5 text-slate-500 hover:text-cyan-600 transition active:scale-95"><i class="fas fa-search-plus text-xs"></i></button>
                                    <div x-show="zoomOpen" class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-12 bg-white border border-slate-200 rounded shadow-lg z-[100] py-0.5">
                                        <template x-for="z in [1.0, 1.5, 2.0, 3.0]">
                                            <button @click.stop="setZoom(z); zoomOpen = false" 
                                                    class="block w-full text-center py-1.5 text-[10px] font-bold hover:bg-cyan-50 transition"
                                                    :class="activeSlots[selectedSlot]?.zoom == z ? 'bg-cyan-100 text-cyan-700' : 'text-slate-600'"
                                                    x-text="z + 'x'">
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="z-10">
                            <button @click="goLive(selectedSlot)" 
                                    :disabled="!isToday || activeSlots[selectedSlot]?.mode === 'live'"
                                    :class="(isToday && activeSlots[selectedSlot]?.mode === 'live') ? 'bg-slate-100 text-slate-400 cursor-default' : 'bg-red-600 text-white hover:bg-red-500 animate-pulse cursor-pointer'"
                                    class="px-3 py-1 rounded font-bold text-[10px] flex items-center gap-1 transition shadow-sm">
                                <i class="fas fa-broadcast-tower pointer-events-none"></i> <span x-text="isToday ? 'REALTIME' : 'BACK'" class="hidden sm:inline"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Timeline Slider -->
                    <div class="relative h-12 w-full select-none cursor-pointer group bg-slate-800 rounded border border-slate-600 z-10"
                            id="global-timeline"
                            @mousemove="handleTimelineHover($event)"
                            @mouseleave="hoverPercent = -100"
                            @click="handleTimelineClick($event)">
                        <!-- Stripes -->
                        <div class="absolute inset-0 top-2 bottom-0 bg-slate-200 rounded overflow-hidden">
                            <div class="absolute inset-0 flex pointer-events-none z-0">
                                <template x-for="h in 25">
                                    <div class="flex-1 border-l border-slate-400/50 h-full relative">
                                        <span class="absolute -top-2 left-1 text-[9px] text-slate-500 font-mono font-bold" x-text="(h-1).toString().padStart(2,'0')"></span>
                                    </div>
                                </template>
                            </div>
                            <!-- Segments -->
                            <template x-for="seg in currentTimelineData">
                                <div class="absolute top-0 bottom-0 z-10 cursor-pointer transition-all border-r border-black/10"
                                     :class="(seg.start + seg.duration) > (currentPlayheadPercent / 100 * 86400) ? 'bg-red-500/90 animate-pulse' : 'bg-green-500 hover:bg-green-400'"
                                     :style="'left: ' + (seg.start / 86400 * 100) + '%; width: calc(' + (seg.duration / 86400 * 100) + '% + 2px); min-width: 5px;'"
                                     :title="'Rekaman: ' + seg.human_start"
                                     @click="playRecord(selectedSlot, seg.url, 0, seg.start)">
                                </div>
                            </template>
                        </div>
                        <!-- Playhead (NOW REACTIVE) -->
                        <div class="absolute top-0 bottom-0 w-0.5 bg-red-600 z-20 pointer-events-none transition-all duration-75 ease-linear"
                                :style="'left: ' + currentPlayheadPercent + '%'">
                             <div class="w-2.5 h-2.5 -ml-1 bg-red-600 rounded-full shadow border border-white relative top-0"></div>
                        </div>
                        <!-- Hover -->
                        <div class="absolute top-0 transform -translate-x-1/2 -translate-y-full pb-1 z-50 pointer-events-none"
                                :style="'left: ' + hoverPercent + '%'" x-show="hoverPercent > 0 && hoverPercent < 100">
                             <div class="bg-slate-800 text-white text-[10px] font-mono font-bold px-2 py-1 rounded shadow-lg flex flex-col items-center border border-slate-600">
                                <span x-text="hoverTimeDisplay"></span>
                                <div class="w-2 h-2 bg-slate-800 transform rotate-45 -mt-1"></div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KANAN: LIST -->
            <div class="w-full lg:w-80 xl:w-96 bg-white border border-slate-200 rounded-2xl shadow-lg flex flex-col shrink-0 transition-all duration-300 z-30 h-64 lg:h-auto"
                    x-show="showSidebar && !isFullscreen"
                    :class="{'absolute top-0 right-0 h-full w-full lg:relative lg:w-80 xl:w-96': isFullscreen}">
                <div class="p-3 border-b border-slate-100 bg-slate-50 rounded-t-2xl font-bold text-slate-700 text-sm flex justify-between items-center">
                    <span>Camera List</span><span class="text-xs text-slate-400 bg-white border px-2 py-0.5 rounded-full">{{ $cctvs->count() }}</span>
                </div>
                <div class="p-2 border-b border-slate-100"><input type="text" x-model="search" placeholder="Cari..." class="w-full px-3 py-1.5 text-xs rounded border"></div>
                <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scrollbar">
                    @foreach($cctvs as $cctv)
                    <div class="bg-white p-2 rounded border hover:border-cyan-400 cursor-pointer flex items-center gap-3 shadow-sm hover:shadow-md transition select-none group"
                        draggable="true" 
                        @dragstart="handleDragStart($event, {{ $cctv->id }}, '{{ $cctv->nama_cctv }}', '{{ $cctv->building->nama_gedung ?? '-' }}', '{{ $cctv->building->fakultas ?? '-' }}', '{{ $cctv->live_stream_url }}')"
                        @dragend="isDragging = false"
                        @click.stop="addCameraOnClick({ id: {{ $cctv->id }}, name: '{{ $cctv->nama_cctv }}', building: '{{ $cctv->building->nama_gedung ?? '-' }}', faculty: '{{ $cctv->building->fakultas ?? '-' }}', liveUrl: '{{ $cctv->live_stream_url }}' })">
                        <div class="w-8 h-8 rounded bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-cyan-50 group-hover:text-cyan-500 transition"><i class="fas fa-video"></i></div>
                        <div class="min-w-0"><p class="text-xs font-bold text-slate-700 truncate group-hover:text-cyan-600 transition">{{ $cctv->nama_cctv }}</p><p class="text-[9px] text-slate-500 truncate">{{ $cctv->building->nama_gedung ?? '-' }}</p></div>
                        <div class="ml-auto opacity-0 group-hover:opacity-100 transition"><i class="fas fa-plus-circle text-cyan-400"></i></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </main>

    <script>
        function hybridMonitoring() {
            return {
                gridSize: 1, activeSlots: {}, selectedSlot: null, showSidebar: true, showTimeline: true,
                search: '', currentHost: window.location.hostname, isFullscreen: false,
                isDragging: false, 
                
                selectedDate: new Date().toISOString().split('T')[0],
                currentTimelineData: [], currentPlayheadPercent: 100, hoverPercent: -100, hoverTimeDisplay: '00:00:00', timelineTimeDisplay: 'LIVE',
                
                isPlaying: true,
                playbackSpeed: 1.0, 
                targetSpeed: 1.0, // <-- VARIABLE BARU UNTUK PERSISTENSI KECEPATAN
                
                preloader: null, 
                panning: false, panSlot: null, startX: 0, startY: 0,

                get isToday() {
                    const today = new Date().toISOString().split('T')[0];
                    return this.selectedDate === today;
                },

                init() {
                    this.preloader = document.createElement('video');
                    this.preloader.preload = 'auto';

                    document.addEventListener('fullscreenchange', () => { 
                        this.isFullscreen = !!document.fullscreenElement; 
                        if(!this.isFullscreen) { this.showSidebar = true; this.showTimeline = true; }
                        else { this.showTimeline = false; }
                    });
                    
                    setInterval(() => {
                        if (this.selectedSlot && this.activeSlots[this.selectedSlot]?.mode === 'live' && this.isToday) {
                            const now = new Date();
                            const sec = (now.getHours()*3600) + (now.getMinutes()*60) + now.getSeconds();
                            this.currentPlayheadPercent = (sec / 86400) * 100;
                            this.timelineTimeDisplay = "LIVE " + now.toLocaleTimeString('en-GB');
                        }
                    }, 1000);
                },

                refreshTimeline() { if(this.selectedSlot) this.selectSlot(this.selectedSlot); },

                selectSlot(index) {
                    this.selectedSlot = index;
                    this.syncControls(); 
                    const slot = this.activeSlots[index];
                    if(slot) {
                        const antiCache = new Date().getTime();
                        fetch(`/monitoring/timeline/${slot.id}?date=${this.selectedDate}&_=${antiCache}`)
                            .then(res => res.json())
                            .then(data => { this.currentTimelineData = data; })
                            .catch(e => this.currentTimelineData = []);
                    } else {
                        this.currentTimelineData = [];
                    }
                },

                // --- EVENT HANDLERS VIDEO ---
                handleTimeUpdate(index) {
                    if (this.selectedSlot !== index) return;
                    const slot = this.activeSlots[index];
                    const vid = document.getElementById('video-playback-' + index);
                    
                    if (slot && vid && !isNaN(vid.currentTime) && slot.recordStartOffset) {
                        const currentSec = parseFloat(slot.recordStartOffset) + vid.currentTime;
                        this.currentPlayheadPercent = (currentSec / 86400) * 100;
                        this.timelineTimeDisplay = this.formatTime(currentSec);

                        const remaining = vid.duration - vid.currentTime;
                        if (remaining < 60 && !slot.nextVideoPreloaded) {
                            this.preloadNextVideo(index);
                            slot.nextVideoPreloaded = true; 
                        }
                    }
                },

                handleWaiting(index) {
                    const slot = this.activeSlots[index];
                    if(!slot) return;
                    slot.isBuffering = true;
                    
                    // --- SMART RECOVERY ---
                    // Jika buffering, turunkan speed actual video ke 1x agar buffer terisi
                    // TAPI jangan ubah this.targetSpeed (agar bisa kembali ke 8x nanti)
                    const vid = document.getElementById('video-playback-' + index);
                    if(vid && vid.playbackRate > 1.0) {
                        console.log("Buffering... temporary drop speed to 1x");
                        vid.playbackRate = 1.0; 
                        // Note: this.playbackSpeed tidak diubah di sini agar UI tetap menunjukkan target
                    }
                },

                handlePlaying(index) {
                    const slot = this.activeSlots[index];
                    if(slot) slot.isBuffering = false;

                    // --- AUTO RESTORE SPEED ---
                    // Begitu video jalan lagi, paksa kembali ke targetSpeed (misal 8x)
                    const vid = document.getElementById('video-playback-' + index);
                    if(vid && this.targetSpeed > 1.0 && vid.playbackRate !== this.targetSpeed) {
                        console.log("Buffer recovered. Restoring speed to " + this.targetSpeed + "x");
                        vid.playbackRate = parseFloat(this.targetSpeed);
                        
                        // Opsional: Matikan suara di high speed untuk performa
                        if(this.targetSpeed > 2) vid.muted = true;
                    }
                },

                preloadNextVideo(index) {
                    const vid = document.getElementById('video-playback-' + index);
                    const currentSrc = decodeURIComponent(vid.src);
                    const idx = this.currentTimelineData.findIndex(seg => currentSrc.includes(encodeURI(seg.url)) || currentSrc.includes(seg.url));
                    
                    if (idx !== -1 && idx < this.currentTimelineData.length - 1) {
                        const nextSeg = this.currentTimelineData[idx + 1];
                        console.log("Preloading next: " + nextSeg.human_start);
                        this.preloader.src = nextSeg.url;
                        this.preloader.load();
                    }
                },

                assignCameraToSlot(cam, index) {
                    this.activeSlots[index] = { 
                        id: cam.id, name: cam.name, building: cam.building, faculty: cam.faculty, 
                        mode: 'live', timestampDisplay: '', hlsInstance: null, liveUrl: cam.liveUrl,
                        zoom: 1, x: 0, y: 0, isBuffering: false, nextVideoPreloaded: false
                    }; 
                    this.selectSlot(index);
                    this.$nextTick(() => { this.playLive(index); });
                },

                addCameraOnClick(cam) {
                    let targetSlot = -1;
                    for (let i = 1; i <= this.gridSize; i++) { if (!this.activeSlots[i]) { targetSlot = i; break; } }
                    if (targetSlot === -1 && this.selectedSlot) targetSlot = this.selectedSlot;
                    else if (targetSlot === -1) targetSlot = 1;
                    this.assignCameraToSlot(cam, targetSlot);
                },

                syncControls() {
                    if(!this.selectedSlot) return;
                    const vid = document.getElementById('video-playback-' + this.selectedSlot);
                    if(vid) {
                        this.isPlaying = !vid.paused;
                        // HANYA update playbackSpeed jika targetnya 1x (normal), 
                        // agar tidak flicker saat auto-drop speed terjadi.
                        if(this.targetSpeed === 1.0) {
                             this.playbackSpeed = vid.playbackRate;
                        } else {
                             // Jika target > 1, biarkan UI tetap menampilkan target
                             this.playbackSpeed = this.targetSpeed;
                        }
                    } else {
                        this.isPlaying = false;
                        this.playbackSpeed = 1.0;
                        this.targetSpeed = 1.0;
                    }
                },

                togglePlayback() {
                    if(!this.selectedSlot) return;
                    const vid = document.getElementById('video-playback-' + this.selectedSlot);
                    if(vid) {
                        if(vid.paused) vid.play().catch(e => {}); 
                        else vid.pause();
                        this.isPlaying = !vid.paused;
                    }
                },

                seek(seconds) {
                    if(!this.selectedSlot) return;
                    const vid = document.getElementById('video-playback-' + this.selectedSlot);
                    if(vid) vid.currentTime += seconds;
                },

                // UPDATE: Set Target Speed
                setSpeed(speed) {
                    this.targetSpeed = speed; // Simpan niat user
                    this.playbackSpeed = speed;
                    
                    if(!this.selectedSlot) return;
                    const vid = document.getElementById('video-playback-' + this.selectedSlot);
                    if(vid) {
                        vid.playbackRate = parseFloat(speed);
                        // Matikan suara jika speed tinggi
                        vid.muted = (speed > 2.0);
                    }
                },

                setZoom(zoom) {
                    if(!this.selectedSlot) return;
                    this.activeSlots[this.selectedSlot].zoom = zoom;
                    if(zoom === 1) { this.activeSlots[this.selectedSlot].x = 0; this.activeSlots[this.selectedSlot].y = 0; }
                    this.applyTransform(this.selectedSlot);
                },

                applyTransform(index) {
                    const slot = this.activeSlots[index]; if(!slot) return;
                    const transform = `translate(${slot.x || 0}px, ${slot.y || 0}px) scale(${slot.zoom || 1})`;
                    const iframe = document.getElementById('iframe-live-' + index); if(iframe) iframe.style.transform = transform;
                    const video = document.getElementById('video-playback-' + index); if(video) video.style.transform = transform;
                },

                handleWheel(e, index) {
                    if (!this.activeSlots[index]) return;
                    let slot = this.activeSlots[index];
                    let currentZoom = slot.zoom || 1;
                    if (e.deltaY < 0) currentZoom += 0.1; else currentZoom -= 0.1;
                    currentZoom = Math.min(Math.max(currentZoom, 1), 5);
                    slot.zoom = currentZoom;
                    if(currentZoom === 1) { slot.x = 0; slot.y = 0; }
                    this.applyTransform(index);
                },

                startPan(e, index) {
                    let slot = this.activeSlots[index]; if(!slot || (slot.zoom || 1) <= 1) return;
                    this.panning = true; this.panSlot = index;
                    this.startX = e.clientX - (slot.x || 0); this.startY = e.clientY - (slot.y || 0);
                },
                doPan(e, index) {
                    if(!this.panning || this.panSlot !== index) return;
                    let slot = this.activeSlots[index];
                    slot.x = e.clientX - this.startX; slot.y = e.clientY - this.startY;
                    this.applyTransform(index);
                },
                endPan(index) { this.panning = false; this.panSlot = null; },

                handleVideoEnded(index) {
                    if (index !== this.selectedSlot) { if(index === this.selectedSlot) this.isPlaying = false; return; }
                    const vid = document.getElementById('video-playback-' + index); if(!vid) return;
                    const currentSrc = decodeURIComponent(vid.src);
                    const idx = this.currentTimelineData.findIndex(seg => currentSrc.includes(encodeURI(seg.url)) || currentSrc.includes(seg.url));

                    if (idx !== -1 && idx < this.currentTimelineData.length - 1) {
                        const nextSeg = this.currentTimelineData[idx + 1];
                        console.log("Auto-playing next part:", nextSeg.human_start);
                        this.playRecord(index, nextSeg.url, 0, nextSeg.start);
                        this.isPlaying = true;
                    } else {
                        this.isPlaying = false;
                        console.log("End of playback list.");
                    }
                },

                playLive(index) {
                    const slot = this.activeSlots[index]; if(!slot || !slot.id) return;
                    if(!this.isToday) { this.selectedDate = new Date().toISOString().split('T')[0]; this.refreshTimeline(); }
                    slot.mode = 'live'; slot.zoom = 1; slot.x = 0; slot.y = 0;
                    this.applyTransform(index);
                    const videoEl = document.getElementById('video-playback-' + index); if(videoEl) videoEl.pause();
                    const iframe = document.getElementById('iframe-live-' + index);
                    if(iframe) { iframe.src = 'about:blank'; setTimeout(() => { iframe.src = slot.liveUrl; }, 50); }
                },

                reconnectLive(index) { this.playLive(index); },

                playRecord(index, fileUrl, offsetSeconds, startTs) {
                    const slot = this.activeSlots[index];
                    slot.mode = 'playback';
                    slot.recordStartOffset = parseFloat(startTs);
                    slot.nextVideoPreloaded = false;
                    slot.zoom = 1; slot.x = 0; slot.y = 0;
                    this.applyTransform(index);
                    
                    const iframe = document.getElementById('iframe-live-' + index); if(iframe) iframe.src = 'about:blank';

                    this.$nextTick(() => {
                        const video = document.getElementById('video-playback-' + index);
                        if(video) {
                            video.src = fileUrl;
                            video.currentTime = offsetSeconds;
                            
                            // FORCE APPLY TARGET SPEED SAAT PINDAH SEGMENT
                            video.playbackRate = parseFloat(this.targetSpeed);
                            video.muted = (this.targetSpeed > 2.0); // Mute jika speed tinggi

                            video.play().then(() => this.isPlaying = true).catch(e => console.log("Play error:", e));
                        }
                    });
                },

                handleTimelineClick(e) {
                    if(!this.selectedSlot) return;
                    const rect = document.getElementById('global-timeline').getBoundingClientRect();
                    const percent = ((e.clientX - rect.left) / rect.width) * 100;
                    const secondsInDay = 86400; const clickedSeconds = (percent / 100) * secondsInDay;
                    
                    const segment = this.currentTimelineData.find(seg => clickedSeconds >= seg.start && clickedSeconds <= (seg.start + seg.duration));
                    if (segment) {
                        const offset = clickedSeconds - segment.start;
                        this.playRecord(this.selectedSlot, segment.url, offset, segment.start);
                    } else { console.log("Tidak ada rekaman pada jam ini."); }
                },

                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(e => { this.isFullscreen = true; });
                        this.showSidebar = false; this.showTimeline = false;
                    } else {
                        if (document.exitFullscreen) document.exitFullscreen();
                        this.showSidebar = true; this.showTimeline = true;
                    }
                },

                handleTimelineHover(e) { const rect = document.getElementById('global-timeline').getBoundingClientRect(); this.hoverPercent = ((e.clientX - rect.left) / rect.width) * 100; const seconds = (this.hoverPercent / 100) * 86400; this.hoverTimeDisplay = this.formatTime(seconds); },
                
                loadHls(video, url, slotIndex) { console.log("HLS Loader not used for Live View."); },
                formatTime(seconds) { const h = Math.floor(seconds / 3600).toString().padStart(2,'0'); const m = Math.floor((seconds % 3600) / 60).toString().padStart(2,'0'); const s = Math.floor(seconds % 60).toString().padStart(2,'0'); return `${h}:${m}:${s}`; },
                goLive(index) { this.playLive(index); }, setGrid(n) { this.gridSize = n; if(this.selectedSlot > n) this.selectedSlot = null; }, clearAll() { this.activeSlots = {}; this.selectedSlot = null; }, removeCamera(i) { delete this.activeSlots[i]; if(this.selectedSlot === i) { this.selectedSlot = null; this.currentTimelineData = []; } }, matchSearch(name, building) { if (this.search === '') return true; return name.includes(this.search.toLowerCase()) || building.includes(this.search.toLowerCase()); },
                
                handleDragStart(e, id, name, building, faculty, liveUrl) { 
                    this.isDragging = true;
                    const camData = JSON.stringify({ id, name, building, faculty, liveUrl }); 
                    e.dataTransfer.setData('application/json', camData); e.dataTransfer.effectAllowed = 'copy';
                },
                handleDrop(e, index) { 
                    this.isDragging = false;
                    const data = e.dataTransfer.getData('application/json'); if (!data) return; 
                    const cam = JSON.parse(data); this.assignCameraToSlot(cam, index);
                },
                handleDragOver(e) {}, handleDragLeave(e) {}
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 4px; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</x-app-layout>