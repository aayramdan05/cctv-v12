<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8 h-screen flex flex-col relative">
        
        <!-- Pesan Error/Sukses (Flash Message) -->
        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-4 gap-4 shrink-0">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-slate-800">Playback</h2>
                <span class="px-3 py-1 bg-slate-100 border border-slate-200 rounded-lg text-xs font-mono text-slate-600 hidden sm:inline-block">
                    {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d M Y') }}
                </span>

                <!-- TOMBOL EXPORT-->
                @if(auth()->user()->role === 'admin')
                    <button onclick="document.getElementById('export-modal').classList.remove('hidden')" 
                            class="ml-2 flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm">
                        <i class="fas fa-file-export"></i> Export ZIP
                    </button>
                @endif
            </div>
            
            <!-- FORM FILTER (KODE LAMA ANDA TETAP DISINI) -->
            <form method="GET" id="filter-form" class="bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-center gap-4 w-full xl:w-auto">
                 {{-- ... (Isi form filter seperti kode anda sebelumnya: Fakultas, Gedung, CCTV, Date) ... --}}
                 {{-- Copy paste form filter dari kode lama Anda disini --}}
                 {{-- Pastikan input select CCTV memiliki id="cctv_selector" agar mudah diambil JS modal --}}
                 @if(auth()->user()->role !== 'faculty_operator')
                    <div class="flex items-center gap-2 border-r border-slate-200 pr-4">
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Fakultas</label>
                        <select name="faculty" class="bg-transparent border-none text-sm font-bold text-slate-700 focus:ring-0 cursor-pointer p-0 w-32 truncate" onchange="this.form.submit()">
                            <option value="">-- Semua --</option>
                            @foreach($faculties as $fac)
                                <option value="{{ $fac }}" {{ $selectedFaculty == $fac ? 'selected' : '' }}>{{ $fac }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-center gap-2 border-r border-slate-200 pr-4">
                    <label class="text-[10px] font-bold text-slate-400 uppercase">Gedung</label>
                    <select name="building_id" class="bg-transparent border-none text-sm font-bold text-slate-700 focus:ring-0 cursor-pointer p-0 w-32 truncate" onchange="this.form.submit()">
                        <option value="">-- Semua --</option>
                        @foreach($buildings as $b)
                            <option value="{{ $b->id }}" {{ $selectedBuildingId == $b->id ? 'selected' : '' }}>{{ $b->nama_gedung }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 border-r border-slate-200 pr-4">
                    <i class="fas fa-video text-slate-400 text-xs"></i>
                    <!-- Saya tambah ID cctv_selector disini -->
                    <select name="cctv_id" id="cctv_selector" class="bg-transparent border-none text-sm font-bold text-slate-700 focus:ring-0 cursor-pointer p-0 w-40 truncate" onchange="this.form.submit()">
                        @forelse($cctvs as $cam)
                            <option value="{{ $cam->id }}" {{ $selectedCctvId == $cam->id ? 'selected' : '' }}>{{ $cam->nama_cctv }}</option>
                        @empty
                            <option disabled>Tidak ada kamera</option>
                        @endforelse
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <i class="fas fa-calendar text-slate-400 text-xs"></i>
                    <!-- Saya tambah ID date_selector disini -->
                    <input type="date" name="date" id="date_selector" value="{{ $date }}" class="bg-transparent border-none text-sm font-bold text-slate-700 focus:ring-0 cursor-pointer p-0" onchange="this.form.submit()">
                </div>
            </form>
        </div>

        <!-- PLAYER & PLAYLIST CONTAINER (KODE LAMA TETAP SAMA) -->
        <div class="flex flex-1 gap-6 overflow-hidden min-h-0 flex-col lg:flex-row">
            {{-- ... (Area Video Player & Playlist Anda) ... --}}
            {{-- Copy paste area div flex-1 player anda --}}
            <div class="flex-[3] flex flex-col bg-black rounded-2xl overflow-hidden shadow-xl relative group border border-slate-800 min-h-[300px]">
                <video id="main-player" class="w-full h-full object-contain" controls autoplay controlsList="nodownload" oncontextmenu="return false;">
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

        <!-- TIMELINE SLIDER (KODE LAMA TETAP SAMA) -->
        <div class="mt-4 shrink-0 bg-white p-3 rounded-xl border border-slate-200 shadow-sm select-none">
             {{-- ... (Kode Slider Timeline Anda) ... --}}
             <div class="flex justify-between items-center mb-2 px-1">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-search-minus text-slate-400 text-xs"></i>
                        <input type="range" id="zoom-slider" min="1" max="10" value="1" step="0.5" class="w-32 h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-cyan-500">
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

    <!-- ================= MODAL EXPORT ================= -->
    <div id="export-modal" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('export-modal').classList.add('hidden')"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <form action="{{ route('playback.export') }}" method="POST">
                        @csrf
                        <!-- Hidden Inputs (Mengambil data dari filter yang sedang aktif) -->
                        <input type="hidden" name="cctv_id" value="{{ $selectedCctvId }}">
                        <input type="hidden" name="date" value="{{ $date }}">

                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 mb-2">Export Rekaman</h3>
                            <p class="text-sm text-gray-500 mb-4">Pilih rentang waktu untuk tanggal <b>{{ $date }}</b>.</p>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Jam Mulai</label>
                                    <input type="time" name="start_time" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-emerald-600 sm:text-sm sm:leading-6">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Jam Selesai</label>
                                    <input type="time" name="end_time" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-emerald-600 sm:text-sm sm:leading-6">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 sm:ml-3 sm:w-auto">Proses di Background</button>
                            <button type="button" onclick="document.getElementById('export-modal').classList.add('hidden')" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= END MODAL ================= -->

    {{-- Script JS lama Anda tetap dibawah sini --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // ... (Kode Javascript yang sudah ada sebelumnya jangan dihapus) ...
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