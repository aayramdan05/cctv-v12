<x-app-layout>
    <!-- Tambahkan SweetAlert2 untuk Popup Keren -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div x-data="playbackApp()" x-init="initApp()">
        <main id="main-content" class="pt-20 p-6 md:p-8 h-screen flex flex-col relative bg-slate-50">
        
        <!-- Header Playback -->
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-4 gap-4 shrink-0">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-slate-800">Playback</h2>
                <span class="px-3 py-1 bg-white border border-slate-200 rounded-lg text-xs font-mono text-slate-600 hidden sm:inline-block shadow-sm" x-text="date"></span>

                <!-- TOMBOL EXPORT (ADMIN ONLY) -->
                @can('playback_export')
                    <button onclick="openExportModal()" 
                            class="ml-2 flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-sm active:scale-95">
                         <i class="fas fa-file-export"></i> Export ZIP
                    </button>
                @endcan
            </div>
            
            <!-- FORM FILTER MODERN (Alpine.js) -->
            <div class="bg-white p-1 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center gap-1 w-full xl:w-auto">
                
                @if(auth()->user()->role !== 'faculty_operator')
                    <div class="flex items-center px-3 py-2 border-r border-slate-100">
                        <select x-model="selectedFaculty" @change="selectFaculty($event.target.value)" class="bg-transparent border-none text-xs font-bold text-slate-700 focus:ring-0 cursor-pointer p-0 w-24 truncate">
                            <option value="">-- Fakultas --</option>
                            @foreach($faculties as $fac)
                                <option value="{{ $fac }}">{{ $fac }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- SEARCHABLE BUILDING -->
                <div class="relative px-3 py-2 border-r border-slate-100 min-w-[150px]" @click.away="showBuilding = false">
                    <button type="button" @click="showBuilding = !showBuilding" class="flex items-center justify-between w-full text-left">
                        <span class="text-xs font-bold text-slate-700 truncate" x-text="selectedBuildingName"></span>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400 ml-2"></i>
                    </button>
                    
                    <div x-show="showBuilding" x-transition class="absolute top-full left-0 mt-2 w-64 bg-white border border-slate-200 rounded-xl shadow-xl z-[110] p-2" x-cloak>
                        <input type="text" x-model="searchBuilding" placeholder="Cari Gedung..." 
                               class="w-full text-xs border-slate-200 rounded-lg mb-2 focus:ring-cyan-500 focus:border-cyan-500">
                        <div class="max-h-48 overflow-y-auto custom-scrollbar">
                            <button type="button" @click="selectBuilding(null)" 
                                    class="w-full text-left px-3 py-2 text-xs hover:bg-slate-50 rounded-lg font-medium text-slate-600">-- Semua Gedung --</button>
                            <template x-for="b in filteredBuildings" :key="b.id">
                                <button type="button" 
                                        @click="selectBuilding(b.id)"
                                        class="w-full text-left px-3 py-2 text-xs hover:bg-cyan-50 hover:text-cyan-700 rounded-lg transition-colors font-medium text-slate-700"
                                        x-text="b.name"></button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- SEARCHABLE CCTV -->
                <div class="relative px-3 py-2 border-r border-slate-100 min-w-[180px]" @click.away="showCctv = false">
                    <button type="button" @click="showCctv = !showCctv" class="flex items-center justify-between w-full text-left">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <i class="fas fa-video text-cyan-500 text-[10px]"></i>
                            <span class="text-xs font-bold text-slate-800 truncate" x-text="selectedCctvName"></span>
                        </div>
                        <i class="fas fa-chevron-down text-[10px] text-slate-400 ml-2"></i>
                    </button>

                    <div x-show="showCctv" x-transition class="absolute top-full left-0 mt-2 w-72 bg-white border border-slate-200 rounded-xl shadow-xl z-[110] p-2" x-cloak>
                        <input type="text" x-model="searchCctv" placeholder="Cari Kamera..." 
                               class="w-full text-xs border-slate-200 rounded-lg mb-2 focus:ring-cyan-500 focus:border-cyan-500">
                        <div class="max-h-60 overflow-y-auto custom-scrollbar">
                            <template x-for="c in filteredCctvs" :key="c.id">
                                <button type="button" 
                                        @click="selectCctv(c.id)"
                                        class="w-full text-left px-3 py-2 text-xs hover:bg-cyan-50 hover:text-cyan-700 rounded-lg transition-colors font-medium"
                                        :class="c.id == selectedCctvId ? 'bg-cyan-50 text-cyan-700 font-bold' : 'text-slate-700'"
                                        x-text="c.name"></button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex items-center px-4 py-2">
                    <input type="date" x-model="date" @change="changeDate($event.target.value)"
                           class="bg-transparent border-none text-xs font-bold text-slate-700 focus:ring-0 cursor-pointer p-0">
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="flex flex-1 gap-4 overflow-hidden min-h-0 flex-col lg:flex-row">
            
            <!-- LEFT SIDEBAR: CAMERA LIST (SEAMLESS SEARCH) -->
            <div class="w-full lg:w-64 flex flex-col bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden shrink-0">
                <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <i class="fas fa-video"></i> Daftar Kamera
                    </h3>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <input type="text" x-model="camSearch" placeholder="Cari kamera..." 
                               class="w-full pl-9 pr-4 py-2 rounded-xl bg-white border-slate-200 text-xs focus:ring-cyan-500 focus:border-cyan-500 transition-all">
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1">
                    <template x-for="cam in sidebarCctvs" :key="cam.id">
                        <button type="button" 
                                @click="selectCctv(cam.id)"
                                class="w-full text-left px-3 py-2.5 rounded-xl transition-all group flex flex-col gap-0.5"
                                :class="cam.id == selectedCctvId ? 'bg-cyan-600 shadow-lg shadow-cyan-600/20' : 'hover:bg-slate-50 border border-transparent hover:border-slate-100'">
                            <span class="text-xs font-bold truncate" :class="cam.id == selectedCctvId ? 'text-white' : 'text-slate-700'" x-text="cam.name"></span>
                            <span class="text-[10px] truncate" :class="cam.id == selectedCctvId ? 'text-cyan-100' : 'text-slate-400'" x-text="cam.building_name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- MIDDLE: VIDEO PLAYER -->
            <div class="flex-1 flex flex-col gap-4 min-w-0">
                <div class="flex-1 flex flex-col bg-black rounded-2xl overflow-hidden shadow-xl relative group border border-slate-800 min-h-[300px]">
                    <video id="main-player" class="w-full h-full object-contain" controls autoplay controlsList="nodownload" oncontextmenu="return false;">
                        <source src="" type="video/mp4">
                    </video>
                    <div class="absolute top-4 left-4 px-4 py-2 bg-black/70 backdrop-blur rounded-lg text-white border border-white/10 pointer-events-none transition-opacity duration-500" id="video-info-overlay">
                        <div id="current-video-info">
                            <h3 class="font-bold text-sm">Ready to Play</h3>
                            <p class="text-xs text-gray-300">Pilih kamera di samping kiri.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDEBAR: PLAYLIST -->
            <div class="w-full lg:w-72 flex flex-col bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden shrink-0">
                <!-- Header Playlist -->
                <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col gap-3">
                    <div class="flex justify-between items-center">
                        <h3 class="font-bold text-slate-700 text-xs flex items-center gap-2">
                            <i class="fas fa-list text-slate-400"></i> File Rekaman
                        </h3>
                        <span class="px-2 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-bold text-slate-500" id="total-files">0</span>
                    </div>

                    @can('playback_export')
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="select-all-recordings" onchange="toggleSelectAll()" class="w-3.5 h-3.5 text-cyan-600 border-slate-300 rounded focus:ring-cyan-500 cursor-pointer">
                            <label for="select-all-recordings" class="text-[10px] font-bold text-slate-500 cursor-pointer">Semua</label>
                        </div>
                        <button id="btn-download-selected" onclick="downloadSelected()" disabled 
                                class="px-2 py-1 bg-white border border-slate-200 rounded text-[10px] font-bold text-slate-400 hover:text-emerald-600 transition flex items-center gap-1">
                            <i class="fas fa-download"></i> <span id="download-count">0</span>
                        </button>
                    </div>
                    @endcan
                </div>

                <!-- Daftar File -->
                <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scrollbar" id="playlist-container">
                    <div class="text-center py-10 text-slate-400 text-xs flex flex-col items-center">
                        <i class="fas fa-arrow-left mb-2 animate-bounce"></i> Pilih kamera terlebih dahulu
                    </div>
                </div>
            </div>
        </div>

        <!-- TIMELINE SLIDER -->
        <div class="mt-4 shrink-0 bg-white p-3 rounded-xl border border-slate-200 shadow-sm select-none">
            <div class="flex justify-between items-center mb-2 px-1">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 bg-slate-100 px-2 py-1 rounded-lg border border-slate-200">
                        <i class="fas fa-search-minus text-slate-400 text-xs"></i>
                        <input type="range" id="zoom-slider" min="1" max="10" value="1" step="0.5" class="w-24 h-1 bg-slate-300 rounded-lg appearance-none cursor-pointer accent-cyan-600">
                        <i class="fas fa-search-plus text-slate-400 text-xs"></i>
                    </div>
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider" id="zoom-level-text">24H VIEW</span>
                </div>
            </div>
            
            <div class="relative w-full h-16 bg-slate-50 rounded-lg overflow-x-auto overflow-y-hidden border border-slate-200 custom-scrollbar shadow-inner" id="timeline-scroll-area">
                <div class="relative h-full transition-all duration-200 ease-out" id="timeline-track" style="width: 100%;">
                    <!-- Hours Marker -->
                    <div class="absolute inset-0 flex pointer-events-none select-none">
                        @for($i=0; $i<=24; $i++)
                            <div class="flex-1 border-l border-slate-300/50 h-full relative group">
                                <span class="absolute top-1 left-1 text-[9px] font-mono text-slate-400 group-hover:text-slate-600 font-bold transition-colors">{{ sprintf("%02d:00", $i) }}</span>
                            </div>
                        @endfor
                    </div>
                    <!-- Blocks Container -->
                    <div id="timeline-blocks" class="absolute inset-y-0 w-full top-6 bottom-1"></div>
                </div>
            </div>
        </div>

    </main>

    @can('playback_export')
    <!-- ================= MODAL EXPORT REDESIGNED ================= -->
    <div id="export-modal" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop with Blur -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modal-backdrop"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md scale-95 opacity-0" id="modal-panel">
                    
                    <form action="{{ route('playback.export') }}" method="POST">
                        @csrf
                        <input type="hidden" name="cctv_id" :value="selectedCctvId">
                        <input type="hidden" name="date" :value="date">

                        <!-- Modal Header -->
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                <i class="fas fa-file-archive"></i> Export Rekaman
                            </h3>
                            <button type="button" onclick="closeExportModal()" class="text-white/80 hover:text-white hover:bg-white/20 rounded-full w-8 h-8 flex items-center justify-center transition">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="px-6 py-6 space-y-4">
                            <!-- Info Box -->
                            <div class="bg-slate-50 border border-slate-100 rounded-lg p-3 flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                                    <i class="fas fa-video text-emerald-600"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 uppercase font-bold tracking-wide">Kamera & Tanggal</p>
                                    <p class="text-sm font-bold text-slate-800" x-text="selectedCctvName"></p>
                                    <p class="text-xs text-slate-600" x-text="date"></p>
                                </div>
                            </div>

                            <p class="text-sm text-slate-500">
                                Pilih rentang waktu yang ingin Anda unduh. Sistem akan memproses file <b>.ZIP</b> di latar belakang.
                            </p>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Mulai Jam</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="far fa-clock text-slate-400"></i>
                                        </div>
                                        <input type="time" name="start_time" required 
                                               class="pl-10 block w-full rounded-lg border-slate-300 bg-slate-50 py-2 text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm font-mono font-bold transition">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Sampai Jam</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="far fa-clock text-slate-400"></i>
                                        </div>
                                        <input type="time" name="end_time" required 
                                               class="pl-10 block w-full rounded-lg border-slate-300 bg-slate-50 py-2 text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm font-mono font-bold transition">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-100">
                            <button type="submit" class="inline-flex w-full justify-center items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-500 active:scale-95 transition sm:w-auto">
                                <i class="fas fa-cog fa-spin hidden" id="loading-icon"></i>
                                <span>Proses Export</span>
                            </button>
                            <button type="button" onclick="closeExportModal()" class="inline-flex w-full justify-center rounded-lg bg-white border border-slate-300 px-4 py-2 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50 active:scale-95 transition sm:w-auto">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- ================= END MODAL ================= -->
    @endcan

    <script>
        @can('playback_export')
        // --- MODAL LOGIC & ANIMATION ---
        const modal = document.getElementById('export-modal');
        const backdrop = document.getElementById('modal-backdrop');
        const panel = document.getElementById('modal-panel');

        function openExportModal() {
            if (!modal) return;
            modal.classList.remove('hidden');
            // Slight delay for animation
            setTimeout(() => {
                backdrop.classList.remove('opacity-0');
                panel.classList.remove('scale-95', 'opacity-0');
                panel.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeExportModal() {
            if (!modal) return;
            backdrop.classList.add('opacity-0');
            panel.classList.remove('scale-100', 'opacity-100');
            panel.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300); // Match transition duration
        }
        @endcan

        // --- POPUP NOTIFICATION LOGIC ---
        // Cek Session dari Controller
        @if(session('success'))
            Swal.fire({
                title: 'Sedang Diproses!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'Oke, Mengerti',
                confirmButtonColor: '#10b981', // Emerald 500
                background: '#fff',
                iconColor: '#10b981',
                showClass: { popup: 'animate__animated animate__fadeInDown' },
                hideClass: { popup: 'animate__animated animate__fadeOutUp' }
            });
        @endif

        @if(session('error'))
            Swal.fire({
                title: 'Gagal!',
                text: "{{ session('error') }}",
                icon: 'error',
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#ef4444'
            });
        @endif

        // --- BULK DOWNLOAD LOGIC ---
        function toggleSelectAll() {
            const masterCheckbox = document.getElementById('select-all-recordings');
            const checkboxes = document.querySelectorAll('.rec-checkbox');
            checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
            updateDownloadCount();
        }

        function updateDownloadCount() {
            const selected = document.querySelectorAll('.rec-checkbox:checked').length;
            const btn = document.getElementById('btn-download-selected');
            const countLabel = document.getElementById('download-count');
            
            countLabel.innerText = selected;
            btn.disabled = selected === 0;
            
            if (selected > 0) {
                btn.classList.remove('text-slate-400', 'border-slate-200');
                btn.classList.add('text-emerald-600', 'border-emerald-500', 'bg-emerald-50');
            } else {
                btn.classList.add('text-slate-400', 'border-slate-200');
                btn.classList.remove('text-emerald-600', 'border-emerald-500', 'bg-emerald-50');
            }
        }

        async function downloadSelected() {
            const selectedCheckboxes = document.querySelectorAll('.rec-checkbox:checked');
            if (selectedCheckboxes.length === 0) return;

            const urls = Array.from(selectedCheckboxes).map(cb => cb.dataset.url);
            
            Swal.fire({
                title: 'Memulai Download',
                text: `Mendownload ${urls.length} file. Harap izinkan browser jika muncul pop-up multiple download.`,
                icon: 'info',
                timer: 3000,
                showConfirmButton: false
            });

            // Loop untuk men-download satu per satu
            for (let i = 0; i < urls.length; i++) {
                const url = urls[i];
                const filename = url.split('/').pop();
                
                // --- LOG ACTIVITY KE DATABASE ---
                try {
                    await fetch('{{ route("playback.logDownload") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            filename: filename,
                            cctv_id: '{{ $selectedCctvId }}'
                        })
                    });
                } catch (e) {
                    console.error("Gagal mencatat log download:", e);
                }
                
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Jeda sebentar agar tidak membebani browser
                await new Promise(resolve => setTimeout(resolve, 800));
            }
        }

        // --- EXISTING PLAYER LOGIC ---
        document.addEventListener("DOMContentLoaded", function() {
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

            // Listen to Alpine component events
            window.addEventListener('load-recordings', function(e) {
                const camIdParam = e.detail.cctvId;
                const dateParam = e.detail.date;

                if (!camIdParam) {
                    container.innerHTML = '<div class="p-10 text-center text-xs text-slate-400">Silakan pilih kamera terlebih dahulu.</div>';
                    return;
                }

                // Tampilkan loading saat fetch
                container.innerHTML = '<div class="p-10 text-center text-xs text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i> Memuat data...</div>';

                // FETCH DATA
                fetch(`{{ route('playback.data') }}?date=${dateParam}&cctv_id=${camIdParam}`)
                    .then(res => res.json())
                    .then(data => {
                        recordings = data;
                        if (totalFilesLabel) totalFilesLabel.innerText = recordings.length;
                        // Auto-select first video if available
                        currentIndex = recordings.length > 0 ? 0 : -1;
                        renderAll();
                        if (currentIndex === 0) playVideo(0);
                    })
                    .catch(err => {
                        console.error(err);
                        container.innerHTML = '<div class="p-4 text-center text-xs text-red-400">Gagal memuat data.</div>';
                    });
            });

            function renderAll() {
                container.innerHTML = '';
                timelineBlocks.innerHTML = '';

                if(recordings.length === 0) {
                    container.innerHTML = `
                        <div class="h-full flex flex-col items-center justify-center text-slate-300">
                            <i class="fas fa-film text-4xl mb-3"></i>
                            <p class="text-xs">Tidak ada rekaman</p>
                        </div>`;
                    return;
                }

                const secondsInDay = 86400; 

                recordings.forEach((rec, index) => {
                    // 1. Render Playlist Item
                    let item = document.createElement('div');
                    let isActive = index === currentIndex;
                    item.className = `p-3 rounded-lg border transition flex justify-between items-center group ${isActive ? 'bg-cyan-50 border-cyan-200 shadow-sm' : 'bg-white border-transparent hover:bg-slate-50 hover:border-slate-200'}`;
                    
                    let buildingName = rec.building_name || 'Unknown';
                    
                    // Logic Checkbox (Hanya Admin)
                    const isAdmin = @json(Gate::allows('playback_export'));
                    let checkboxHtml = isAdmin ? `
                        <input type="checkbox" class="rec-checkbox w-3.5 h-3.5 text-cyan-600 border-slate-300 rounded focus:ring-cyan-500 cursor-pointer" 
                               data-url="${rec.url}" onchange="updateDownloadCount()" onclick="event.stopPropagation()">
                    ` : '';
                    
                    item.innerHTML = `
                        <div class="flex items-center gap-3 overflow-hidden">
                            ${checkboxHtml}
                            
                            <div class="flex items-center gap-3 overflow-hidden cursor-pointer" onclick="playVideo(${index})">
                                <div class="w-8 h-8 shrink-0 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 text-xs group-hover:text-cyan-600 group-hover:bg-cyan-100 transition-colors">
                                    <i class="fas ${isActive ? 'fa-chart-bar animate-pulse' : 'fa-play'}"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-700 truncate font-mono">${rec.start_time} - ${rec.end_time}</p>
                                    <p class="text-[9px] text-slate-400 truncate uppercase tracking-wider" title="${buildingName}">${buildingName}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    if(isActive) { setTimeout(() => item.scrollIntoView({ behavior: 'smooth', block: 'center' }), 100); }
                    container.appendChild(item);

                    // 2. Render Timeline Block
                    let [h, m] = rec.start_time.split(':');
                    let startSeconds = (parseInt(h) * 3600) + (parseInt(m) * 60); 
                    let leftPercent = (startSeconds / secondsInDay) * 100;
                    let widthPercent = (rec.duration / secondsInDay) * 100;

                    let block = document.createElement('div');
                    // Style updated for clearer segments
                    block.className = `absolute top-1 bottom-1 bg-emerald-400 hover:bg-emerald-300 cursor-pointer rounded-sm border-r border-white/30 transition-all z-10 ${isActive ? 'bg-emerald-300 ring-2 ring-white z-20 shadow-lg' : ''}`;
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
                if(zoomVal == 1) zoomText.innerText = "24H VIEW";
                else if(zoomVal > 1 && zoomVal < 5) zoomText.innerText = "ZOOMED";
                else zoomText.innerText = "MAX PRECISION";
            });

            window.playVideo = function(index) {
                if(!recordings[index]) return;
                currentIndex = index;
                let rec = recordings[index];
                
                if (rec.is_live) {
                    // Play directly from the active .temp.mp4 file!
                    player.src = rec.url.replace('.mp4', '.temp.mp4');
                    let playPromise = player.play();
                    if (playPromise !== undefined) {
                        playPromise.catch(error => {
                            // Jika .temp.mp4 gagal diload (404), fallback ke .mp4 standar
                            if (player.src.includes('.temp.mp4')) {
                                console.log("Fallback (catch): mencoba file utama .mp4");
                                player.src = rec.url;
                                player.play();
                            }
                        });
                    }
                } else {
                    player.src = rec.url;
                    player.play();
                }
                
                let faculty = rec.faculty_name || 'FACULTY';
                let building = rec.building_name || 'BUILDING';
                let camName = rec.cctv_name || 'CAMERA';

                videoInfo.innerHTML = `
                    <span class="text-cyan-300 block text-[10px] font-bold uppercase mb-0.5 tracking-wider">${faculty} • ${building}</span>
                    <h3 class="font-bold text-lg leading-none text-white shadow-black drop-shadow-md">${camName}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-1.5 py-0.5 bg-white/20 rounded text-[10px] font-mono text-white">${rec.start_time}</span>
                        <i class="fas fa-arrow-right text-[8px] text-white/50"></i>
                        <span class="px-1.5 py-0.5 bg-white/20 rounded text-[10px] font-mono text-white">${rec.end_time}</span>
                    </div>
                `;
                
                renderAll(); 
            };

            player.addEventListener('ended', function() {
                if (currentIndex < recordings.length - 1) {
                    playVideo(currentIndex + 1);
                }
            });
        });

        // --- ALPINE.JS COMPONENT ---
        function playbackApp() {
            return {
                date: '{{ $date }}',
                selectedFaculty: '{{ $selectedFaculty ?? '' }}',
                selectedBuildingId: {{ $selectedBuildingId ?: 'null' }},
                selectedCctvId: {{ $selectedCctvId ?: 'null' }},
                
                allBuildings: @json($buildings->map(fn($b) => ['id' => $b->id, 'name' => $b->nama_gedung, 'faculty' => $b->fakultas])),
                allCctvs: @json($cctvs->map(fn($c) => ['id' => $c->id, 'name' => $c->nama_cctv, 'building_id' => $c->building_id, 'building_name' => $c->building->nama_gedung ?? 'N/A'])),
                
                showBuilding: false,
                showCctv: false,
                searchBuilding: '',
                searchCctv: '',
                camSearch: '',

                get filteredBuildings() {
                    let b = this.allBuildings;
                    if (this.selectedFaculty) {
                        b = b.filter(i => i.faculty === this.selectedFaculty);
                    }
                    if (this.searchBuilding) {
                        b = b.filter(i => i.name.toLowerCase().includes(this.searchBuilding.toLowerCase()));
                    }
                    return b;
                },

                get filteredCctvs() {
                    let c = this.allCctvs;
                    if (this.selectedBuildingId) {
                        c = c.filter(i => i.building_id == this.selectedBuildingId);
                    } else if (this.selectedFaculty) {
                        let validBuildingIds = this.allBuildings.filter(b => b.faculty === this.selectedFaculty).map(b => b.id);
                        c = c.filter(i => validBuildingIds.includes(i.building_id));
                    }
                    if (this.searchCctv) {
                        c = c.filter(i => i.name.toLowerCase().includes(this.searchCctv.toLowerCase()));
                    }
                    return c;
                },
                
                get sidebarCctvs() {
                    let c = this.filteredCctvs;
                    if (this.camSearch) {
                        c = c.filter(i => i.name.toLowerCase().includes(this.camSearch.toLowerCase()) || i.building_name.toLowerCase().includes(this.camSearch.toLowerCase()));
                    }
                    return c;
                },

                get selectedBuildingName() {
                    if (!this.selectedBuildingId) return '-- Semua Gedung --';
                    let b = this.allBuildings.find(i => i.id == this.selectedBuildingId);
                    return b ? b.name : '-- Semua Gedung --';
                },

                get selectedCctvName() {
                    if (!this.selectedCctvId) return '-- Pilih Kamera --';
                    let c = this.allCctvs.find(i => i.id == this.selectedCctvId);
                    return c ? c.name : '-- Pilih Kamera --';
                },

                selectFaculty(fac) {
                    this.selectedFaculty = fac;
                    this.selectedBuildingId = null;
                    this.updateUrl();
                },

                selectBuilding(id) {
                    this.selectedBuildingId = id;
                    this.showBuilding = false;
                    this.updateUrl();
                },

                selectCctv(id) {
                    this.selectedCctvId = id;
                    this.showCctv = false;
                    this.fetchRecordings();
                    this.updateUrl();
                },
                
                changeDate(newDate) {
                    this.date = newDate;
                    this.fetchRecordings();
                    this.updateUrl();
                },
                
                updateUrl() {
                    const url = new URL(window.location);
                    if (this.selectedFaculty) url.searchParams.set('faculty', this.selectedFaculty);
                    else url.searchParams.delete('faculty');
                    
                    if (this.selectedBuildingId) url.searchParams.set('building_id', this.selectedBuildingId);
                    else url.searchParams.delete('building_id');
                    
                    if (this.selectedCctvId) url.searchParams.set('cctv_id', this.selectedCctvId);
                    else url.searchParams.delete('cctv_id');
                    
                    url.searchParams.set('date', this.date);
                    window.history.pushState({}, '', url);
                },

                initApp() {
                    if (this.selectedCctvId) {
                        this.fetchRecordings();
                    }
                },

                fetchRecordings() {
                    if (!this.selectedCctvId) return;
                    window.dispatchEvent(new CustomEvent('load-recordings', { 
                        detail: { cctvId: this.selectedCctvId, date: this.date } 
                    }));
                }
            }
        }
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    </div> <!-- END ALPINE WRAPPER -->
</x-app-layout>