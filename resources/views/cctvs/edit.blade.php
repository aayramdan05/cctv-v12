<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <a href="{{ route('cctv.index') }}" class="text-slate-500 hover:text-cyan-600">Kamera CCTV</a>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Edit Kamera</span>
            </div>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="glass-effect rounded-2xl p-8 border border-cyan-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-32 h-32 bg-gradient-to-br from-cyan-400/20 to-blue-400/20 rounded-full blur-2xl"></div>
                
                <div class="relative z-10 flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-slate-800">Edit: {{ $cctv->nama_cctv }}</h2>
                    
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase 
                        {{ $cctv->status == 'online' ? 'bg-green-100 text-green-700' : 
                          ($cctv->status == 'offline' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                        {{ $cctv->status }}
                    </span>
                </div>

                <form action="{{ route('cctv.update', $cctv->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                    @csrf
                    @method('PUT') <div class="md:col-span-2 bg-cyan-50/50 p-4 rounded-xl border border-cyan-100">
                        <h3 class="text-sm font-bold text-cyan-700 mb-4 uppercase tracking-wider flex justify-between">
                            <span>Generator URL (Gunakan jika ingin reset URL)</span>
                            <button type="button" onclick="generateUrl()" class="text-xs bg-white px-2 py-1 rounded shadow text-cyan-600 hover:text-cyan-800">Generate Ulang</button>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">Merk</label>
                                <select id="brand_select" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-200 text-sm focus:ring-2 focus:ring-cyan-300">
                                    <option value="manual">-- Jangan Ubah URL --</option>
                                    <option value="hikvision">Hikvision / HiLook</option>
                                    <option value="dahua">Dahua / G-Lenz / SPC</option>
                                    <option value="uniview">Uniview (UNV)</option>
                                    <option value="axis">Axis Communications</option>
                                    <option value="panasonic">Panasonic / i-Pro</option>
                                    <option value="onvif">Standard ONVIF</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">IP Address</label>
                                <input type="text" id="ip_input" value="{{ $cctv->ip }}" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-200 text-sm focus:ring-2 focus:ring-cyan-300">
                            </div>
                            <div class="flex space-x-2">
                                <div class="flex-1">
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Channel</label>
                                    <input type="number" id="channel_input" value="1" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-200 text-sm">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs font-bold text-slate-500 mb-1">Tipe</label>
                                    <select id="stream_type" class="w-full px-3 py-2 rounded-lg bg-white border border-slate-200 text-sm">
                                        <option value="sub">Sub</option>
                                        <option value="main">Main</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2" x-data="{ 
                        open: false, 
                        search: '', 
                        selectedId: '{{ old('building_id', $cctv->building_id) }}',
                        selectedName: '{{ $cctv->building->nama_gedung }}',
                        buildings: [
                            @foreach($buildings as $building)
                                { id: '{{ $building->id }}', name: '{{ $building->nama_gedung }}', fakultas: '{{ $building->fakultas }}', kode: '{{ $building->kode_gedung }}' },
                            @endforeach
                        ],
                        get filteredBuildings() {
                            if (this.search === '') return this.buildings;
                            return this.buildings.filter(b => b.name.toLowerCase().includes(this.search.toLowerCase()) || b.fakultas.toLowerCase().includes(this.search.toLowerCase()));
                        },
                        selectBuilding(b) {
                            this.selectedId = b.id;
                            this.selectedName = b.name;
                            this.open = false;
                            this.search = '';
                            $nextTick(() => { detectPlacement(b.kode); });
                        }
                    }">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi Gedung (Searchable)</label>
                        <div class="relative">
                            <input type="hidden" name="building_id" :value="selectedId">
                            <button type="button" @click="open = !open" 
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-left flex justify-between items-center focus:ring-2 focus:ring-cyan-200">
                                <span x-text="selectedName || 'Pilih Gedung...'" class="truncate"></span>
                                <i class="fas fa-chevron-down text-xs text-slate-400"></i>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-cloak 
                                 class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden">
                                <div class="p-2 border-b border-slate-100 bg-slate-50">
                                    <input type="text" x-model="search" placeholder="Ketik nama gedung atau fakultas..." 
                                           class="w-full px-3 py-2 text-sm rounded-lg border-slate-200 focus:ring-2 focus:ring-cyan-200">
                                </div>
                                <div class="max-h-60 overflow-y-auto custom-scrollbar">
                                    <template x-for="b in filteredBuildings" :key="b.id">
                                        <button type="button" @click="selectBuilding(b)" 
                                                class="w-full px-4 py-2.5 text-left text-sm hover:bg-cyan-50 transition-colors flex flex-col">
                                            <span class="font-bold text-slate-700" x-text="b.name"></span>
                                            <span class="text-[10px] text-slate-400 uppercase tracking-wider" x-text="b.fakultas"></span>
                                        </button>
                                    </template>
                                    <div x-show="filteredBuildings.length === 0" class="p-4 text-center text-xs text-slate-400 italic">
                                        Gedung tidak ditemukan...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Penempatan</label>
                        <select name="penempatan" id="penempatan_select" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200" required>
                            <option value="Indoor" {{ old('penempatan', $cctv->penempatan) == 'Indoor' ? 'selected' : '' }}>Indoor</option>
                            <option value="Outdoor" {{ old('penempatan', $cctv->penempatan) == 'Outdoor' ? 'selected' : '' }}>Outdoor</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Kode CCTV</label>
                        <input type="text" name="kode_cctv" value="{{ old('kode_cctv', $cctv->kode_cctv) }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Kamera</label>
                        <input type="text" name="nama_cctv" value="{{ old('nama_cctv', $cctv->nama_cctv) }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200" required>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Server Perekam (Node)</label>
                        <select name="server_id" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200">
                            <option value="">-- Master Server (Lokal) --</option>
                            @foreach($servers as $server)
                                <option value="{{ $server->id }}" 
                                    {{ old('server_id', $cctv->server_id) == $server->id ? 'selected' : '' }}>
                                    {{ $server->name }} ({{ $server->ip_address }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1">Pilih server fisik yang akan menangani kamera ini.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Status Sistem</label>
                        <select name="status" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200">
                            <option value="online" {{ old('status', $cctv->status) == 'online' ? 'selected' : '' }}>🟢 Online</option>
                            <option value="offline" {{ old('status', $cctv->status) == 'offline' ? 'selected' : '' }}>🔴 Offline</option>
                            <option value="maintenance" {{ old('status', $cctv->status) == 'maintenance' ? 'selected' : '' }}>🟠 Maintenance</option>
                        </select>
                        <p class="text-xs text-slate-400 mt-1">Status akan terupdate otomatis oleh sistem setiap 5 menit.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">IP Address</label>
                        <input type="text" name="ip" value="{{ old('ip', $cctv->ip) }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200">
                    </div>

                    <div class="md:col-span-2 pt-4 border-t border-slate-100">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-map-marked-alt text-slate-400"></i> Koordinat Map (Outdoor Monitoring)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Latitude</label>
                                <input type="text" name="lat" value="{{ old('lat', $cctv->lat) }}" placeholder="-6.92610000" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Longitude</label>
                                <input type="text" name="lng" value="{{ old('lng', $cctv->lng) }}" placeholder="107.77430000" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200">
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-2 italic">Dapatkan koordinat dari Google Maps (Klik kanan di peta > Pilih koordinat).</p>
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
                                   value="{{ old('rtsp_url', $cctv->rtsp_url) }}"
                                   class="flex-1 px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200 font-mono text-sm text-slate-600 focus:bg-white transition-all" required>
                            
                            <button type="button" onclick="testConnection()" id="btn-test"
                                    class="px-5 py-2 rounded-xl bg-slate-800 text-white text-sm font-bold hover:bg-slate-700 transition-all flex items-center gap-2 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                                <i class="fas fa-plug"></i> Test
                            </button>
                        </div>
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
                        <input type="text" name="rtsp_user" id="rtsp_user_input" value="{{ old('rtsp_user', $cctv->rtsp_user) }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">RTSP Password</label>
                        <input type="password" name="rtsp_password" id="rtsp_password_input" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200 placeholder-slate-400" placeholder="(Biarkan kosong jika tidak ingin mengubah)">
                        <p class="text-xs text-slate-400 mt-1">Isi hanya jika ingin mengganti password.</p>
                    </div>

                    <!-- ONVIF Configuration Section -->
                    <div class="md:col-span-2 pt-4 border-t border-slate-100 mt-4">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-robot text-slate-400"></i> Konfigurasi ONVIF (Event Intelligence)
                        </h3>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">ONVIF Port</label>
                        <input type="number" name="onvif_port" value="{{ old('onvif_port', $cctv->onvif_port ?? 80) }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200" placeholder="80 atau 8000">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">ONVIF User</label>
                        <input type="text" name="onvif_user" value="{{ old('onvif_user', $cctv->onvif_user) }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200" placeholder="admin">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">ONVIF Password</label>
                        <input type="password" name="onvif_password" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-cyan-200 placeholder-slate-400" placeholder="(Biarkan kosong jika tidak ingin mengubah)">
                        <p class="text-[10px] text-slate-400 mt-1">Isi hanya jika ingin mengganti password ONVIF.</p>
                    </div>

                    <div class="md:col-span-2 flex justify-end space-x-4 pt-6 border-t border-slate-100 mt-4">
                        <a href="{{ route('cctv.index') }}" class="px-6 py-2.5 rounded-xl text-slate-500 hover:bg-slate-100 font-medium transition-colors">Batal</a>
                        <button type="submit" class="px-8 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-bold shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 hover:-translate-y-0.5 transition-all duration-300">
                            Update Kamera
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    @push('scripts')
    <script>
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
            } else if (brand === 'dahua') {
                let subtype = (type === 'main') ? '0' : '1';
                url = `rtsp://${ip}:554/cam/realmonitor?channel=${channel}&subtype=${subtype}`;
            } else if (brand === 'uniview') {
                let videoStream = (type === 'main') ? '1' : '2';
                url = `rtsp://${ip}:554/media/video${videoStream}`;
            } else if (brand === 'axis') {
                url = `rtsp://${ip}:554/axis-media/media.amp?videocodec=h264`;
                if (type === 'sub') url += '&resolution=640x480&compression=30';
                else url += '&resolution=1280x720';
                if (channel > 1) url += `&camera=${channel}`;
            } else if (brand === 'panasonic') {
                let subtype = (type === 'main') ? '0' : '1';
                url = `rtsp://${ip}:554/cam/realmonitor?channel=${channel}&subtype=${subtype}`;
            } else if (brand === 'onvif') {
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