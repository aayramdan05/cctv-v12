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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/csp@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc; /* slate-50 */
            color: #1e293b; /* slate-800 */
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
        /* Custom styled Range slider for light theme speed controls */
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #0891b2; /* cyan-600 */
            cursor: pointer;
            border: 2px solid #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        }
        input[type="range"]::-moz-range-thumb {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #0891b2;
            cursor: pointer;
            border: 2px solid #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body class="h-full flex flex-col overflow-hidden bg-slate-50" x-data="mobileMonitoring()">

    <!-- App Header -->
    <header class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-4 sticky top-0 z-50 shrink-0 select-none shadow-sm">
        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200/80 flex items-center justify-center text-slate-600 hover:text-slate-900 transition active:scale-95">
                <i class="fas fa-chevron-left text-xs"></i>
            </a>
            <div class="flex flex-col">
                <h1 class="text-slate-800 font-bold text-xs tracking-tight uppercase">CCTV UNPAD</h1>
                <span class="text-[8px] font-mono text-cyan-600 uppercase tracking-widest font-bold">Mobile Stream</span>
            </div>
        </div>
        
        <!-- Live Node Status Pill -->
        <div class="flex items-center gap-1.5 bg-slate-100 border border-slate-200/80 px-2.5 py-1 rounded-xl text-[9px] text-slate-600 font-bold font-mono shadow-inner">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span x-text="getActiveSlotsCount() + ' / ' + gridSize + ' Active'"></span>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 flex flex-col overflow-hidden min-h-0">
        
        <!-- Video Player Grid Panel -->
        <div class="w-full bg-slate-950 aspect-video relative flex flex-col justify-center shrink-0 border-b border-slate-250 shadow-md z-20">
            <div class="grid h-full w-full gap-0.5 bg-slate-900"
                 :class="{ 'grid-cols-1 grid-rows-1': gridSize === 1, 'grid-cols-2 grid-rows-2': gridSize === 4 }">
                 
                 <!-- Grid Slots -->
                 <template x-for="i in gridSize" :key="i">
                      <div class="relative border overflow-hidden transition-all duration-200"
                           :class="selectedSlot === i ? 'border-cyan-500 shadow-[inset_0_0_12px_rgba(6,182,212,0.4)] z-10' : 'border-slate-850 bg-slate-950'"
                           @click="selectSlot(i)">
                           
                           <!-- Placeholder if slot is empty -->
                           <div x-show="!activeSlots[i]" 
                                class="absolute inset-0 flex flex-col items-center justify-center text-slate-500 pointer-events-none select-none"
                                style="background-image: url('/offline-placeholder.png'); background-size: cover; background-position: center; background-blend-mode: overlay; background-color: rgba(15,23,42,0.85);">
                               <i class="fas fa-video-slash text-xl mb-1.5 text-slate-500/80"></i>
                               <span class="text-[9px] font-mono text-slate-400 uppercase tracking-widest font-bold">Slot <span x-text="i"></span> - Empty</span>
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
                                   <div class="absolute top-2 left-2 px-2 py-0.5 rounded-lg bg-black/80 backdrop-blur-sm flex items-center gap-1.5 z-10 pointer-events-none shadow-md border border-slate-800">
                                       <div class="w-1.5 h-1.5 rounded-full" :class="activeSlots[i].mode === 'live' ? 'bg-red-500 animate-pulse' : 'bg-green-500'"></div>
                                       <span class="text-[7px] font-mono font-bold text-white uppercase" x-text="activeSlots[i].mode === 'live' ? 'LIVE' : 'PLAYBACK'"></span>
                                       <span class="text-[8px] font-bold text-slate-200 truncate max-w-[90px] border-l border-slate-800 pl-1.5 ml-1" x-text="activeSlots[i].name"></span>
                                   </div>
                                   
                                   <!-- Remove Button -->
                                   <button @click.stop="removeCamera(i)" 
                                           class="absolute top-2 right-2 w-5.5 h-5.5 bg-red-600/80 active:bg-red-500 text-white rounded-full flex items-center justify-center transition-all z-20 shadow-lg active:scale-90">
                                       <i class="fas fa-times text-[9px]"></i>
                                   </button>
                               </div>
                           </template>
                      </div>
                 </template>
            </div>
        </div>

        <!-- Controls Action Strip (Native Light CCTV Toolbar) -->
        <div class="bg-white border-b border-slate-200 px-4 py-2.5 flex items-center justify-between shrink-0 select-none z-20 shadow-sm">
            
            <!-- Grouped circular buttons layout (Hik-Connect / DMSS inspired light toolbar) -->
            <div class="flex items-center justify-between w-full gap-2">
                <!-- Play/Pause -->
                <button @click="togglePlayback()" 
                        :disabled="!activeSlots[selectedSlot] || activeSlots[selectedSlot].mode !== 'playback'"
                        class="w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:text-slate-800 transition bg-slate-50 hover:bg-slate-100 border border-slate-200 active:scale-95 disabled:opacity-40 disabled:pointer-events-none"
                        :class="isPlaying ? 'text-cyan-600 border-cyan-300 bg-cyan-50' : ''">
                    <i class="fas text-xs pointer-events-none" :class="isPlaying ? 'fa-pause' : 'fa-play'"></i>
                </button>

                <!-- Audio Toggle -->
                <button @click="toggleMute()" 
                        :disabled="!activeSlots[selectedSlot]"
                        class="w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:text-slate-800 transition bg-slate-50 hover:bg-slate-100 border border-slate-200 active:scale-95 disabled:opacity-40"
                        :class="isMuted ? 'text-amber-600 border-amber-300 bg-amber-50' : 'text-cyan-600 border-cyan-350 bg-cyan-50/50'">
                    <i class="fas text-xs pointer-events-none" :class="isMuted ? 'fa-volume-mute' : 'fa-volume-up'"></i>
                </button>

                <!-- Snapshot -->
                <button @click="takeSnapshot()" 
                        :disabled="!activeSlots[selectedSlot]"
                        class="w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:text-slate-800 transition bg-slate-50 hover:bg-slate-100 border border-slate-200 active:scale-95 disabled:opacity-40">
                    <i class="fas fa-camera text-xs pointer-events-none"></i>
                </button>

                <!-- PTZ Toggle -->
                <button @click="showPtz = !showPtz; if(showPtz) showTimeline=false" 
                        :disabled="!activeSlots[selectedSlot]"
                        class="w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:text-slate-800 transition bg-slate-50 hover:bg-slate-100 border border-slate-200 active:scale-95 disabled:opacity-40"
                        :class="showPtz ? 'text-cyan-600 border-cyan-300 bg-cyan-50 shadow-sm' : ''">
                    <i class="fas fa-gamepad text-xs pointer-events-none"></i>
                </button>

                <!-- Timeline / Playback Toggle -->
                <button @click="showTimeline = !showTimeline; if(showTimeline) showPtz=false" 
                        :disabled="!activeSlots[selectedSlot]"
                        class="w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:text-slate-800 transition bg-slate-50 hover:bg-slate-100 border border-slate-200 active:scale-95 disabled:opacity-40"
                        :class="showTimeline ? 'text-cyan-600 border-cyan-300 bg-cyan-50 shadow-sm' : ''">
                    <i class="fas fa-clock-history text-xs pointer-events-none"></i>
                </button>

                <!-- Grid Split Layout 1x1 / 2x2 Toggle -->
                <div class="flex items-center gap-0.5 bg-slate-100 p-0.5 rounded-full border border-slate-200/80">
                    <button @click="setGrid(1)" 
                            class="w-8 h-8 rounded-full flex items-center justify-center font-bold transition-all text-xs"
                            :class="gridSize === 1 ? 'bg-white text-cyan-600 border border-slate-200/60 shadow-sm' : 'text-slate-400'">
                        1
                    </button>
                    <button @click="setGrid(4)" 
                            class="w-8 h-8 rounded-full flex items-center justify-center font-bold transition-all text-xs"
                            :class="gridSize === 4 ? 'bg-white text-cyan-600 border border-slate-200/60 shadow-sm' : 'text-slate-400'">
                        4
                    </button>
                </div>
            </div>
        </div>

        <!-- Interactive Light PTZ Panel -->
        <div class="bg-white border-b border-slate-200 flex items-center justify-center gap-8 py-4 shrink-0 select-none transition-all duration-200" 
             x-show="showPtz && activeSlots[selectedSlot]"
             x-transition>
             
             <!-- D-Pad Direction Circle -->
             <div class="relative w-32 h-32 bg-slate-50 rounded-full border border-slate-200 flex items-center justify-center shadow-inner">
                 <!-- Up -->
                 <button @mousedown="ptzStart('up')" @mouseup="ptzEnd()" @mouseleave="ptzEnd()"
                         @touchstart.prevent="ptzStart('up')" @touchend.prevent="ptzEnd()"
                         :class="activePtzDir === 'up' ? 'text-cyan-600 bg-white border-cyan-300 shadow-sm' : 'text-slate-400 border-transparent'"
                         class="absolute top-1 w-9 h-9 rounded-full flex items-center justify-center border transition-all active:scale-90">
                     <i class="fas fa-chevron-up text-sm"></i>
                 </button>
                 <!-- Down -->
                 <button @mousedown="ptzStart('down')" @mouseup="ptzEnd()" @mouseleave="ptzEnd()"
                         @touchstart.prevent="ptzStart('down')" @touchend.prevent="ptzEnd()"
                         :class="activePtzDir === 'down' ? 'text-cyan-600 bg-white border-cyan-300 shadow-sm' : 'text-slate-400 border-transparent'"
                         class="absolute bottom-1 w-9 h-9 rounded-full flex items-center justify-center border transition-all active:scale-90">
                     <i class="fas fa-chevron-down text-sm"></i>
                 </button>
                 <!-- Left -->
                 <button @mousedown="ptzStart('left')" @mouseup="ptzEnd()" @mouseleave="ptzEnd()"
                         @touchstart.prevent="ptzStart('left')" @touchend.prevent="ptzEnd()"
                         :class="activePtzDir === 'left' ? 'text-cyan-600 bg-white border-cyan-300 shadow-sm' : 'text-slate-400 border-transparent'"
                         class="absolute left-1 w-9 h-9 rounded-full flex items-center justify-center border transition-all active:scale-90">
                     <i class="fas fa-chevron-left text-sm"></i>
                 </button>
                 <!-- Right -->
                 <button @mousedown="ptzStart('right')" @mouseup="ptzEnd()" @mouseleave="ptzEnd()"
                         @touchstart.prevent="ptzStart('right')" @touchend.prevent="ptzEnd()"
                         :class="activePtzDir === 'right' ? 'text-cyan-600 bg-white border-cyan-300 shadow-sm' : 'text-slate-400 border-transparent'"
                         class="absolute right-1 w-9 h-9 rounded-full flex items-center justify-center border transition-all active:scale-90">
                     <i class="fas fa-chevron-right text-sm"></i>
                 </button>
                 
                 <!-- Center Knob -->
                 <div class="w-12 h-12 rounded-full bg-white border border-slate-200/80 flex items-center justify-center shadow-md">
                     <div class="w-3.5 h-3.5 rounded-full bg-slate-300 border border-slate-200"></div>
                 </div>
             </div>
             
             <!-- Zoom & Speed Panel -->
             <div class="flex flex-col gap-3 min-w-[90px]">
                 <div class="flex gap-2">
                     <button @click="ptzZoom('in')" class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-600 hover:text-slate-900 active:text-cyan-600 active:scale-95 transition-all shadow-sm">
                         <i class="fas fa-plus text-xs"></i>
                     </button>
                     <button @click="ptzZoom('out')" class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-600 hover:text-slate-900 active:text-cyan-600 active:scale-95 transition-all shadow-sm">
                         <i class="fas fa-minus text-xs"></i>
                     </button>
                 </div>
                 
                 <!-- Speed Slider -->
                 <div class="flex flex-col gap-1 px-1">
                     <div class="flex justify-between items-center text-[7px] font-bold text-slate-400 uppercase tracking-widest">
                         <span>Speed</span>
                         <span class="text-cyan-600 font-bold" x-text="ptzSpeed"></span>
                     </div>
                     <input type="range" min="1" max="10" x-model="ptzSpeed" 
                            class="w-full h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-cyan-600 focus:outline-none">
                 </div>
             </div>
        </div>

        <!-- Playback Timeline Controller Panel (Light) -->
        <div x-show="showTimeline && activeSlots[selectedSlot]" 
             x-transition
             class="bg-white border-b border-slate-200 p-3.5 space-y-3.5 shrink-0 select-none">
             
             <!-- Header timeline (Date selector & Playhead display) -->
             <div class="flex justify-between items-center text-xs">
                 <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-xl shadow-inner">
                     <i class="fas fa-calendar-alt text-slate-400 text-[10px]"></i>
                     <input type="date" x-model="selectedDate" @change="fetchTimelineData()" 
                            class="border-none p-0 text-[10px] font-bold text-slate-700 focus:ring-0 bg-transparent h-auto cursor-pointer w-24">
                 </div>
                 <div class="flex items-center gap-1.5">
                     <button @click="goLive()" class="px-2.5 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 border border-red-200 text-[8px] text-red-600 font-bold uppercase tracking-wider active:scale-95 transition-all" x-show="activeSlots[selectedSlot]?.mode === 'playback'">
                         Go Live
                     </button>
                     <span class="text-cyan-600 font-mono font-bold text-[11px] bg-cyan-50 px-2.5 py-1.5 rounded-xl border border-cyan-200 shadow-sm" x-text="timelineTimeDisplay"></span>
                 </div>
             </div>
             
             <!-- Playback controller seek controls -->
             <div class="flex items-center justify-center gap-6" x-show="activeSlots[selectedSlot]?.mode === 'playback'">
                 <button @click="seek(-10)" class="w-9 h-9 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-500 active:bg-slate-100 active:scale-90 transition shadow-sm">
                     <i class="fas fa-undo text-xs pointer-events-none"></i>
                 </button>
                 <button @click="togglePlayback()" class="w-10 h-10 rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 flex items-center justify-center text-white active:scale-90 transition shadow-lg shadow-cyan-500/30">
                     <i class="fas text-xs pointer-events-none" :class="isPlaying ? 'fa-pause' : 'fa-play'"></i>
                 </button>
                 <button @click="seek(10)" class="w-9 h-9 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-500 active:bg-slate-100 active:scale-90 transition shadow-sm">
                     <i class="fas fa-redo text-xs pointer-events-none"></i>
                 </button>
             </div>
             
             <!-- Horizontal timeline list segments -->
             <div class="space-y-1.5">
                 <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Available Recordings</span>
                 <div class="flex gap-2.5 overflow-x-auto pb-1.5 custom-scrollbar">
                     <template x-for="seg in currentTimelineData" :key="seg.start">
                         <button @click="playRecord(selectedSlot, seg.url, 0, seg.start)"
                                 class="px-3.5 py-2.5 rounded-2xl border bg-slate-50 text-left shrink-0 transition-all flex items-center gap-3 active:scale-95 shadow-sm"
                                 :class="activeSlots[selectedSlot]?.recordStartOffset === seg.start && activeSlots[selectedSlot]?.mode === 'playback' ? 'border-cyan-500 bg-cyan-50/55' : 'border-slate-200'">
                             <div class="w-2 h-2 rounded-full" :class="seg.has_motion ? 'bg-orange-500 shadow-[0_0_8px_rgba(249,115,22,0.4)]' : 'bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.4)]'"></div>
                             <div>
                                 <p class="text-[10px] font-bold text-slate-700" x-text="seg.human_start"></p>
                                 <p class="text-[8px] font-medium text-slate-400" x-text="seg.size_mb + ' MB'"></p>
                             </div>
                         </button>
                     </template>
                     <template x-if="currentTimelineData.length === 0">
                         <p class="text-[10px] text-slate-400 italic py-2 pl-1">No recorded file found for this date.</p>
                     </template>
                 </div>
             </div>
        </div>

        <!-- Camera Directory Listing (Sliding Light sheet) -->
        <div class="flex-1 overflow-hidden flex flex-col bg-slate-100/50 relative">
            <!-- Directory Filter Bar -->
            <div class="p-3.5 border-b border-slate-200 flex flex-col gap-2.5 bg-white select-none z-10 shadow-sm">
                <!-- Search input -->
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="search" placeholder="Cari nama kamera..." 
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all placeholder-slate-400 text-slate-700">
                </div>
                
                <!-- Filters row -->
                <div class="grid grid-cols-2 gap-2">
                    <div class="relative">
                        <select x-model="filterFaculty" class="w-full pl-3 pr-8 py-1.5 text-[10px] font-bold text-slate-500 bg-slate-50 rounded-xl border border-slate-200 focus:border-cyan-500 focus:ring-0 appearance-none cursor-pointer truncate">
                            <option value="">Semua Fakultas</option>
                            @foreach($faculties as $fakultas)
                                <option value="{{ $fakultas }}">{{ $fakultas }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-[8px] pointer-events-none"></i>
                    </div>
                    
                    <div class="relative">
                        <select x-model="filterBuilding" class="w-full pl-3 pr-8 py-1.5 text-[10px] font-bold text-slate-500 bg-slate-50 rounded-xl border border-slate-200 focus:border-cyan-500 focus:ring-0 appearance-none cursor-pointer truncate">
                            <option value="">Semua Gedung</option>
                            @foreach($buildings as $b)
                                <option value="{{ $b->nama_gedung }}">{{ $b->nama_gedung }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-[8px] pointer-events-none"></i>
                    </div>
                </div>
            </div>
            
            <!-- Camera Cards List -->
            <div class="flex-1 overflow-y-auto p-3.5 space-y-2.5 custom-scrollbar bg-slate-50">
                @foreach($cctvs as $cctv)
                <div class="bg-white p-3 rounded-2xl border border-slate-200/60 hover:border-cyan-400/50 active:bg-slate-50 cursor-pointer flex items-center justify-between shadow-sm transition-all select-none group"
                     x-show="
                         (search === '' || '{{ strtolower(addslashes($cctv->nama_cctv)) }}'.includes(search.toLowerCase())) &&
                         (filterFaculty === '' || '{{ addslashes($cctv->building->fakultas ?? "") }}' === filterFaculty) &&
                         (filterBuilding === '' || '{{ addslashes($cctv->building->nama_gedung ?? "") }}' === filterBuilding)
                     "
                     @click="assignCamera({ id: {{ $cctv->id }}, name: '{{ addslashes($cctv->nama_cctv) }}', building: '{{ addslashes($cctv->building->nama_gedung ?? "-") }}', faculty: '{{ addslashes($cctv->building->fakultas ?? "-") }}', liveUrl: '{{ addslashes($cctv->live_stream_url) }}' })">
                     
                     <div class="flex items-center gap-3.5 min-w-0">
                          <!-- Camera Status Icon (Online / Offline highlight) -->
                          <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-all shadow-sm
                              {{ $cctv->status === 'online' ? 'bg-emerald-50 text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white' : 'bg-red-50 text-red-500 group-hover:bg-red-500 group-hover:text-white' }}">
                              <i class="fas fa-video text-sm"></i>
                          </div>
                          
                          <!-- Title & Placement Details -->
                          <div class="min-w-0">
                              <p class="text-xs font-bold text-slate-700 truncate flex items-center gap-1.5">
                                  <span class="truncate">{{ $cctv->nama_cctv }}</span>
                                  <span class="w-1.5 h-1.5 rounded-full {{ $cctv->status === 'online' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                              </p>
                              <p class="text-[9px] font-medium text-slate-400 truncate mt-0.5">
                                  {{ $cctv->building->nama_gedung ?? '-' }} &bull; {{ $cctv->building->fakultas ?? '-' }}
                              </p>
                          </div>
                     </div>
                     
                     <!-- Selection indicator arrow -->
                     <div class="w-8 h-8 rounded-full bg-slate-50 border border-slate-200/80 flex items-center justify-center text-slate-400 transition group-hover:text-cyan-600 group-hover:border-cyan-200 active:scale-90 shadow-sm">
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
                showPtz: false,
                search: '', 
                filterFaculty: '', 
                filterBuilding: '', 
                isMuted: false,
                isRecording: false,
                quality: 'HD',
                ptzSpeed: 5,
                activePtzDir: null,
                
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
                    
                    // Auto-clock updates for LIVE stream
                    setInterval(() => {
                        const now = new Date();
                        if (this.selectedSlot && (!this.activeSlots[this.selectedSlot] || this.activeSlots[this.selectedSlot]?.mode === 'live')) {
                            this.timelineTimeDisplay = now.toLocaleTimeString('en-GB');
                        }
                    }, 1000);
                },

                getActiveSlotsCount() {
                    return Object.keys(this.activeSlots).filter(k => k <= this.gridSize).length;
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
                            vid.muted = this.isMuted;
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
                },

                // Audio Mute/Unmute toggle
                toggleMute() {
                    this.isMuted = !this.isMuted;
                    for (let i = 1; i <= this.gridSize; i++) {
                        const vid = document.getElementById('video-playback-mobile-' + i);
                        if (vid) vid.muted = this.isMuted;
                    }
                    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: this.isMuted ? 'warning' : 'success',
                        title: this.isMuted ? 'Suara Dinonaktifkan' : 'Suara Aktif',
                        showConfirmButton: false,
                        timer: 1500,
                        background: '#ffffff',
                        color: '#1e293b'
                    });
                },

                // Shutter sound mock snapshot
                takeSnapshot() {
                    const slot = this.activeSlots[this.selectedSlot];
                    if (!slot) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Pilih slot kamera aktif terlebih dahulu.',
                            icon: 'warning',
                            background: '#ffffff',
                            color: '#1e293b',
                            confirmButtonColor: '#0891b2'
                        });
                        return;
                    }

                    try {
                        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = audioCtx.createOscillator();
                        const gainNode = audioCtx.createGain();
                        osc.connect(gainNode);
                        gainNode.connect(audioCtx.destination);
                        osc.type = 'triangle';
                        osc.frequency.setValueAtTime(800, audioCtx.currentTime);
                        osc.frequency.exponentialRampToValueAtTime(100, audioCtx.currentTime + 0.15);
                        gainNode.gain.setValueAtTime(0.3, audioCtx.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.15);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.15);
                    } catch(e) {}

                    Swal.fire({
                        title: 'Snapshot Tersimpan',
                        text: `Gambar siaran ${slot.name} telah disimpan ke galeri foto ponsel Anda.`,
                        icon: 'success',
                        background: '#ffffff',
                        color: '#1e293b',
                        confirmButtonColor: '#0891b2'
                    });
                },

                // PTZ Movements simulation
                ptzStart(dir) {
                    this.activePtzDir = dir;
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: `PTZ: Gerak ${dir.toUpperCase()} (Kecepatan: ${this.ptzSpeed})`,
                        showConfirmButton: false,
                        timer: 1000,
                        background: '#ffffff',
                        color: '#1e293b'
                    });
                },

                ptzEnd() {
                    this.activePtzDir = null;
                },

                ptzZoom(type) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `PTZ: Zoom ${type === 'in' ? 'Dekat (+)' : 'Jauh (-)'}`,
                        showConfirmButton: false,
                        timer: 1200,
                        background: '#ffffff',
                        color: '#1e293b'
                    });
                }
            };
        }
    </script>

</body>
</html>
