<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <title>SKYPAD Map Monitoring - Unpad</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('cctvCounts', { up: 0, down: 0 });
            Alpine.store('weather', { temp: '--', desc: 'Loading...', icon: 'fa-cloud' });
            Alpine.store('mapState', { editMode: false });
            Alpine.store('toast', {
                show: false,
                message: '',
                type: 'success',
                trigger(msg, type = 'success') {
                    this.show = true;
                    this.message = msg;
                    this.type = type;
                    setTimeout(() => { this.show = false; }, 3000);
                }
            });
        });
    </script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        #map { height: 100vh; width: 100%; z-index: 1; cursor: crosshair; }
        .custom-camera-marker {
            background: #f97316; color: white; border-radius: 50%; width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4); border: 2px solid white;
            transition: transform 0.2s; cursor: pointer;
        }
        .custom-camera-marker:hover { transform: scale(1.1); background: #ea580c; }
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

        #cctv-modal, #edit-coord-modal {
            position: absolute;
            z-index: 2000;
            pointer-events: auto;
        }
        
        #edit-coord-modal {
            width: 300px;
        }

        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        .animate-marquee {
            display: inline-block;
            white-space: nowrap;
            animation: marquee 35s linear infinite;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 overflow-hidden font-sans" x-data="{ sidebarOpen: true }">

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'w-80' : 'w-0 -ml-80'" class="fixed left-0 top-0 h-full bg-white border-r border-slate-200 z-[1001] flex flex-col transition-all duration-300 shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-gradient-to-br from-white to-orange-50/30">
            <div class="flex items-center gap-4">
                <div class="relative flex-shrink-0">
                    <div class="bg-orange-500 p-3 rounded-2xl shadow-lg shadow-orange-200 text-white relative z-10">
                        <i class="fa-solid fa-map-location-dot text-xl"></i>
                    </div>
                    <div class="absolute inset-0 bg-orange-400 rounded-2xl animate-ping opacity-75"></div>
                </div>
                <div>
                    <h1 class="font-extrabold text-xl tracking-tighter text-slate-950 leading-none">MAP VIEW</h1>
                    <p class="text-[10px] font-bold text-orange-600 uppercase tracking-widest mt-1.5 flex items-center gap-1.5">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                        </span>
                        Outdoor Sync
                    </p>
                </div>
            </div>
        </div>

        <div class="p-4 grid grid-cols-2 gap-3 border-b border-slate-100 text-center">
            <div class="bg-emerald-50 p-2.5 rounded-xl border border-emerald-100 text-emerald-700 font-bold text-[10px] uppercase shadow-inner tracking-tighter">
                Online: <span x-text="$store.cctvCounts.up" class="font-black text-xs ml-1"></span>
            </div>
            <div class="bg-rose-50 p-2.5 rounded-xl border border-rose-100 text-rose-700 font-bold text-[10px] uppercase shadow-inner tracking-tighter">
                Offline: <span x-text="$store.cctvCounts.down" class="font-black text-xs ml-1"></span>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-h-0">
            <div class="p-4">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="search-input" placeholder="Cari kamera..." class="w-full bg-slate-100 border border-slate-200 rounded-full py-2 pl-9 pr-4 text-sm focus:ring-2 focus:ring-orange-500 focus:bg-white outline-none transition-all shadow-inner">
                </div>
            </div>
            <div id="cctv-list" class="flex-1 overflow-y-auto px-2 custom-scrollbar space-y-1 pb-20"></div>
        </div>

        <!-- Back Button -->
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            <a href="{{ route('dashboard') }}" class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl bg-slate-800 text-white text-xs font-bold hover:bg-slate-700 transition-all">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>
    </aside>

    <main :class="sidebarOpen ? 'ml-80' : 'ml-0'" class="relative transition-all duration-300 h-screen overflow-hidden">
        
        <!-- Header Controls -->
        <header class="absolute top-4 left-4 right-4 z-[1000] flex justify-between items-start pointer-events-none gap-2">
            <div class="flex gap-3 pointer-events-auto">
                <button @click="sidebarOpen = !sidebarOpen" class="bg-white p-3.5 rounded-2xl shadow-lg border border-slate-200 text-slate-600 hover:text-orange-600 transition-all active:scale-95">
                    <i class="fa-solid" :class="sidebarOpen ? 'fa-indent' : 'fa-outdent'"></i>
                </button>
                
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="bg-white p-3.5 rounded-2xl shadow-lg border border-slate-200 text-slate-600 hover:text-orange-600 transition-all">
                        <i class="fa-solid fa-layer-group"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak class="absolute top-full mt-2 left-0 bg-white rounded-2xl shadow-2xl border border-slate-100 p-2 min-w-[120px] flex flex-col gap-1">
                        <button onclick="changeBaseLayer('light')" class="text-[10px] font-bold px-3 py-2 rounded-lg hover:bg-slate-50 text-left uppercase tracking-tighter">Street View</button>
                        <button onclick="changeBaseLayer('satellite')" class="text-[10px] font-bold px-3 py-2 rounded-lg hover:bg-slate-50 text-left uppercase tracking-tighter border-t border-slate-50">Satellite</button>
                        <button onclick="changeBaseLayer('dark')" class="text-[10px] font-bold px-3 py-2 rounded-lg hover:bg-slate-50 text-left uppercase tracking-tighter border-t border-slate-50">Dark Mode</button>
                    </div>
                </div>

                <!-- Campus Switcher -->
                <div class="flex bg-white/90 backdrop-blur-sm p-1 rounded-2xl shadow-lg border border-slate-200">
                    <button onclick="switchToCampus('jatinangor')" class="px-4 py-2 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-orange-500 hover:text-white transition-all campus-btn" id="btn-jatinangor">Jatinangor</button>
                    <button onclick="switchToCampus('dipatiukur')" class="px-4 py-2 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-orange-500 hover:text-white transition-all campus-btn" id="btn-dipatiukur">Dipati Ukur</button>
                </div>

                <!-- Admin Edit Mode Toggle -->
                @if(auth()->user()->role === 'admin')
                <button @click="$store.mapState.editMode = !$store.mapState.editMode" 
                        :class="$store.mapState.editMode ? 'bg-orange-500 text-white border-orange-600' : 'bg-white text-slate-600 border-slate-200'"
                        class="px-4 py-2 rounded-2xl shadow-lg border font-bold text-[10px] uppercase tracking-widest transition-all flex items-center gap-2">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                    <span x-text="$store.mapState.editMode ? 'Exit Edit Mode' : 'Admin Edit Mode'"></span>
                </button>
                @endif
            </div>
            
            <div class="flex items-center gap-3 pointer-events-auto">
                <div class="bg-white/90 backdrop-blur-md px-4 py-2 rounded-xl shadow-lg border border-slate-200 flex items-center gap-3">
                    <div class="text-orange-500 text-xl">
                        <i :class="'fa-solid ' + $store.weather.icon"></i>
                    </div>
                    <div class="flex flex-col border-l border-slate-100 pl-3 text-nowrap">
                        <span class="text-lg font-black text-slate-800 leading-none tracking-tighter"><span x-text="$store.weather.temp"></span>°C</span>
                        <span x-text="$store.weather.desc" class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-1"></span>
                    </div>
                </div>

                <div x-data="{ time: '', date: '' }" x-init="setInterval(() => { let d = new Date(); time = d.toLocaleTimeString('id-ID'); date = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); }, 1000)" class="bg-white/90 backdrop-blur-md px-5 py-2 rounded-xl shadow-lg border border-slate-200 flex flex-col items-end min-w-[160px]">
                    <span x-text="time" class="font-mono font-black text-xl text-slate-950 leading-none tracking-tighter"></span>
                    <span x-text="date" class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mt-1"></span>
                </div>
            </div>
        </header>

        <!-- Map Container -->
        <div id="map"></div>

        <!-- Video Modal -->
        <div id="cctv-modal" class="hidden opacity-0 scale-95 transition-all duration-200 pointer-events-auto origin-center w-[400px]">
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-200 flex flex-col shadow-orange-500/10">
                <div class="h-11 px-5 border-b border-slate-100 flex items-center justify-between bg-white shrink-0">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-pulse flex-shrink-0"></div>
                        <h2 class="text-xs font-extrabold text-slate-950 truncate uppercase tracking-tight" id="modal-title-text">Feed</h2>
                    </div>
                    <button onclick="closeModal()" class="text-slate-400 hover:text-rose-500 p-1.5">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div id="video-container" class="bg-black relative aspect-video w-full flex items-center justify-center group">
                    <div id="video-loader" class="absolute inset-0 z-10 flex flex-col items-center justify-center text-orange-500 bg-black/70 backdrop-blur-sm">
                        <i class="fa-solid fa-circle-notch fa-spin text-3xl mb-1.5"></i>
                        <span class="text-[9px] font-bold uppercase tracking-widest text-orange-300">SkySync Active</span>
                    </div>
                    
                    <video id="hls-player" class="w-full h-full object-contain hidden" autoplay muted playsinline></video>
                    <iframe id="iframe-player" class="w-full h-full hidden" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                    
                    <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex justify-between items-center z-20">
                        <div class="flex gap-2">
                            <button onclick="togglePlay()" class="text-white/80 hover:text-white"><i class="fa-solid fa-play"></i></button>
                            <button onclick="toggleFullScreen()" class="text-white/80 hover:text-white"><i class="fa-solid fa-expand"></i></button>
                        </div>
                        <span class="bg-orange-500 text-white text-[8px] font-black px-1.5 py-0.5 rounded uppercase">LIVE</span>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 flex justify-between items-center border-t border-slate-100">
                    <div class="flex flex-col overflow-hidden max-w-[60%]">
                        <span class="text-[8px] text-orange-600 font-bold uppercase tracking-widest leading-none flex items-center gap-1">
                            <i class="fa-solid fa-location-dot"></i> Lokasi
                        </span>
                        <p id="modal-location-text" class="text-[10px] font-black text-slate-800 truncate mt-1.5">Universitas Padjadjaran</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(auth()->user()->role === 'admin')
                        <button id="modal-edit-btn" onclick="" class="bg-slate-200 text-slate-700 px-2.5 py-1.5 rounded-lg text-[9px] font-extrabold uppercase tracking-tight hover:bg-orange-500 hover:text-white transition-all flex items-center gap-1.5">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </button>
                        @endif
                        <span class="bg-emerald-100 text-emerald-800 px-2.5 py-1.5 rounded-lg text-[9px] font-extrabold uppercase tracking-tight flex items-center gap-1.5">
                            <i class="fa-solid fa-signal text-[8px]"></i> Online
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Coordinate Modal -->
        @if(auth()->user()->role === 'admin')
        <div id="edit-coord-modal" class="hidden opacity-0 scale-95 transition-all duration-200 pointer-events-auto origin-center">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-200 flex flex-col">
                <div class="h-10 px-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Update Coordinates</span>
                    <button onclick="closeEditModal()" class="text-slate-400 hover:text-rose-500"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="p-4 space-y-3">
                    <div>
                        <label class="block text-[8px] font-bold text-slate-400 uppercase mb-1">Latitude</label>
                        <input type="text" id="edit-lat" class="w-full bg-slate-100 border border-slate-200 rounded-lg py-1.5 px-3 text-[10px] font-mono focus:ring-1 focus:ring-orange-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[8px] font-bold text-slate-400 uppercase mb-1">Longitude</label>
                        <input type="text" id="edit-lng" class="w-full bg-slate-100 border border-slate-200 rounded-lg py-1.5 px-3 text-[10px] font-mono focus:ring-1 focus:ring-orange-500 outline-none">
                    </div>
                    <input type="hidden" id="edit-id">
                    <button onclick="saveCoordinates()" id="btn-save-coords" class="w-full bg-orange-500 text-white py-2 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-orange-600 transition-all shadow-lg shadow-orange-200">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Toast Notification -->
        <div x-data x-show="$store.toast.show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-10"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-10"
             x-cloak
             class="fixed bottom-12 left-1/2 -translate-x-1/2 z-[3000] pointer-events-none">
            <div :class="$store.toast.type === 'success' ? 'bg-slate-900 text-white shadow-orange-500/20' : 'bg-rose-600 text-white shadow-rose-500/20'"
                 class="px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-white/10 backdrop-blur-md">
                <div :class="$store.toast.type === 'success' ? 'bg-orange-500' : 'bg-white/20'" class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0">
                    <i :class="$store.toast.type === 'success' ? 'fa-solid fa-check text-white' : 'fa-solid fa-triangle-exclamation text-white'"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-black uppercase tracking-widest opacity-50" x-text="$store.toast.type === 'success' ? 'Success' : 'Attention'"></span>
                    <p class="text-xs font-bold" x-text="$store.toast.message"></p>
                </div>
            </div>
        </div>

        <footer class="fixed bottom-0 right-0 left-0 z-[1000] transition-all duration-300 shadow-[0_-4px_15px_rgba(249,115,22,0.15)]" :class="sidebarOpen ? 'ml-80' : 'ml-0'">
            <div class="bg-slate-950 border-t border-orange-600 text-white/90 h-9 flex items-center overflow-hidden">
                <div class="animate-marquee px-4 text-[10px] font-medium tracking-wide uppercase">
                    SkyPad System &copy; INFRA Direktorat Perencanaan Sistem Informasi Transformasi Digital Universitas Padjadjaran 2026
                </div>
            </div>
        </footer>
    </main>

    <script>
        const campusCoords = {
            jatinangor: [-6.9261, 107.7743],
            dipatiukur: [-6.8925, 107.6186]
        };
        
        const map = L.map('map', { zoomControl: false }).setView(campusCoords.jatinangor, 15);

        const baseLayers = {
            light: L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png'),
            dark: L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'),
            satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}')
        };
        baseLayers.light.addTo(map);

        function changeBaseLayer(type) {
            Object.values(baseLayers).forEach(l => map.removeLayer(l));
            baseLayers[type].addTo(map);
        }

        function switchToCampus(campus) {
            map.flyTo(campusCoords[campus], 16);
            document.querySelectorAll('.campus-btn').forEach(btn => btn.classList.remove('bg-orange-500', 'text-white'));
            document.getElementById('btn-' + campus).classList.add('bg-orange-500', 'text-white');
        }
        switchToCampus('jatinangor');

        // Map Click Listener (Coordinate Inspector)
        map.on('click', (e) => {
            const editMode = Alpine.store('mapState').editMode;
            if (editMode) {
                const lat = e.latlng.lat.toFixed(8);
                const lng = e.latlng.lng.toFixed(8);
                
                L.popup()
                    .setLatLng(e.latlng)
                    .setContent(`
                        <div class="p-2 min-w-[150px]">
                            <p class="text-[9px] font-bold text-orange-600 uppercase tracking-widest mb-2 flex items-center gap-1">
                                <i class="fa-solid fa-crosshairs"></i> Map Inspector
                            </p>
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between bg-slate-100 p-1.5 rounded-lg border border-slate-200">
                                    <span class="text-[9px] font-mono text-slate-500">LAT: ${lat}</span>
                                    <button onclick="copyToClipboard('${lat}')" class="text-orange-500 hover:text-orange-700 p-0.5"><i class="fa-solid fa-copy text-[10px]"></i></button>
                                </div>
                                <div class="flex items-center justify-between bg-slate-100 p-1.5 rounded-lg border border-slate-200">
                                    <span class="text-[9px] font-mono text-slate-500">LNG: ${lng}</span>
                                    <button onclick="copyToClipboard('${lng}')" class="text-orange-500 hover:text-orange-700 p-0.5"><i class="fa-solid fa-copy text-[10px]"></i></button>
                                </div>
                                <div class="pt-1 text-[8px] text-slate-400 italic">Klik koordinat untuk menyalin.</div>
                            </div>
                        </div>
                    `)
                    .openOn(map);
            } else {
                if (!modal.classList.contains('hidden')) closeModal();
                closeEditModal();
            }
        });

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                Alpine.store('toast').trigger('Koordinat disalin ke clipboard!');
            });
        }

        L.control.zoom({ position: 'bottomright' }).addTo(map);

        async function fetchWeather() {
            try {
                const res = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${campusCoords.jatinangor[0]}&longitude=${campusCoords.jatinangor[1]}&current_weather=true`);
                const data = await res.json();
                const cw = data.current_weather;
                const store = Alpine.store('weather');
                store.temp = Math.round(cw.temperature);
                const codes = { 0: ['Cerah', 'fa-sun'], 1: ['Berawan', 'fa-cloud-sun'], 2: ['Berawan', 'fa-cloud'], 3: ['Mendung', 'fa-cloud'], 61: ['Hujan', 'fa-cloud-rain'] };
                const info = codes[cw.weathercode] || ['Berawan', 'fa-cloud'];
                store.desc = info[0]; store.icon = info[1];
            } catch (e) { console.error("Weather Error", e); }
        }
        fetchWeather();

        let allCameras = [];
        let markersMap = new Map();
        let hlsInstance = null;

        const modal = document.getElementById('cctv-modal');
        const hlsPlayer = document.getElementById('hls-player');
        const iframePlayer = document.getElementById('iframe-player');
        const videoLoader = document.getElementById('video-loader');
        const editBtn = document.getElementById('modal-edit-btn');

        // Fetch Data CCTV dari internal API
        fetch('{{ route("api.map.cctvs") }}').then(res => res.json()).then(data => {
            allCameras = data;
            const online = allCameras.filter(c => c.status === 'online').length;
            Alpine.store('cctvCounts').up = online;
            Alpine.store('cctvCounts').down = allCameras.length - online;
            renderUI(allCameras);
        });

        function renderUI(cameras) {
            // Bersihkan marker lama
            markersMap.forEach(m => map.removeLayer(m));
            markersMap.clear();

            const list = document.getElementById('cctv-list');
            list.innerHTML = '';

            cameras.forEach(camera => {
                const marker = L.marker([camera.lat, camera.lng], {
                    icon: L.divIcon({
                        className: 'bg-transparent',
                        html: `<div class="custom-camera-marker shadow-lg shadow-orange-500/20"><i class="fa-solid fa-video text-[10px]"></i></div>`,
                        iconSize: [32, 32], iconAnchor: [16, 16]
                    })
                }).addTo(map);

                marker.bindTooltip(`<div class="p-1.5"><p class="text-xs font-extrabold text-orange-600 tracking-tight">${camera.name}</p><p class="text-[9px] font-medium text-slate-600 mt-1">${camera.building}</p></div>`, { direction: 'top', offset: [0, -15] });
                
                markersMap.set(camera.id, marker);
                
                marker.on('click', (e) => {
                    L.DomEvent.stopPropagation(e);
                    openModal(camera, e);
                });

                const isAdmin = @json(auth()->user()->role === 'admin');

                const item = document.createElement('div');
                item.className = 'group p-3 hover:bg-orange-50 rounded-2xl cursor-pointer border border-transparent hover:border-orange-100 mb-1 transition-all mx-1 flex items-center gap-3';
                item.innerHTML = `
                    <div class="w-9 h-9 flex-shrink-0 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-orange-500 group-hover:text-white transition-all group-hover:scale-105 group-hover:shadow-md group-hover:shadow-orange-100"><i class="fa-solid fa-video text-xs"></i></div>
                    <div class="flex-1 min-w-0" onclick="focusCamera('${camera.id}')">
                        <h4 class="text-xs font-bold text-slate-700 truncate group-hover:text-orange-800 tracking-tight">${camera.name}</h4>
                        <p class="text-[9px] font-medium text-slate-400 truncate group-hover:text-orange-600 mt-0.5">${camera.building}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        ${isAdmin ? `<button onclick="event.stopPropagation(); openEditCoordModal(${JSON.stringify(camera).replace(/"/g, '&quot;')})" class="hidden group-hover:flex w-7 h-7 bg-white border border-slate-200 rounded-lg items-center justify-center text-slate-400 hover:text-orange-500 hover:border-orange-200 transition-all"><i class="fa-solid fa-pen-to-square text-[10px]"></i></button>` : ''}
                        <span class="relative flex h-2 w-2 flex-shrink-0 ml-1">
                            <span class="${camera.status !== 'online' ? 'bg-rose-400' : 'bg-emerald-400 animate-pulse'} absolute inline-flex h-full w-full rounded-full opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 ${camera.status !== 'online' ? 'bg-rose-500' : 'bg-emerald-500'}"></span>
                        </span>
                    </div>`;
                list.appendChild(item);
            });
        }

        function focusCamera(id) {
            const camera = allCameras.find(c => c.id == id);
            if (camera) {
                map.flyTo([camera.lat, camera.lng], 18);
                setTimeout(() => {
                    const point = map.latLngToContainerPoint([camera.lat, camera.lng]);
                    openModal(camera, { containerPoint: point });
                }, 500);
            }
        }

        function openModal(camera, event) {
            document.getElementById('modal-title-text').innerText = camera.name;
            document.getElementById('modal-location-text').innerText = camera.building;
            
            if (editBtn) {
                editBtn.onclick = (e) => {
                    e.stopPropagation();
                    closeModal();
                    openEditCoordModal(camera);
                };
            }

            // Perhitungan Posisi Agar Tetap di Tengah/Dalam Layar
            const point = event.containerPoint; 
            const modalWidth = 400;
            const modalHeight = 350;
            
            let top = point.y - (modalHeight / 2); 
            let left = point.x + 40;

            // Proteksi agar tidak keluar kanan
            if (left + modalWidth > window.innerWidth) {
                left = point.x - modalWidth - 40;
            }
            
            // Proteksi agar tidak keluar kiri
            if (left < 10) left = 10;

            // Proteksi agar tidak keluar bawah
            if (top + modalHeight > window.innerHeight) {
                top = window.innerHeight - modalHeight - 20;
            }
            
            // Proteksi agar tidak keluar atas
            if (top < 20) top = 20;

            modal.style.top = `${top}px`;
            modal.style.left = `${left}px`;
            
            modal.classList.remove('hidden');
            setTimeout(() => { modal.classList.add('opacity-100', 'scale-100'); }, 10);

            stopVideo();
            videoLoader.classList.remove('hidden');

            let streamUrl = camera.stream_url;
            
            if (streamUrl.includes('stream.html') || streamUrl.includes('iframe')) {
                iframePlayer.src = streamUrl;
                iframePlayer.classList.remove('hidden');
                hlsPlayer.classList.add('hidden');
                iframePlayer.onload = () => videoLoader.classList.add('hidden');
            } else {
                hlsPlayer.classList.remove('hidden');
                iframePlayer.classList.add('hidden');
                if (Hls.isSupported()) {
                    if (hlsInstance) hlsInstance.destroy();
                    hlsInstance = new Hls();
                    hlsInstance.loadSource(streamUrl);
                    hlsInstance.attachMedia(hlsPlayer);
                    hlsInstance.on(Hls.Events.MANIFEST_PARSED, () => {
                        videoLoader.classList.add('hidden');
                        hlsPlayer.play().catch(e => console.warn(e));
                    });
                } else if (hlsPlayer.canPlayType('application/vnd.apple.mpegurl')) {
                    hlsPlayer.src = streamUrl;
                    hlsPlayer.onloadedmetadata = () => { videoLoader.classList.add('hidden'); hlsPlayer.play(); };
                }
            }
        }

        const editCoordModal = document.getElementById('edit-coord-modal');
        function openEditCoordModal(camera) {
            document.getElementById('edit-lat').value = camera.lat;
            document.getElementById('edit-lng').value = camera.lng;
            document.getElementById('edit-id').value = camera.id;

            // Posisikan modal di tengah layar
            editCoordModal.style.top = '50%';
            editCoordModal.style.left = '50%';
            editCoordModal.style.transform = 'translate(-50%, -50%)';
            
            editCoordModal.classList.remove('hidden');
            setTimeout(() => { editCoordModal.classList.add('opacity-100', 'scale-100'); }, 10);
        }

        function closeEditModal() {
            if (editCoordModal) {
                editCoordModal.classList.remove('opacity-100', 'scale-100');
                setTimeout(() => { editCoordModal.classList.add('hidden'); }, 200);
            }
        }

        async function saveCoordinates() {
            const id = document.getElementById('edit-id').value;
            const lat = document.getElementById('edit-lat').value;
            const lng = document.getElementById('edit-lng').value;
            const btn = document.getElementById('btn-save-coords');

            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

            try {
                const res = await fetch('{{ route("api.map.update-coords") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id, lat, lng })
                });

                const data = await res.json();
                if (res.ok) {
                    Alpine.store('toast').trigger('Koordinat berhasil diperbarui!');
                    setTimeout(() => { location.reload(); }, 1500); 
                } else {
                    Alpine.store('toast').trigger('Gagal: ' + (data.error || 'Terjadi kesalahan'), 'error');
                }
            } catch (e) {
                Alpine.store('toast').trigger('Gagal menghubungi server.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Simpan Perubahan';
            }
        }

        function closeModal() {
            modal.classList.remove('opacity-100', 'scale-100');
            setTimeout(() => { modal.classList.add('hidden'); stopVideo(); }, 200);
        }

        function stopVideo() {
            if (hlsInstance) { hlsInstance.destroy(); hlsInstance = null; }
            hlsPlayer.pause();
            hlsPlayer.removeAttribute('src');
            hlsPlayer.load();
            iframePlayer.src = '';
            hlsPlayer.classList.add('hidden');
            iframePlayer.classList.add('hidden');
        }

        function togglePlay() {
            if(!hlsPlayer.classList.contains('hidden')) {
                if (hlsPlayer.paused) hlsPlayer.play(); else hlsPlayer.pause();
            }
        }
        function toggleFullScreen() {
            const container = document.getElementById('video-container');
            if (container.requestFullscreen) container.requestFullscreen();
        }

        document.getElementById('search-input').addEventListener('input', (e) => {
            const val = e.target.value.toLowerCase();
            const filtered = allCameras.filter(c => c.name.toLowerCase().includes(val) || (c.building && c.building.toLowerCase().includes(val)));
            renderUI(filtered);
        });

        document.addEventListener('keydown', (e) => { 
            if (e.key === 'Escape') {
                closeModal();
                closeEditModal();
            }
        });
        map.on('mousedown', () => { 
            if (!modal.classList.contains('hidden')) closeModal();
            closeEditModal();
        });
    </script>
</body>
</html>
