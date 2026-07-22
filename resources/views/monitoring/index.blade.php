<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    <main id="main-content" 
          x-data="hybridMonitoring()"
          class="flex flex-col h-screen pt-20 p-4 gap-4 bg-slate-100 transition-all duration-300"
          :class="isFullscreen ? 'fixed inset-0 z-50 bg-slate-900 p-0 pt-0' : ''">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center shrink-0 gap-3 md:h-12 min-h-[3rem]" x-show="!isFullscreen" x-transition>
            <div class="flex items-center gap-4 min-w-0 shrink w-full md:w-auto">
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 truncate">Live Monitoring</h2>
                <p class="text-xs text-slate-500 hidden md:block">WebRTC Realtime & Instant Playback.</p>
            </div>

            <div class="flex flex-wrap gap-2 items-center shrink-0 max-w-full w-full md:w-auto justify-start md:justify-end">
                <!-- Date Picker -->
                <div class="flex items-center bg-white rounded-lg border border-slate-200 px-3 py-1.5 shadow-sm gap-2 h-10 shrink-0">
                    <i class="fas fa-calendar-alt text-slate-400 text-xs pointer-events-none"></i>
                    <input type="date" x-model="selectedDate" @change="refreshTimeline()" 
                           class="border-none p-0 text-xs font-bold text-slate-700 focus:ring-0 bg-transparent h-full cursor-pointer w-24">
                </div>

                <!-- Grid Selectors -->
                <div class="bg-white p-1 rounded-lg border border-slate-200 flex shadow-sm h-10 shrink-0 items-center">
                    <button @click="setGrid(1)" :class="{'bg-cyan-100 text-cyan-700': gridSize===1}" class="p-1 rounded transition w-8 h-8 flex items-center justify-center hover:bg-slate-50"><i class="fas fa-square pointer-events-none"></i></button>
                    <button @click="setGrid(4)" :class="{'bg-cyan-100 text-cyan-700': gridSize===4}" class="p-1 rounded transition w-8 h-8 flex items-center justify-center hover:bg-slate-50"><i class="fas fa-th-large pointer-events-none"></i></button>
                    <button @click="setGrid(9)" :class="{'bg-cyan-100 text-cyan-700': gridSize===9}" class="p-1 rounded transition w-8 h-8 flex items-center justify-center hover:bg-slate-50"><i class="fas fa-th pointer-events-none"></i></button>
                </div>

                <!-- Preset Dropdown -->
                <div class="relative shrink-0">
                    <button @click="presetsOpen = !presetsOpen" 
                            class="px-3 py-1.5 h-10 rounded-lg bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition flex items-center gap-2 shadow-sm">
                        <i class="fas fa-bookmark text-cyan-500"></i>
                        <span>Preset</span>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                    </button>
                    
                    <div x-show="presetsOpen" @click.away="presetsOpen = false" x-cloak
                         class="absolute right-0 top-full mt-2 w-56 bg-white border border-slate-200 rounded-xl shadow-lg z-50 py-2 text-xs">
                        <div class="px-3 py-2 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <span class="font-bold text-slate-500 uppercase text-[9px] tracking-wider">Layout Preset</span>
                            <button @click="saveNewPreset()" class="text-cyan-600 hover:text-cyan-700 font-bold flex items-center gap-1">
                                <i class="fas fa-plus-circle"></i> Baru
                            </button>
                        </div>
                        
                        <div class="max-h-48 overflow-y-auto custom-scrollbar divide-y divide-slate-50">
                            <template x-for="(preset, index) in presets" :key="index">
                                <div class="flex items-center justify-between px-3 py-2 hover:bg-slate-50 transition">
                                    <button @click="loadPreset(preset); presetsOpen = false" 
                                            class="flex-1 text-left font-semibold text-slate-700 truncate mr-2" 
                                            :title="preset.name"
                                            x-text="preset.name">
                                    </button>
                                    <button @click="deletePreset(index)" class="text-slate-400 hover:text-red-500 p-1 transition" title="Hapus Preset">
                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                    </button>
                                </div>
                            </template>
                            <template x-if="presets.length === 0">
                                <div class="px-3 py-4 text-center text-slate-400 italic">
                                    Belum ada preset.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Timeline Toggle -->
                <button @click="showTimeline = !showTimeline" 
                        class="px-3 py-1.5 h-10 rounded-lg bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 transition flex items-center gap-2 shadow-sm shrink-0"
                        :class="{'bg-cyan-50 border-cyan-200 text-cyan-600': showTimeline}"
                        title="Toggle Timeline">
                    <i class="fas pointer-events-none" :class="showTimeline ? 'fa-chart-area' : 'fa-chart-bar'"></i>
                </button>

                <!-- Fullscreen / Kiosk -->
                <button @click="toggleFullscreen()" class="px-4 py-1.5 h-10 rounded-lg bg-slate-800 text-white text-xs font-bold hover:bg-slate-700 transition flex items-center gap-2 shadow select-none shrink-0">
                    <i class="fas pointer-events-none" :class="isFullscreen ? 'fa-compress' : 'fa-expand'"></i>
                    <span x-text="isFullscreen ? 'Exit' : 'Kiosk'" class="hidden sm:inline"></span>
                </button>

                <!-- Sidebar Toggle -->
                <button @click="showSidebar = !showSidebar" class="w-10 h-10 bg-white border rounded-lg shadow-sm hover:bg-slate-50 text-slate-600 flex items-center justify-center transition select-none shrink-0">
                    <i class="fas pointer-events-none" :class="showSidebar ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
                </button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row flex-1 gap-4 lg:gap-6 overflow-hidden min-h-0 relative">
            
            <div class="flex flex-col min-w-0 gap-3 lg:gap-4 z-10 min-h-0"
                 :class="isFullscreen ? 'flex-1 h-full' : 'flex-none lg:flex-1'">
                
                <div class="w-full lg:w-auto bg-slate-900 rounded-xl lg:rounded-2xl overflow-hidden shadow-md lg:shadow-lg border border-slate-700 relative transition-all duration-300"
                     :class="isFullscreen ? 'flex-1 h-full' : (gridSize === 1 ? 'aspect-video shrink-0 lg:flex-1 lg:shrink' : 'aspect-square shrink-0 lg:flex-1 lg:shrink')">
                    <div class="grid h-full w-full gap-0.5 bg-black"
                         :class="{ 'grid-cols-1 grid-rows-1': gridSize === 1, 'grid-cols-2 grid-rows-2': gridSize === 4, 'grid-cols-3 grid-rows-3': gridSize === 9 }">
                        
                        <template x-for="i in gridSize">
                            <div class="relative border border-slate-800 bg-black group overflow-hidden cursor-pointer"
                                 :id="'slot-container-' + i"
                                 :class="{'ring-2 ring-cyan-400 z-20': selectedSlot === i}"
                                 @click="selectSlot(i)"
                                 @dblclick="activeSlots[i] ? toggleSlotFullscreen(i) : null"
                                 @dragover.prevent @drop="handleDrop($event, i)"
                                 oncontextmenu="return false;"> 
                                
                                <div x-show="!activeSlots[i]" class="absolute inset-0 flex flex-col items-center justify-center text-slate-700 pointer-events-none">
                                    <i class="fas fa-plus text-3xl mb-2 opacity-20"></i>
                                    <span class="text-xs font-mono text-slate-600">Slot <span x-text="i"></span></span>
                                </div>

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
                                            
                                            <iframe 
                                                :id="'iframe-live-' + i"
                                                x-show="activeSlots[i].mode === 'live'"
                                                class="absolute w-full h-full object-contain border-none transition-transform duration-75 ease-out origin-center pointer-events-none"
                                                allow="autoplay; encrypted-media; fullscreen; picture-in-picture"
                                                playsinline
                                                allowfullscreen>
                                            </iframe>

                                            <video 
                                                :id="'video-playback-' + i"
                                                x-show="activeSlots[i].mode === 'playback'"
                                                class="absolute w-full h-full object-contain transition-transform duration-75 ease-out origin-center pointer-events-none" 
                                                controlsList="nodownload noremoteplayback" 
                                                oncontextmenu="return false;"
                                                playsinline
                                                @timeupdate="handleTimeUpdate(i)">
                                            </video>

                                            <div class="absolute bottom-2 right-2 px-2 py-1 rounded bg-black/60 backdrop-blur z-20 pointer-events-none transition-opacity duration-300"
                                                 x-show="activeSlots[i].zoom > 1">
                                                <span class="text-[10px] font-bold text-white font-mono" x-text="Math.round(activeSlots[i].zoom * 100) + '%'"></span>
                                            </div>

                                            <div class="absolute top-2 left-2 px-2 py-1 rounded bg-black/60 backdrop-blur flex items-center gap-2 z-20 pointer-events-auto">
                                                <div class="w-2 h-2 rounded-full" :class="activeSlots[i].mode === 'live' ? 'bg-red-500 animate-pulse' : 'bg-green-500'"></div>
                                                <span class="text-[10px] font-bold text-white uppercase" x-text="activeSlots[i].mode === 'live' ? 'LIVE' : 'REC'"></span>
                                                <span class="text-[10px] text-gray-300 border-l border-gray-600 pl-2 ml-1 truncate max-w-[100px]" x-text="activeSlots[i].name"></span>
                                                
                                                <button @click.stop="playLive(i)" 
                                                        x-show="activeSlots[i].mode === 'live'"
                                                        class="ml-1 bg-slate-700 hover:bg-cyan-600 text-white w-5 h-5 rounded flex items-center justify-center transition shadow-sm" 
                                                        title="Reconnect Stream">
                                                    <i class="fas fa-sync-alt text-[9px]"></i>
                                                </button>
                                            </div>

                                            <div class="absolute top-2 right-2 flex flex-col gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-30 pointer-events-auto">
                                                <button @click.stop="removeCamera(i)" class="w-6 h-6 bg-red-600/80 hover:bg-red-500 text-white rounded shadow-md flex items-center justify-center transition" title="Tutup Kamera"><i class="fas fa-times text-xs"></i></button>
                                                <button @click.stop="toggleSlotFullscreen(i)" class="w-6 h-6 bg-slate-800/80 hover:bg-cyan-500 text-white rounded shadow-md flex items-center justify-center transition" title="Fullscreen"><i class="fas fa-expand text-xs"></i></button>
                                            </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="h-auto min-h-[6rem] bg-white border border-slate-200 lg:border-slate-300 p-2.5 lg:p-3 flex flex-col shrink-0 z-30 transition-all rounded-xl shadow-md lg:shadow-lg relative"
                     x-show="selectedSlot && activeSlots[selectedSlot] && showTimeline"
                     x-transition>
                    
                    <div class="flex flex-col md:flex-row items-center justify-between mb-3 gap-3 md:gap-0 relative z-40 w-full">
                        
                        <div class="flex items-center justify-between md:justify-start gap-3 w-full md:w-1/3 order-1">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 min-w-0">
                                <span class="text-cyan-600 font-bold text-sm truncate max-w-[150px] sm:max-w-[200px]" x-text="activeSlots[selectedSlot]?.name"></span>
                                <div class="hidden sm:flex items-center gap-2">
                                    <span class="text-gray-300">|</span>
                                    <span class="text-slate-500 text-xs font-bold" x-text="selectedDate"></span>
                                </div>
                            </div>
                            <span class="text-white text-xs font-mono bg-slate-800 px-2 py-1 rounded border border-slate-600 shrink-0" x-text="timelineTimeDisplay"></span>
                        </div>
                        
                        <div class="flex items-center justify-center gap-4 w-full md:w-1/3 order-2" 
                             x-show="activeSlots[selectedSlot]?.mode === 'playback'"
                             x-transition>
                            
                            <div class="relative z-50 flex items-center gap-4 bg-slate-50 px-4 py-1.5 rounded-full border border-slate-200 shadow-sm">
                                
                                <button @click.stop.prevent="seek(-10)" class="text-slate-400 hover:text-cyan-600 transition transform hover:scale-110 active:scale-95 p-1" title="Mundur 10s">
                                    <i class="fas fa-undo text-sm pointer-events-none"></i>
                                </button>
                                
                                <button @click.stop.prevent="togglePlayback()" 
                                        class="text-cyan-600 hover:text-cyan-500 transition transform hover:scale-110 active:scale-95 w-10 h-10 flex items-center justify-center rounded-full hover:bg-cyan-50">
                                    <i class="fas text-2xl pointer-events-none" :class="isPlaying ? 'fa-pause' : 'fa-play'"></i>
                                </button>
                                
                                <button @click.stop.prevent="seek(10)" class="text-slate-400 hover:text-cyan-600 transition transform hover:scale-110 active:scale-95 p-1" title="Maju 10s">
                                    <i class="fas fa-redo text-sm pointer-events-none"></i>
                                </button>
                            </div>

                            <div class="flex items-center gap-2 relative z-50">
                                <div class="relative" x-data="{ speedOpen: false }" @click.outside="speedOpen = false">
                                    <button @click.stop.prevent="speedOpen = !speedOpen" 
                                            class="flex items-center justify-center w-8 h-8 rounded-full bg-white border border-slate-200 text-[10px] font-bold text-slate-500 hover:text-cyan-600 hover:border-cyan-300 transition active:scale-95 shadow-sm">
                                        <span x-text="playbackSpeed + 'x'" class="pointer-events-none"></span>
                                    </button>
                                    <div x-show="speedOpen" class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-12 bg-white border border-slate-200 rounded shadow-lg z-[100] py-0.5">
                                        <template x-for="speed in [1.0, 2.0, 3.0, 5.0]">
                                            <button @click.stop="setSpeed(speed); speedOpen = false" 
                                                    class="block w-full text-center py-1.5 text-[10px] font-bold hover:bg-cyan-50 hover:text-cyan-600 transition border-b border-slate-50 last:border-none"
                                                    :class="playbackSpeed == speed ? 'bg-cyan-100 text-cyan-700' : 'text-slate-600'"
                                                    x-text="speed + 'x'">
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <div class="relative" x-data="{ zoomOpen: false }" @click.outside="zoomOpen = false">
                                    <button @click.stop.prevent="zoomOpen = !zoomOpen" 
                                            class="flex items-center justify-center w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-500 hover:text-cyan-600 hover:border-cyan-300 transition active:scale-95 shadow-sm">
                                        <i class="fas fa-search-plus text-xs pointer-events-none"></i>
                                    </button>
                                    <div x-show="zoomOpen" class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-12 bg-white border border-slate-200 rounded shadow-lg z-[100] py-0.5">
                                        <template x-for="z in [1.0, 1.5, 2.0, 3.0]">
                                            <button @click.stop="setZoom(z); zoomOpen = false" 
                                                    class="block w-full text-center py-1.5 text-[10px] font-bold hover:bg-cyan-50 hover:text-cyan-600 transition"
                                                    :class="activeSlots[selectedSlot]?.zoom == z ? 'bg-cyan-100 text-cyan-700' : 'text-slate-600'"
                                                    x-text="z + 'x'">
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end w-full md:w-1/3 order-3">
                            <button @click="goLive(selectedSlot)" 
                                    :disabled="isToday && activeSlots[selectedSlot]?.mode === 'live'"
                                    :class="(isToday && activeSlots[selectedSlot]?.mode === 'live') ? 'bg-slate-100 text-slate-400 border border-slate-200 cursor-default' : 'bg-red-600 text-white hover:bg-red-500 shadow-md animate-pulse cursor-pointer border border-red-700'"
                                    class="px-4 py-1.5 rounded-lg font-bold text-xs flex items-center justify-center gap-2 transition w-full md:w-auto">
                                <i class="fas fa-broadcast-tower pointer-events-none"></i> 
                                <span x-text="isToday ? 'LIVE' : 'BACK TO TODAY'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="relative h-12 w-full select-none cursor-pointer group bg-slate-800 rounded border border-slate-600 z-10"
                            id="global-timeline"
                            @mousemove="handleTimelineHover($event)"
                            @mouseleave="hoverPercent = -100"
                            @click="handleTimelineClick($event)">
                        
                        <div class="absolute inset-0 top-4 bottom-0 bg-slate-200 rounded overflow-hidden">
                            <div class="absolute inset-0 flex pointer-events-none z-0">
                                <template x-for="h in 25">
                                    <div class="flex-1 border-l border-slate-400/50 h-full relative">
                                        <span class="absolute -top-4 left-0 text-[9px] text-slate-400 font-mono" x-text="(h-1).toString().padStart(2,'0')"></span>
                                    </div>
                                </template>
                            </div>
                            <!-- Block Rekaman (Hijau & Orange) -->
                            <template x-for="seg in currentTimelineData">
                                <div class="absolute top-0 bottom-0 z-10 cursor-pointer transition-all border-r border-black/10"
                                     :class="(seg.start + seg.duration) > (currentPlayheadPercent / 100 * 86400) 
                                             ? 'animate-pulse shadow-[0_0_10px_rgba(255,165,0,0.6)]' 
                                             : 'hover:opacity-80'"
                                     :style="{
                                         left: (seg.start / 86400 * 100) + '%', 
                                         width: 'calc(' + (seg.duration / 86400 * 100) + '% + 2px)',
                                         minWidth: '5px',
                                         background: seg.has_motion ? `linear-gradient(to right, #f97316 ${seg.motion_percentage}%, #22c55e ${seg.motion_percentage}%)` : '#22c55e'
                                     }"
                                     :title="(seg.has_motion ? '⚠️ GERAKAN (' + seg.motion_percentage + '%): ' : 'Rekaman: ') + seg.human_start"
                                     @click="playRecord(selectedSlot, seg.url, 0, seg.start)">
                                </div>
                            </template>
                        </div>
                        
                        <div class="absolute top-2 bottom-0 w-0.5 bg-red-600 z-20 pointer-events-none transition-all duration-75 ease-linear"
                                :style="'left: ' + currentPlayheadPercent + '%'">
                             <div class="w-2.5 h-2.5 -ml-1 bg-red-600 rounded-full -mt-1.5 shadow border border-white"></div>
                        </div>
                        
                        <div class="absolute top-0 transform -translate-x-1/2 -translate-y-full pb-1 z-50 pointer-events-none"
                                :style="'left: ' + hoverPercent + '%'"
                                x-show="hoverPercent > 0 && hoverPercent < 100">
                             <div class="bg-slate-800 text-white text-[10px] font-mono font-bold px-2 py-1 rounded shadow-lg flex flex-col items-center border border-slate-600">
                                <span x-text="hoverTimeDisplay"></span>
                                <div class="w-2 h-2 bg-slate-800 transform rotate-45 -mt-1"></div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-80 xl:w-96 bg-white border border-slate-200 rounded-2xl shadow-lg flex flex-col transition-all duration-300 z-30 min-h-0"
                    x-show="showSidebar && !isFullscreen"
                    :class="isFullscreen ? 'absolute top-0 right-0 h-full w-full lg:relative lg:w-80 xl:w-96' : 'flex-1 lg:flex-none lg:h-auto lg:shrink-0'">
                <div class="p-4 border-b border-slate-100 bg-white flex flex-col gap-3 rounded-t-2xl shrink-0">
                    <div class="flex justify-between items-center text-slate-700 text-sm font-bold">
                        <span class="flex items-center gap-2"><i class="fas fa-video text-cyan-500"></i> Camera List</span>
                        <span class="text-[10px] font-mono text-slate-500 bg-slate-100 px-2 py-1 rounded-full border border-slate-200 shadow-inner">{{ $cctvs->count() }} Unit</span>
                    </div>
                    
                    <div class="relative group">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 group-focus-within:text-cyan-500 transition-colors pointer-events-none text-xs"></i>
                        <input type="text" x-model="search" placeholder="Cari nama kamera..." 
                               class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 transition-all placeholder-slate-400 text-slate-700 shadow-sm">
                    </div>
                    
                    <div class="hidden sm:grid grid-cols-2 gap-2">
                        <!-- 1. NODE FILTER -->
                        <div class="relative group">
                            <i class="fas fa-server absolute left-2.5 top-1/2 transform -translate-y-1/2 text-slate-400 text-[10px] group-focus-within:text-cyan-500 transition-colors pointer-events-none"></i>
                            <select x-model="filterServer" class="w-full pl-7 pr-6 py-1.5 text-[10px] font-medium text-slate-600 rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 appearance-none transition-all cursor-pointer truncate shadow-sm">
                                <option value="">Semua Node</option>
                                @foreach($servers as $srv)
                                    <option value="{{ $srv->id }}">Node {{ $srv->id }}</option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down absolute right-2.5 top-1/2 transform -translate-y-1/2 text-slate-400 text-[9px] pointer-events-none"></i>
                        </div>

                        <!-- 2. PENEMPATAN FILTER -->
                        <div class="relative group">
                            <i class="fas fa-door-open absolute left-2.5 top-1/2 transform -translate-y-1/2 text-slate-400 text-[10px] group-focus-within:text-cyan-500 transition-colors pointer-events-none"></i>
                            <select x-model="filterPlacement" class="w-full pl-7 pr-6 py-1.5 text-[10px] font-medium text-slate-600 rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 appearance-none transition-all cursor-pointer truncate shadow-sm">
                                <option value="">Penempatan</option>
                                <option value="Indoor">Indoor</option>
                                <option value="Outdoor">Outdoor</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-2.5 top-1/2 transform -translate-y-1/2 text-slate-400 text-[9px] pointer-events-none"></i>
                        </div>

                        <!-- 3. FAKULTAS FILTER -->
                        <div class="relative group">
                            <i class="fas fa-layer-group absolute left-2.5 top-1/2 transform -translate-y-1/2 text-slate-400 text-[10px] group-focus-within:text-cyan-500 transition-colors pointer-events-none"></i>
                            <select x-model="filterFaculty" class="w-full pl-7 pr-6 py-1.5 text-[10px] font-medium text-slate-600 rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 appearance-none transition-all cursor-pointer truncate shadow-sm">
                                <option value="">Semua Fakultas</option>
                                @foreach($faculties as $fakultas)
                                    <option value="{{ $fakultas }}">{{ $fakultas }}</option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down absolute right-2.5 top-1/2 transform -translate-y-1/2 text-slate-400 text-[9px] pointer-events-none"></i>
                        </div>

                        <!-- 4. GEDUNG FILTER -->
                        <div class="relative group">
                            <i class="fas fa-building absolute left-2.5 top-1/2 transform -translate-y-1/2 text-slate-400 text-[10px] group-focus-within:text-cyan-500 transition-colors pointer-events-none"></i>
                            <select x-model="filterBuilding" class="w-full pl-7 pr-6 py-1.5 text-[10px] font-medium text-slate-600 rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 appearance-none transition-all cursor-pointer truncate shadow-sm">
                                <option value="">Semua Gedung</option>
                                @foreach($buildings as $b)
                                    <option value="{{ $b->nama_gedung }}">{{ $b->nama_gedung }}</option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down absolute right-2.5 top-1/2 transform -translate-y-1/2 text-slate-400 text-[9px] pointer-events-none"></i>
                        </div>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scrollbar">
                    @foreach($cctvs as $cctv)
                    <div class="bg-white p-2 rounded border hover:border-cyan-400 cursor-pointer flex items-center gap-3 shadow-sm hover:shadow-md transition select-none group"
                        x-show="
                            (search === '' || '{{ strtolower($cctv->nama_cctv) }}'.includes(search.toLowerCase()) || '{{ strtolower($cctv->building->nama_gedung ?? "") }}'.includes(search.toLowerCase())) &&
                            (filterFaculty === '' || '{{ $cctv->building->fakultas ?? '' }}' === filterFaculty) &&
                            (filterBuilding === '' || '{{ $cctv->building->nama_gedung ?? '' }}' === filterBuilding) &&
                            (filterPlacement === '' || '{{ $cctv->penempatan ?? '' }}' === filterPlacement) &&
                            (filterServer === '' || (filterServer === 'master' && '{{ $cctv->server_id }}' === '') || '{{ $cctv->server_id }}' === filterServer)
                        "
                        draggable="true" 
                        @dragstart="handleDragStart($event, {{ $cctv->id }}, '{{ $cctv->nama_cctv }}', '{{ $cctv->building->nama_gedung ?? '-' }}', '{{ $cctv->building->fakultas ?? '-' }}', '{{ $cctv->live_stream_url }}')"
                        @dragend="isDragging = false"
                        @click.stop="addCameraOnClick({ id: {{ $cctv->id }}, name: '{{ $cctv->nama_cctv }}', building: '{{ $cctv->building->nama_gedung ?? '-' }}', faculty: '{{ $cctv->building->fakultas ?? '-' }}', liveUrl: '{{ $cctv->live_stream_url }}' })">
                        
                        <div class="w-8 h-8 rounded flex items-center justify-center transition shrink-0
                            {{ $cctv->status === 'online' ? 'bg-emerald-50 text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white' : 'bg-red-50 text-red-500 group-hover:bg-red-500 group-hover:text-white' }}">
                            <i class="fas fa-video"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-700 truncate group-hover:text-cyan-600 transition flex items-center gap-1">
                                <span class="truncate">{{ $cctv->nama_cctv }}</span>
                                @if($cctv->server)
                                    <span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 text-[8px] font-mono border border-blue-100 shrink-0" title="IP: {{ $cctv->server->ip_address }}">Node {{ $cctv->server->id }}</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[8px] font-mono border border-slate-200 shrink-0">Master</span>
                                @endif
                            </p>
                            <p class="text-[9px] text-slate-500 truncate">{{ $cctv->building->nama_gedung ?? '-' }}</p>
                        </div>
                        <div class="ml-auto opacity-0 group-hover:opacity-100 transition shrink-0"><i class="fas fa-plus-circle text-cyan-400"></i></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- Modern Save Preset Modal -->
        <div x-show="showPresetModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[2000] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            
            <div @click.away="showPresetModal = false"
                 x-show="showPresetModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl border border-cyan-100 shadow-2xl max-w-md w-full overflow-hidden">
                
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-cyan-500/5 to-blue-500/5">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-bookmark text-cyan-500 text-lg"></i>
                        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Simpan Preset Layout</h3>
                    </div>
                    <button @click="showPresetModal = false" class="text-slate-400 hover:text-slate-600 transition p-1">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                
                <!-- Body -->
                <div class="p-6 space-y-4">
                    <p class="text-slate-500 text-xs font-medium">Layout aktif saat ini dengan ukuran grid <span class="font-bold text-cyan-600" x-text="gridSize + 'x' + gridSize"></span> akan disimpan ke dalam preset baru.</p>
                    
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Preset</label>
                        <input type="text" 
                               x-model="newPresetName" 
                               @keydown.enter="submitNewPreset()"
                               placeholder="Contoh: Gedung Rektorat, Lobby Utama..."
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition text-xs shadow-sm bg-slate-50 focus:bg-white"
                               focus>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button @click="showPresetModal = false" 
                            class="px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button @click="submitNewPreset()" 
                            class="px-5 py-2 bg-gradient-to-r from-cyan-500 to-blue-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-cyan-500/20 hover:shadow-cyan-500/30 transition active:scale-95">
                        Simpan Preset
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Delete Preset Modal -->
        <div x-show="showDeletePresetModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[2000] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            
            <div @click.away="showDeletePresetModal = false"
                 x-show="showDeletePresetModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl border border-red-100 shadow-2xl max-w-md w-full overflow-hidden">
                
                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-red-500/5 to-rose-500/5">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle text-red-500 text-lg"></i>
                        <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Hapus Preset Layout</h3>
                    </div>
                    <button @click="showDeletePresetModal = false" class="text-slate-400 hover:text-slate-600 transition p-1">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                
                <!-- Body -->
                <div class="p-6 space-y-3">
                    <p class="text-slate-700 text-xs font-semibold">Apakah Anda yakin ingin menghapus preset layout <span class="text-red-600 font-bold" x-text="'&ldquo;' + presetToDeleteName + '&rdquo;'"></span>?</p>
                    <p class="text-slate-400 text-[11px]">Tindakan ini permanen dan preset yang terhapus tidak dapat dipulihkan kembali.</p>
                </div>
                
                <!-- Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button @click="showDeletePresetModal = false" 
                            class="px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-100 text-xs font-semibold transition">
                        Batal
                    </button>
                    <button @click="confirmDeletePreset()" 
                            class="px-5 py-2 bg-gradient-to-r from-red-500 to-rose-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-red-500/20 hover:shadow-red-500/30 transition active:scale-95">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script>
        function hybridMonitoring() {
            return {
                gridSize: 1, activeSlots: {}, selectedSlot: null, showSidebar: true, showTimeline: true,
                search: '', filterFaculty: '', filterBuilding: '', filterServer: '', filterPlacement: '', currentHost: window.location.hostname, isFullscreen: false,
                isDragging: false, presetsOpen: false, showPresetModal: false, newPresetName: '',
                showDeletePresetModal: false, presetToDeleteIndex: null, presetToDeleteName: '',
                
                selectedDate: new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0') + '-' + String(new Date().getDate()).padStart(2, '0'),
                currentTimelineData: [], currentEventsData: [], currentPlayheadPercent: 100, hoverPercent: -100, hoverTimeDisplay: '00:00:00', timelineTimeDisplay: 'LIVE',
                
                // CONTROL STATE
                isPlaying: true,
                playbackSpeed: 1.0,
                
                // MONITORING STATE
                lastTime: 0,
                checkInterval: null,
                timelineInterval: null,
                
                // Panning State
                panning: false, panSlot: null, startX: 0, startY: 0,

                // Preset & Auto-save state
                presets: [],

                toggleSlotFullscreen(i) {
                    const el = document.getElementById('slot-container-' + i);
                    if (!el) return;
                    if (!document.fullscreenElement) {
                        if (el.requestFullscreen) {
                            el.requestFullscreen();
                        } else if (el.webkitRequestFullscreen) {
                            el.webkitRequestFullscreen();
                        } else if (el.msRequestFullscreen) {
                            el.msRequestFullscreen();
                        }
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        } else if (document.webkitExitFullscreen) {
                            document.webkitExitFullscreen();
                        } else if (document.msExitFullscreen) {
                            document.msExitFullscreen();
                        }
                    }
                },

                get isToday() {
                    const d = new Date();
                    const today = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                    return this.selectedDate === today;
                },

                init() {
                    document.addEventListener('fullscreenchange', () => { 
                        this.isFullscreen = !!document.fullscreenElement; 
                        if(!this.isFullscreen) { this.showSidebar = true; this.showTimeline = true; }
                        else { this.showTimeline = false; }
                    });
                    
                    let lastSystemDate = new Date().getFullYear() + '-' + String(new Date().getMonth() + 1).padStart(2, '0') + '-' + String(new Date().getDate()).padStart(2, '0');
                    
                    setInterval(() => {
                        const now = new Date();
                        const todayLocal = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
                        
                        // Hanya lakukan rollover jika mendeteksi pergantian hari sistem (tengah malam)
                        if (todayLocal !== lastSystemDate) {
                            // Jika user sebelumnya sedang melihat tanggal "hari kemarin" (yang baru saja berganti dari hari ini)
                            // dan slot terpilih saat ini sedang aktif LIVE stream, barulah geser otomatis ke hari baru.
                            if (this.selectedSlot && this.activeSlots[this.selectedSlot]?.mode === 'live' && this.selectedDate === lastSystemDate) {
                                this.selectedDate = todayLocal;
                                this.refreshTimeline();
                            }
                            lastSystemDate = todayLocal;
                        }

                        // Logic jam Live
                        if (this.selectedSlot && this.activeSlots[this.selectedSlot]?.mode === 'live' && this.isToday) {
                            const sec = (now.getHours()*3600) + (now.getMinutes()*60) + now.getSeconds();
                            this.currentPlayheadPercent = (sec / 86400) * 100;
                            this.timelineTimeDisplay = "LIVE CLOCK " + now.toLocaleTimeString('en-GB');
                        }
                    }, 1000);

                    // Fetch presets from database
                    fetch('{{ route("monitoring.presets") }}')
                        .then(res => res.json())
                        .then(data => {
                            this.presets = data;
                        })
                        .catch(e => console.error("Error fetching presets", e));

                    // Load last layout from localStorage
                    const savedGrid = localStorage.getItem('cctv_live_layout_grid_size');
                    if (savedGrid) {
                        this.gridSize = parseInt(savedGrid);
                    }
                    const savedSlots = localStorage.getItem('cctv_live_layout_active_slots');
                    if (savedSlots) {
                        try {
                            this.activeSlots = JSON.parse(savedSlots);
                            this.$nextTick(() => {
                                let firstActiveSlot = null;
                                for (let i = 1; i <= this.gridSize; i++) {
                                    if (this.activeSlots[i]) {
                                        this.playLive(i);
                                        if (firstActiveSlot === null) {
                                            firstActiveSlot = i;
                                        }
                                    }
                                }
                                if (firstActiveSlot !== null) {
                                    this.selectSlot(firstActiveSlot);
                                }
                            });
                        } catch (e) {
                            console.error("Error restoring slots:", e);
                        }
                    }
                },

                // --- SMART MONITOR (TOLERAN TERHADAP BUFFERING) ---
                startPlaybackMonitor(vid) {
                    if (this.checkInterval) {
                        clearInterval(this.checkInterval);
                        this.checkInterval = null;
                    }
                    this.lastTime = vid.currentTime;
                    let stuckCounter = 0;
                    
                    this.checkInterval = setInterval(() => {
                        if (!vid || vid.paused) return;

                        // Jika browser memberi sinyal buffering (readyState < 3), kita tunggu
                        if (vid.readyState < 3) return; 

                        const currentTime = vid.currentTime;
                        if (currentTime === this.lastTime && this.isPlaying) {
                            stuckCounter++;
                            // Jika data ada tapi waktu tidak jalan selama 3x cek (1.5 detik)
                            if (stuckCounter >= 3) {
                                console.log("Anti-Stuck: Jump starting...");
                                vid.currentTime += 0.1; // Geser dikit
                                vid.play().catch(e => {});
                                stuckCounter = 0; 
                            }
                        } else {
                            stuckCounter = 0;
                            this.lastTime = currentTime;
                        }
                    }, 500);
                },

                handleTimeUpdate(index) {
                    if (this.selectedSlot !== index) return;
                    const slot = this.activeSlots[index];
                    const vid = document.getElementById('video-playback-' + index);
                    
                    if (slot && vid && !isNaN(vid.currentTime) && slot.recordStartOffset) {
                        const currentSec = parseFloat(slot.recordStartOffset) + vid.currentTime;
                        this.currentPlayheadPercent = (currentSec / 86400) * 100;
                        this.timelineTimeDisplay = this.formatTime(currentSec);
                    }
                },

                refreshTimeline() {
                    if(this.selectedSlot) {
                        this.selectSlot(this.selectedSlot);
                    }
                },

                selectSlot(index) {
                    this.selectedSlot = index;
                    this.syncControls(); 

                    // Hapus interval lama jika ada
                    if(this.timelineInterval) clearInterval(this.timelineInterval);

                    const slot = this.activeSlots[index];
                    if(slot) {
                        const refreshTimelineData = () => {
                            const antiCache = new Date().getTime();
                            fetch(`/monitoring/timeline/${slot.id}?date=${this.selectedDate}&_=${antiCache}`)
                                .then(res => res.json())
                                .then(data => { 
                                    // Update data tanpa mengganggu playhead
                                    this.currentTimelineData = data.segments || []; 
                                    this.currentEventsData = data.events || [];
                                    console.log(`📊 Timeline Updated: ${this.currentTimelineData.length} Rekaman, ${this.currentEventsData.length} Kejadian.`);
                                })
                                .catch(e => {});
                        };

                        // Ambil data pertama kali
                        refreshTimelineData();

                        // Set auto-refresh setiap 60 detik
                        this.timelineInterval = setInterval(refreshTimelineData, 60000);
                    } else {
                        this.currentTimelineData = [];
                    }
                },

                assignCameraToSlot(cam, index) {
                    this.activeSlots[index] = { 
                        id: cam.id, name: cam.name, building: cam.building, faculty: cam.faculty, 
                        mode: 'live', liveUrl: cam.liveUrl,
                        zoom: 1, x: 0, y: 0     
                    }; 
                    this.selectSlot(index);
                    this.saveCurrentLayoutToLocalStorage();
                    this.$nextTick(() => { this.playLive(index); });
                    
                    fetch(`/log/cctv-view/${cam.id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    }).catch(e => console.error("Error logging camera view", e));
                },

                addCameraOnClick(cam) {
                    let targetSlot = -1;
                    for (let i = 1; i <= this.gridSize; i++) {
                        if (!this.activeSlots[i]) { targetSlot = i; break; }
                    }
                    if (targetSlot === -1 && this.selectedSlot) targetSlot = this.selectedSlot;
                    else if (targetSlot === -1) targetSlot = 1;
                    this.assignCameraToSlot(cam, targetSlot);
                },

                syncControls() {
                    if(!this.selectedSlot) return;
                    const vid = document.getElementById('video-playback-' + this.selectedSlot);
                    if(vid) {
                        this.isPlaying = !vid.paused;
                        this.playbackSpeed = vid.playbackRate;
                    } else {
                        this.isPlaying = false;
                        this.playbackSpeed = 1.0;
                    }
                },

                togglePlayback() {
                    if(!this.selectedSlot) return;
                    const vid = document.getElementById('video-playback-' + this.selectedSlot);
                    if(vid) {
                        if(vid.paused) {
                            vid.play().then(() => { this.isPlaying = true; }).catch(e => {});
                        } else {
                            vid.pause();
                            this.isPlaying = false;
                        }
                    }
                },

                seek(seconds) {
                    if(!this.selectedSlot) return;
                    const vid = document.getElementById('video-playback-' + this.selectedSlot);
                    if(vid) vid.currentTime += seconds;
                },

                setSpeed(speed) {
                    this.playbackSpeed = speed;
                    if(!this.selectedSlot) return;
                    const vid = document.getElementById('video-playback-' + this.selectedSlot);
                    if(vid) {
                        vid.playbackRate = parseFloat(speed);
                        vid.defaultPlaybackRate = parseFloat(speed);
                    }
                },

                setZoom(zoom) {
                    if(!this.selectedSlot) return;
                    this.activeSlots[this.selectedSlot].zoom = zoom;
                    if(zoom === 1) {
                        this.activeSlots[this.selectedSlot].x = 0;
                        this.activeSlots[this.selectedSlot].y = 0;
                    }
                    this.applyTransform(this.selectedSlot);
                },

                applyTransform(index) {
                    const slot = this.activeSlots[index];
                    if(!slot) return;
                    const transform = `translate(${slot.x || 0}px, ${slot.y || 0}px) scale(${slot.zoom || 1})`;
                    
                    const iframe = document.getElementById('iframe-live-' + index);
                    if(iframe) iframe.style.transform = transform;
                    
                    const video = document.getElementById('video-playback-' + index);
                    if(video) video.style.transform = transform;
                },

                handleWheel(e, index) {
                    if (!this.activeSlots[index]) return;
                    let slot = this.activeSlots[index];
                    let currentZoom = slot.zoom || 1;
                    if (e.deltaY < 0) currentZoom += 0.1;
                    else currentZoom -= 0.1;
                    currentZoom = Math.min(Math.max(currentZoom, 1), 5);
                    slot.zoom = currentZoom;
                    if(currentZoom === 1) { slot.x = 0; slot.y = 0; }
                    this.applyTransform(index);
                },

                startPan(e, index) {
                    let slot = this.activeSlots[index];
                    if(!slot || (slot.zoom || 1) <= 1) return;
                    this.panning = true;
                    this.panSlot = index;
                    this.startX = e.clientX - (slot.x || 0);
                    this.startY = e.clientY - (slot.y || 0);
                },
                
                doPan(e, index) {
                    if(!this.panning || this.panSlot !== index) return;
                    let slot = this.activeSlots[index];
                    slot.x = e.clientX - this.startX;
                    slot.y = e.clientY - this.startY;
                    this.applyTransform(index);
                },
                
                endPan(index) {
                    this.panning = false;
                    this.panSlot = null;
                },

                handleVideoEnded(index) {
                    if (index !== this.selectedSlot) return;
                    const vid = document.getElementById('video-playback-' + index);
                    if(!vid) return;
                    
                    if(this.checkInterval) clearInterval(this.checkInterval);

                    const currentSrc = decodeURIComponent(vid.src);
                    const idx = this.currentTimelineData.findIndex(seg => currentSrc.includes(encodeURI(seg.url)) || currentSrc.includes(seg.url));

                    if (idx !== -1 && idx < this.currentTimelineData.length - 1) {
                        const nextSeg = this.currentTimelineData[idx + 1];
                        console.log("Auto-playing next part:", nextSeg.human_start);
                        this.playRecord(index, nextSeg.url, 0, nextSeg.start);
                    } else {
                        this.isPlaying = false;
                        console.log("End of playback list.");
                    }
                },

                playLive(index) {
                    const slot = this.activeSlots[index]; 
                    if(!slot || !slot.id) return;
                    
                    if(!this.isToday) { 
                        const now = new Date();
                        this.selectedDate = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
                        this.refreshTimeline(); 
                    }

                    slot.mode = 'live';
                    slot.zoom = 1; slot.x = 0; slot.y = 0;
                    this.applyTransform(index);

                    const videoEl = document.getElementById('video-playback-' + index);
                    if(videoEl) {
                        videoEl.pause();
                        videoEl.removeAttribute('src'); // Stop background buffering of MP4
                        videoEl.load();
                    }
                    
                    if(this.checkInterval) clearInterval(this.checkInterval);

                    const iframe = document.getElementById('iframe-live-' + index);
                    if(iframe) {
                        iframe.src = 'about:blank';
                        setTimeout(() => { iframe.src = slot.liveUrl; }, 50);
                    }
                },

                // --- CORE RECORD (STREAMING STANDAR + MONITOR) ---
                playRecord(index, fileUrl, offsetSeconds, startTs) {
                    const slot = this.activeSlots[index];
                    slot.mode = 'playback';
                    slot.recordStartOffset = parseFloat(startTs);
                    slot.zoom = 1; slot.x = 0; slot.y = 0;
                    this.applyTransform(index);
                    
                    const iframe = document.getElementById('iframe-live-' + index);
                    if(iframe) iframe.src = 'about:blank';

                    this.$nextTick(() => {
                        const vid = document.getElementById('video-playback-' + index);
                        if(vid) {
                            if(this.checkInterval) clearInterval(this.checkInterval);
                            
                            // Setup Handler
                            vid.onplay = () => { this.isPlaying = true; };
                            vid.onpause = () => { this.isPlaying = false; };
                            vid.onended = () => { this.handleVideoEnded(index); };
                            vid.onerror = (e) => { console.error("Video Error:", e); };

                            // Mode Streaming Biasa
                            vid.src = fileUrl;
                            vid.currentTime = offsetSeconds;
                            vid.playbackRate = parseFloat(this.playbackSpeed);
                            
                            vid.play().catch(e => console.log("Auto-play prevented:", e));
                            
                            // Start Smart Monitor (Anti-Stuck)
                            this.startPlaybackMonitor(vid);
                        }
                    });
                },

                handleTimelineClick(e) {
                    if(!this.selectedSlot) return;
                    const rect = document.getElementById('global-timeline').getBoundingClientRect();
                    const percent = ((e.clientX - rect.left) / rect.width) * 100;
                    const secondsInDay = 86400; const clickedSeconds = (percent / 100) * secondsInDay;
                    const now = new Date(); const nowSeconds = (now.getHours()*3600) + (now.getMinutes()*60) + now.getSeconds();
                    
                    if (this.isToday && clickedSeconds >= (nowSeconds - 60)) { this.goLive(this.selectedSlot); return; }
                    
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
                
                formatTime(seconds) { const h = Math.floor(seconds / 3600).toString().padStart(2,'0'); const m = Math.floor((seconds % 3600) / 60).toString().padStart(2,'0'); const s = Math.floor(seconds % 60).toString().padStart(2,'0'); return `${h}:${m}:${s}`; },
                goLive(index) { this.playLive(index); }, 
                setGrid(n) { 
                    this.gridSize = n; 
                    if(this.selectedSlot > n) this.selectedSlot = null; 
                    this.saveCurrentLayoutToLocalStorage();
                }, 
                removeCamera(i) { 
                    delete this.activeSlots[i]; 
                    if(this.selectedSlot === i) { 
                        this.selectedSlot = null; 
                        this.currentTimelineData = []; 
                    } 
                    this.saveCurrentLayoutToLocalStorage();
                },
                
                // LocalStorage Helper for current layout
                saveCurrentLayoutToLocalStorage() {
                    localStorage.setItem('cctv_live_layout_grid_size', this.gridSize);
                    localStorage.setItem('cctv_live_layout_active_slots', JSON.stringify(this.activeSlots));
                },

                // Preset Management Methods
                saveNewPreset() {
                    this.newPresetName = '';
                    this.presetsOpen = false;
                    this.showPresetModal = true;
                },

                submitNewPreset() {
                    const name = this.newPresetName.trim();
                    if (!name) {
                        alert("Nama preset tidak boleh kosong.");
                        return;
                    }

                    const payload = {
                        name: name,
                        grid_size: this.gridSize,
                        slots: this.activeSlots
                    };

                    fetch('{{ route("monitoring.presets.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => {
                        if (!res.ok) throw new Error("Gagal menyimpan preset");
                        return res.json();
                    })
                    .then(preset => {
                        this.presets.push(preset);
                        this.presets.sort((a, b) => a.name.localeCompare(b.name));
                        this.showPresetModal = false;
                        this.newPresetName = '';
                    })
                    .catch(e => {
                        console.error(e);
                        alert("Gagal menyimpan preset.");
                    });
                },

                loadPreset(preset) {
                    this.gridSize = preset.grid_size || preset.gridSize;
                    
                    // Clear older streams
                    for (let i = 1; i <= 9; i++) {
                        const vid = document.getElementById('video-playback-' + i);
                        if (vid) {
                            vid.pause();
                            vid.removeAttribute('src');
                            vid.load();
                        }
                        const iframe = document.getElementById('iframe-live-' + i);
                        if (iframe) iframe.src = 'about:blank';
                    }

                    this.activeSlots = JSON.parse(JSON.stringify(preset.slots));
                    this.saveCurrentLayoutToLocalStorage();

                    this.$nextTick(() => {
                        for (let i = 1; i <= this.gridSize; i++) {
                            if (this.activeSlots[i]) {
                                this.playLive(i);
                            }
                        }
                        if (this.selectedSlot > this.gridSize) {
                            this.selectedSlot = null;
                        }
                    });
                },

                deletePreset(index) {
                    const preset = this.presets[index];
                    if (!preset) return;
                    this.presetToDeleteIndex = index;
                    this.presetToDeleteName = preset.name;
                    this.presetsOpen = false;
                    this.showDeletePresetModal = true;
                },

                confirmDeletePreset() {
                    const index = this.presetToDeleteIndex;
                    const preset = this.presets[index];
                    if (!preset) return;

                    fetch(`/monitoring/presets/${preset.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error("Gagal menghapus preset");
                        this.presets.splice(index, 1);
                        this.showDeletePresetModal = false;
                        this.presetToDeleteIndex = null;
                        this.presetToDeleteName = '';
                    })
                    .catch(e => {
                        console.error(e);
                        alert("Gagal menghapus preset.");
                    });
                },

                handleDragStart(e, id, name, building, faculty, liveUrl) { 
                    this.isDragging = true;
                    const camData = JSON.stringify({ id, name, building, faculty, liveUrl }); 
                    e.dataTransfer.setData('application/json', camData);
                    e.dataTransfer.effectAllowed = 'copy';
                },
                handleDrop(e, index) { 
                    this.isDragging = false;
                    const data = e.dataTransfer.getData('application/json'); 
                    if (!data) return; 
                    const cam = JSON.parse(data); 
                    this.assignCameraToSlot(cam, index);
                }
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