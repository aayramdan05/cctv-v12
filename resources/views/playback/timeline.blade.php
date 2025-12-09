<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8 h-screen flex flex-col">
        
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-4 gap-4 shrink-0">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-slate-800">Playback</h2>
                <span class="px-3 py-1 bg-slate-100 border border-slate-200 rounded-lg text-xs font-mono text-slate-600 hidden sm:inline-block">
                    {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d M Y') }}
                </span>
            </div>
            
            <form method="GET" id="filter-form" class="bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-center gap-4 w-full xl:w-auto">
                
                @if(auth()->user()->role !== 'faculty_operator')
                    <div class="flex items-center gap-2 border-r border-slate-200 pr-4">
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Fakultas</label>
                        <select name="faculty" class="bg-transparent border-none text-sm font-bold text-slate-700 focus:ring-0 cursor-pointer p-0 w-32 truncate" 
                                onchange="this.form.submit()">
                            <option value="">-- Semua --</option>
                            @foreach($faculties as $fac)
                                <option value="{{ $fac }}" {{ $selectedFaculty == $fac ? 'selected' : '' }}>{{ $fac }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-center gap-2 border-r border-slate-200 pr-4">
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Gedung</label>
                    <select name="building_id" class="bg-transparent border-none text-sm font-bold text-slate-700 focus:ring-0 cursor-pointer p-0 w-32 truncate" 
                            onchange="this.form.submit()">
                        <option value="">-- Semua --</option>
                        @foreach($buildings as $b)
                            <option value="{{ $b->id }}" {{ $selectedBuildingId == $b->id ? 'selected' : '' }}>{{ $b->nama_gedung }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 border-r border-slate-200 pr-4">
                    <i class="fas fa-video text-slate-400 text-xs"></i>
                    <select name="cctv_id" class="bg-transparent border-none text-sm font-bold text-slate-700 focus:ring-0 cursor-pointer p-0 w-40 truncate" 
                            onchange="this.form.submit()">
                        @forelse($cctvs as $cam)
                            <option value="{{ $cam->id }}" {{ $selectedCctvId == $cam->id ? 'selected' : '' }}>
                                {{ $cam->nama_cctv }}
                            </option>
                        @empty
                            <option disabled>Tidak ada kamera</option>
                        @endforelse
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <i class="fas fa-calendar text-slate-400 text-xs"></i>
                    <input type="date" name="date" value="{{ $date }}" 
                           class="bg-transparent border-none text-sm font-bold text-slate-700 focus:ring-0 cursor-pointer p-0"
                           onchange="this.form.submit()">
                </div>
            </form>
        </div>

        <div class="flex flex-1 gap-6 overflow-hidden min-h-0 flex-col lg:flex-row">
            
            <div class="flex-[3] flex flex-col bg-black rounded-2xl overflow-hidden shadow-xl relative group border border-slate-800 min-h-[300px]">
                <video id="main-player" 
                      class="w-full h-full object-contain" 
                      controls 
                      autoplay 
                      controlsList="nodownload" 
                      oncontextmenu="return false;">
                    <source src="" type="video/mp4">
                </video>
                
                <div class="absolute top-4 left-4 px-4 py-2 bg-black/70 backdrop-blur rounded-lg text-white border border-white/10 pointer-events-none">
                    <div id="current-video-info">
                        <h3 class="font-bold text-sm">Ready to Play</h3>
                        <p class="text-xs text-gray-300">Pilih rekaman di bawah.</p>
                    </div>
                </div>
            </div>

            <div class="flex-1 bg-white rounded-2xl border border-slate-200 flex flex-col overflow-hidden shadow-sm max-w-md lg:max-w-xs">
                <div class="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-700 text-sm">Playlist Files</h3>
                    <span class="text-xs text-slate-400" id="total-files">0 Files</span>
                </div>
                <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scrollbar" id="playlist-container">
                    <div class="text-center py-10 text-slate-400 text-xs">Memuat data...</div>
                </div>
            </div>
        </div>

        <div class="mt-4 shrink-0 bg-white p-3 rounded-xl border border-slate-200 shadow-sm select-none">
            <div class="flex justify-between items-center mb-2 px-1">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-search-minus text-slate-400 text-xs"></i>
                        <input type="range" id="zoom-slider" min="1" max="10" value="1" step="0.5" 
                               class="w-32 h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-cyan-500">
                        <i class="fas fa-search-plus text-slate-400 text-xs"></i>
                    </div>
                    <span class="text-xs text-slate-500 font-mono" id="zoom-level-text">24h View</span>
                </div>
            </div>
            
            <div class="relative w-full h-16 bg-slate-100 rounded-lg overflow-x-auto overflow-y-hidden border border-slate-200 custom-scrollbar" id="timeline-scroll-area">
                <div class="relative h-full transition-all duration-200 ease-out" id="timeline-track" style="width: 100%;">
                    <div class="absolute inset-0 flex pointer-events-none">
                        @for($i=0; $i<=24; $i++)
                            <div class="flex-1 border-l border-slate-300/50 h-full relative">
                                <span class="absolute top-1 left-1 text-[10px] font-mono text-slate-400">{{ sprintf("%02d:00", $i) }}</span>
                            </div>
                        @endfor
                    </div>
                    <div id="timeline-blocks" class="absolute inset-y-0 w-full top-6 bottom-0"></div>
                </div>
            </div>
        </div>

    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const dateParam = "{{ $date }}";
            const camIdParam = "{{ $selectedCctvId }}";
            
            const player = document.getElementById('main-player');
            const container = document.getElementById('playlist-container');
            const timelineBlocks = document.getElementById('timeline-blocks');
            const track = document.getElementById('timeline-track');
            const videoInfo = document.getElementById('current-video-info');
            const zoomSlider = document.getElementById('zoom-slider');
            const zoomText = document.getElementById('zoom-level-text');
            const totalFilesLabel = document.getElementById('total-files');
            
            let recordings = [];
            let currentIndex = -1;

            if (!camIdParam) {
                container.innerHTML = '<div class="p-4 text-center text-xs text-slate-400">Silakan pilih kamera.</div>';
                return;
            }

            // FETCH DATA
            fetch(`{{ route('playback.data') }}?date=${dateParam}&cctv_id=${camIdParam}`)
                .then(res => res.json())
                .then(data => {
                    recordings = data;
                    totalFilesLabel.innerText = `${recordings.length} Files`;
                    renderAll();
                })
                .catch(err => {
                    console.error(err);
                    container.innerHTML = '<div class="p-4 text-center text-xs text-red-400">Gagal memuat data.</div>';
                });

            function renderAll() {
                container.innerHTML = '';
                timelineBlocks.innerHTML = '';

                if(recordings.length === 0) {
                    container.innerHTML = '<div class="p-10 text-center text-xs text-slate-400 flex flex-col items-center"><i class="fas fa-film text-2xl mb-2 opacity-20"></i>Tidak ada rekaman.</div>';
                    return;
                }

                const secondsInDay = 86400; 

                recordings.forEach((rec, index) => {
                    // 1. Render Playlist Item
                    let item = document.createElement('div');
                    let isActive = index === currentIndex;
                    item.className = `p-3 rounded-lg cursor-pointer border transition flex justify-between items-center group ${isActive ? 'bg-cyan-50 border-cyan-200 shadow-sm' : 'bg-white border-transparent hover:bg-slate-50 hover:border-slate-200'}`;
                    
                    // PERBAIKAN UTAMA: Pastikan rec.building_name tidak undefined
                    // Kita pakai fallback 'Unknown' jika data kosong
                    let buildingName = rec.building_name || 'Unknown Building';
                    
                    item.innerHTML = `
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-8 h-8 shrink-0 rounded bg-slate-100 flex items-center justify-center text-slate-400 text-xs group-hover:text-cyan-500 group-hover:bg-cyan-100 transition-colors">
                                <i class="fas ${isActive ? 'fa-volume-up animate-pulse' : 'fa-play'}"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-700 truncate">${rec.start_time} - ${rec.end_time}</p>
                                <p class="text-[10px] text-slate-500 truncate" title="${buildingName}">${buildingName}</p>
                            </div>
                        </div>
                    `;
                    item.onclick = () => playVideo(index);
                    if(isActive) { setTimeout(() => item.scrollIntoView({ behavior: 'smooth', block: 'center' }), 100); }
                    container.appendChild(item);

                    // 2. Render Timeline Block
                    let [h, m] = rec.start_time.split(':');
                    let startSeconds = (parseInt(h) * 3600) + (parseInt(m) * 60); 
                    let leftPercent = (startSeconds / secondsInDay) * 100;
                    let widthPercent = (rec.duration / secondsInDay) * 100;

                    let block = document.createElement('div');
                    block.className = `absolute top-0 bottom-0 h-full bg-green-500 hover:bg-green-400 cursor-pointer border-r border-white/20 transition-colors z-10 ${isActive ? 'bg-green-300 brightness-110 ring-2 ring-cyan-300 z-20' : ''}`;
                    block.style.left = `${leftPercent}%`;
                    block.style.width = `${widthPercent}%`;
                    block.title = `Putar ${rec.start_time}`;
                    block.onclick = () => playVideo(index);
                    timelineBlocks.appendChild(block);
                });
            }

            zoomSlider.addEventListener('input', function() {
                const zoomVal = this.value;
                track.style.width = `${zoomVal * 100}%`;
                if(zoomVal == 1) zoomText.innerText = "24h View";
                else if(zoomVal > 1 && zoomVal < 5) zoomText.innerText = "Zoomed";
                else zoomText.innerText = "Max Precision";
            });

            window.playVideo = function(index) {
                if(!recordings[index]) return;
                currentIndex = index;
                let rec = recordings[index];
                
                player.src = rec.url;
                player.play();
                
                // PERBAIKAN UTAMA: Handle undefined data saat update judul
                let faculty = rec.faculty_name || 'FACULTY';
                let building = rec.building_name || 'BUILDING';
                let camName = rec.cctv_name || 'CAMERA';

                videoInfo.innerHTML = `
                    <span class="text-cyan-300 block text-[10px] font-bold uppercase mb-0.5 tracking-wider">${faculty} • ${building}</span>
                    <h3 class="font-bold text-lg leading-none">${camName}</h3>
                    <p class="text-xs text-gray-400 mt-1 font-mono">${rec.start_time} - ${rec.end_time}</p>
                `;
                
                renderAll(); 
            };

            player.addEventListener('ended', function() {
                if (currentIndex < recordings.length - 1) {
                    playVideo(currentIndex + 1);
                }
            });
        });
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</x-app-layout>