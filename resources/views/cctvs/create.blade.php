<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <a href="{{ route('cctv.index') }}" class="text-slate-500 hover:text-cyan-600">Kamera CCTV</a>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Tambah Baru</span>
            </div>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="glass-effect rounded-2xl p-8 border border-cyan-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-32 h-32 bg-gradient-to-br from-cyan-400/20 to-blue-400/20 rounded-full blur-2xl"></div>
                <h2 class="text-2xl font-bold text-slate-800 mb-6 relative z-10">Input Perangkat CCTV</h2>

                <form action="{{ route('cctv.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                    @csrf
                    
                    <div class="md:col-span-2" x-data="{ 
                        open: false, 
                        search: '', 
                        selectedId: '{{ old('building_id') }}',
                        selectedName: '{{ old('building_id') ? \App\Models\Building::find(old('building_id'))->nama_gedung : '' }}',
                        buildings: [
                            @foreach($buildings as $building)
                                { id: '{{ $building->id }}', name: '{{ $building->nama_gedung }}', fakultas: '{{ $building->fakultas }}', kode: '{{ $building->kode_gedung }}' },
                            @endforeach
                        ],
                        get filteredBuildings() {
                            if (this.search === '') return this.buildings;
                            return this.buildings.filter(b => 
                                b.name.toLowerCase().includes(this.search.toLowerCase()) || 
                                b.fakultas.toLowerCase().includes(this.search.toLowerCase())
                            );
                        },
                        selectBuilding(b) {
                            this.selectedId = b.id;
                            this.selectedName = b.name;
                            this.search = '';
                            this.open = false;
                            $nextTick(() => { detectPlacement(b.kode); });
                        },
                        toggle() {
                            this.open = !this.open;
                            if (this.open) {
                                $nextTick(() => { this.$refs.searchInput.focus(); });
                            }
                        },
                        close() {
                            this.open = false;
                            this.search = ''; // Reset search when closing without selection
                        }
                    }">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi Gedung</label>
                        <div class="relative">
                            <input type="hidden" name="building_id" :value="selectedId">
                            
                            <!-- Trigger Button (Gaya Dropdown) -->
                            <button type="button" @click="toggle()" 
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-left flex justify-between items-center focus:ring-2 focus:ring-cyan-200 focus:bg-white transition-all shadow-sm">
                                <span x-text="selectedName || 'Pilih Gedung...'" 
                                      :class="selectedName ? 'text-slate-800 font-bold' : 'text-slate-400 font-medium'"
                                      class="truncate text-sm"></span>
                                <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            
                            <!-- Dropdown Panel -->
                            <div x-show="open" 
                                 @click.away="close()" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-cloak 
                                 class="absolute z-50 w-full mt-2 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden shadow-cyan-500/10">
                                
                                <!-- Search Input (Hidden Typing Style) -->
                                <div class="p-3 border-b border-slate-50 bg-slate-50/50">
                                    <div class="relative">
                                        <input type="text" 
                                               x-ref="searchInput"
                                               x-model="search"
                                               placeholder="Ketik untuk mencari..." 
                                               class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-200 transition-all"
                                               @keydown.escape="close()"
                                               autocomplete="off">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-slate-300 text-xs"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- List Items -->
                                <div class="max-h-64 overflow-y-auto custom-scrollbar">
                                    <template x-for="b in filteredBuildings" :key="b.id">
                                        <button type="button" @click="selectBuilding(b)" 
                                                class="w-full px-4 py-3 text-left hover:bg-cyan-50 transition-colors flex flex-col border-b border-slate-50 last:border-none group">
                                            <span class="text-sm font-bold text-slate-700 group-hover:text-cyan-700" x-text="b.name"></span>
                                            <span class="text-[10px] text-slate-400 uppercase tracking-widest font-medium" x-text="b.fakultas"></span>
                                        </button>
                                    </template>
                                    
                                    <div x-show="filteredBuildings.length === 0" class="p-8 text-center">
                                        <i class="fas fa-search-minus mb-2 block text-2xl text-slate-200"></i>
                                        <p class="text-xs text-slate-400 italic">Gedung tidak ditemukan...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('building_id')
                            <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Penempatan</label>
                        <select name="penempatan" id="penempatan_select" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200" required>
                            <option value="Indoor" {{ old('penempatan') == 'Indoor' ? 'selected' : '' }}>Indoor</option>
                            <option value="Outdoor" {{ old('penempatan') == 'Outdoor' ? 'selected' : '' }}>Outdoor</option>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Otomatis terdeteksi berdasarkan kode gedung.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Kode CCTV</label>
                        <input type="text" name="kode_cctv" value="{{ old('kode_cctv') }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200" placeholder="CAM-001" required>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Kamera</label>
                        <input type="text" name="nama_cctv" value="{{ old('nama_cctv') }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200" placeholder="Lobi Utama" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Server Perekam (Node)</label>
                        <select name="server_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200">
                            <option value="">-- Master Server (Lokal) --</option>
                            @foreach($servers as $server)
                                <option value="{{ $server->id }}" {{ old('server_id') == $server->id ? 'selected' : '' }}>
                                    {{ $server->name }} ({{ $server->ip_address }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1">Pilih server fisik yang akan menangani kamera ini.</p>
                    </div>

                    <div class="md:col-span-2 pt-4 border-t border-slate-100" x-data="{ 
                        showMapModal: false,
                        map: null,
                        marker: null,
                        initMap() {
                            if (this.map) return;
                            
                            $nextTick(() => {
                                this.map = L.map('picker-map').setView([-6.9261, 107.7743], 16);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '© OpenStreetMap'
                                }).addTo(this.map);

                                // Load existing coordinates if any
                                let lat = document.getElementsByName('lat')[0].value;
                                let lng = document.getElementsByName('lng')[0].value;
                                if (lat && lng) {
                                    this.marker = L.marker([lat, lng]).addTo(this.map);
                                    this.map.setView([lat, lng], 18);
                                }

                                this.map.on('click', (e) => {
                                    if (this.marker) this.map.removeLayer(this.marker);
                                    this.marker = L.marker(e.latlng).addTo(this.map);
                                    
                                    document.getElementsByName('lat')[0].value = e.latlng.lat.toFixed(8);
                                    document.getElementsByName('lng')[0].value = e.latlng.lng.toFixed(8);
                                    
                                    // Beri feedback visual sebentar
                                    showToast('Koordinat dipilih: ' + e.latlng.lat.toFixed(4) + ', ' + e.latlng.lng.toFixed(4), 'success');
                                });

                                // Fix leaflet gray tiles on modal open
                                setTimeout(() => { this.map.invalidateSize(); }, 200);
                            });
                        }
                    }">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <i class="fas fa-map-marked-alt text-slate-400"></i> Koordinat Map (Outdoor)
                            </h3>
                            <button type="button" @click="showMapModal = true; initMap()" 
                                    class="px-3 py-1.5 rounded-lg bg-cyan-50 text-cyan-600 text-xs font-bold hover:bg-cyan-100 transition-all flex items-center gap-2 border border-cyan-200">
                                <i class="fas fa-location-dot"></i> Pilih dari Peta
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Latitude</label>
                                <input type="text" name="lat" value="{{ old('lat') }}" placeholder="-6.92610000" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200 font-mono text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Longitude</label>
                                <input type="text" name="lng" value="{{ old('lng') }}" placeholder="107.77430000" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200 font-mono text-sm">
                            </div>
                        </div>

                        <!-- Map Picker Modal -->
                        <div x-show="showMapModal" x-cloak class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div x-show="showMapModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showMapModal = false"></div>
                                
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                
                                <div x-show="showMapModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                                     class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-white/20">
                                    
                                    <div class="bg-white px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                            <i class="fas fa-crosshairs text-cyan-500"></i> Klik Peta untuk Plotting Kamera
                                        </h3>
                                        <button @click="showMapModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                                            <i class="fas fa-times text-lg"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="p-0">
                                        <div id="picker-map" style="height: 500px;" class="w-full"></div>
                                    </div>

                                    <div class="bg-slate-50 px-6 py-4 flex justify-between items-center">
                                        <p class="text-xs text-slate-500 italic">Gunakan scroll untuk zoom, klik pada area gedung untuk menentukan koordinat.</p>
                                        <button @click="showMapModal = false" class="px-6 py-2 bg-slate-800 text-white rounded-xl font-bold text-sm hover:bg-slate-700 transition-all">
                                            Selesai
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 pt-4 border-t border-slate-100 mt-2">
                        <h3 class="text-sm font-bold text-cyan-700 mb-4 uppercase tracking-wider">Bantuan Input Otomatis</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Merk Kamera</label>
                                <select id="brand_select" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-200 text-sm focus:ring-2 focus:ring-cyan-300" onchange="generateUrl()">
                                    <option value="manual">-- Manual / Lainnya --</option>
                                    <option value="hikvision">Hikvision / HiLook</option>
                                    <option value="dahua">Dahua / G-Lenz / SPC</option>
                                    <option value="uniview">Uniview (UNV)</option>
                                    <option value="axis">Axis Communications</option>
                                    <option value="panasonic">Panasonic / i-Pro</option> 
                                    <option value="onvif">Standard ONVIF</option>
                                </select>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">IP Address</label>
                                <input type="text" name="ip" id="ip_input" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-200 text-sm focus:ring-2 focus:ring-cyan-300" placeholder="192.168.1.64" oninput="generateUrl()">
                            </div>

                            <div class="flex space-x-2">
                                <div class="flex-1">
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Channel</label>
                                    <input type="number" id="channel_input" value="1" min="1" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-200 text-sm" oninput="generateUrl()">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Tipe</label>
                                    <select id="stream_type" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-200 text-sm" onchange="generateUrl()">
                                        <option value="sub">Sub (Ringan)</option>
                                        <option value="main">Main (HD)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 pt-4 border-t border-slate-100 mt-2">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-video text-slate-400"></i> Konfigurasi Stream
                        </h3>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">RTSP / HTTP URL</label>
                        <div class="flex gap-2">
                            <input type="text" name="rtsp_url" id="rtsp_url_input" 
                                   value="{{ old('rtsp_url') }}"
                                   class="flex-1 px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200 font-mono text-sm text-slate-600 focus:bg-white transition-all" 
                                   placeholder="rtsp://..." required>
                            
                            <button type="button" onclick="testConnection()" id="btn-test"
                                    class="px-5 py-2 rounded-xl bg-slate-800 text-white text-sm font-bold hover:bg-slate-700 transition-all flex items-center gap-2 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                                <i class="fas fa-plug"></i> Test
                            </button>
                        </div>
                        <p class="text-xs text-slate-400 mt-1 ml-1">Pastikan URL valid sebelum menyimpan.</p>
                    </div>

                    <div id="test-result" class="md:col-span-2 hidden transition-all duration-300">
                        <div class="p-4 rounded-xl border flex items-start gap-4" id="test-result-box">
                            <div class="w-32 h-20 bg-slate-200 rounded-lg overflow-hidden shrink-0 border border-slate-300 relative">
                                <img id="test-snapshot" src="" class="w-full h-full object-cover hidden">
                                <div id="test-loading" class="absolute inset-0 flex items-center justify-center text-slate-400 bg-slate-100">
                                    <i class="fas fa-spinner fa-spin text-2xl"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm mb-1" id="test-status-title">Testing...</h4>
                                <p class="text-xs text-slate-600 leading-relaxed" id="test-status-msg">Sedang menghubungi kamera...</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">RTSP User</label>
                        <input type="text" name="rtsp_user" id="rtsp_user_input" value="{{ old('rtsp_user') }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200 focus:bg-white transition-all" placeholder="admin">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">RTSP Password</label>
                        <input type="password" name="rtsp_password" id="rtsp_password_input" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200" placeholder="••••••">
                    </div>

                    <!-- ONVIF Configuration Section -->
                    <div class="md:col-span-2 pt-4 border-t border-slate-100 mt-4">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-robot text-slate-400"></i> Konfigurasi ONVIF (Event Intelligence)
                        </h3>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">ONVIF Port</label>
                        <input type="number" name="onvif_port" value="{{ old('onvif_port', 80) }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200" placeholder="80 atau 8000">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">ONVIF User</label>
                        <input type="text" name="onvif_user" value="{{ old('onvif_user') }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200" placeholder="admin">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">ONVIF Password</label>
                        <input type="password" name="onvif_password" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200" placeholder="••••••">
                        <p class="text-[10px] text-slate-400 mt-1">Gunakan password yang sama dengan RTSP jika tidak yakin.</p>
                    </div>

                    <div class="md:col-span-2 flex justify-end space-x-4 pt-6 border-t border-slate-100 mt-4">
                        <a href="{{ route('cctv.index') }}" class="px-6 py-2.5 rounded-xl text-slate-500 hover:bg-slate-100 font-medium transition-colors">Batal</a>
                        <button type="submit" class="px-8 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-bold shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 hover:-translate-y-0.5 transition-all duration-300">
                            Simpan Kamera
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    @push('scripts')
    <script>
        // 1. URL GENERATOR LOGIC
        function generateUrl() {
            const brand = document.getElementById('brand_select').value;
            const ip = document.getElementById('ip_input').value || '192.168.1.x';
            const channel = document.getElementById('channel_input').value || '1';
            const type = document.getElementById('stream_type').value; 
            const urlInput = document.getElementById('rtsp_url_input');

            if (brand === 'manual') return;

            let url = '';

            if (brand === 'hikvision') {
                let streamCode = (type === 'main') ? '01' : '02';
                url = `rtsp://${ip}:554/Streaming/Channels/${channel}${streamCode}`;
            } 
            else if (brand === 'dahua') {
                let subtype = (type === 'main') ? '0' : '1';
                url = `rtsp://${ip}:554/cam/realmonitor?channel=${channel}&subtype=${subtype}`;
            } 
            else if (brand === 'uniview') {
                let videoStream = (type === 'main') ? '1' : '2';
                url = `rtsp://${ip}:554/media/video${videoStream}`;
            }
            else if (brand === 'axis') {
                url = `rtsp://${ip}:554/axis-media/media.amp?videocodec=h264`;
                if (type === 'sub') url += '&resolution=640x480&compression=30';
                else url += '&resolution=1280x720';
                if (channel > 1) url += `&camera=${channel}`;
            }
            else if (brand === 'panasonic') {
                url = `rtsp://${ip}:554/cam/realmonitor?channel=${channel}&subtype=1`;
            }
            else if (brand === 'onvif') {
                url = `rtsp://${ip}:554/live/ch${channel}`;
            }

            urlInput.value = url;
        }

        function detectPlacement(kode = null) {
            let kodeGedung = kode;
            
            if (!kodeGedung) {
                const select = document.getElementById('building_select');
                if (select) {
                    const selectedOption = select.options[select.selectedIndex];
                    kodeGedung = selectedOption.getAttribute('data-kode') || '';
                }
            }

            const penempatanSelect = document.getElementById('penempatan_select');
            if (penempatanSelect && kodeGedung) {
                if (kodeGedung.startsWith('WM')) {
                    penempatanSelect.value = 'Indoor';
                } else {
                    penempatanSelect.value = 'Outdoor';
                }
            }
        }

        // 2. TEST CONNECTION LOGIC (AJAX)
        function testConnection() {
            const url = document.getElementById('rtsp_url_input').value;
            // AMBIL USER & PASS
            const user = document.getElementById('rtsp_user_input').value;
            const pass = document.getElementById('rtsp_password_input').value;
            const btn = document.getElementById('btn-test');
            const resultArea = document.getElementById('test-result');
            const resultBox = document.getElementById('test-result-box');
            const img = document.getElementById('test-snapshot');
            const loading = document.getElementById('test-loading');
            const title = document.getElementById('test-status-title');
            const msg = document.getElementById('test-status-msg');

            if (!url) { alert("Harap isi URL RTSP terlebih dahulu!"); return; }

            resultArea.classList.remove('hidden');
            resultBox.className = "p-4 rounded-xl border flex items-start gap-4 bg-slate-50 border-slate-200";
            img.classList.add('hidden');
            loading.classList.remove('hidden');
            title.innerText = "Menghubungkan...";
            title.className = "font-bold text-sm mb-1 text-slate-700";
            msg.innerText = "Mencoba mengambil snapshot dari kamera...";
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';

            fetch("{{ route('cctv.test') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                
                // KIRIM DATA LENGKAP
                body: JSON.stringify({ 
                    rtsp_url: url,
                    rtsp_user: user,
                    rtsp_password: pass
                })
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-plug"></i> Test';
                loading.classList.add('hidden');

                if (res.status === 200) {
                    resultBox.className = "p-4 rounded-xl border flex items-start gap-4 bg-green-50 border-green-200";
                    title.innerText = "Koneksi Berhasil!";
                    title.className = "font-bold text-sm mb-1 text-green-700";
                    msg.innerText = res.body.message;
                    if(res.body.snapshot_url) {
                        img.src = res.body.snapshot_url;
                        img.classList.remove('hidden');
                    }
                } else {
                    throw new Error(res.body.message || "Gagal terhubung.");
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-plug"></i> Test';
                loading.classList.add('hidden');
                resultBox.className = "p-4 rounded-xl border flex items-start gap-4 bg-red-50 border-red-200";
                title.innerText = "Koneksi Gagal";
                title.className = "font-bold text-sm mb-1 text-red-700";
                msg.innerText = error.message;
            });
        }
    </script>
    @endpush
</x-app-layout>