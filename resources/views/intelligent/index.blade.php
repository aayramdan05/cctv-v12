<x-app-layout>
    <div x-data="intelligentApp()" class="pt-20 p-4 md:p-8 min-h-screen bg-slate-50/70 relative">
        <!-- Ambient Background Glows -->
        <div class="fixed top-20 right-10 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="fixed bottom-10 left-10 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto relative z-10 space-y-6">

            <!-- 1. Header Section -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white/90 backdrop-blur-md p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-purple-500/20">
                            <i class="fas fa-brain text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                                Intelligent AI Video Analytics
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-700 border border-purple-200">v2.0 Beta</span>
                            </h1>
                            <p class="text-sm text-slate-500 font-medium">Pelacakan Objek, Deteksi Kehilangan Barang, & Linimasa Narasi Berbasis YOLO + Ollama</p>
                        </div>
                    </div>
                </div>

                <!-- System Engine Badges -->
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        YOLOv8 Active
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200 shadow-xs">
                        <i class="fas fa-robot text-purple-500"></i>
                        Ollama Narrative
                    </span>
                    <button @click="showPoCModal = true" 
                            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-slate-900 text-white hover:bg-slate-800 transition-all shadow-sm">
                        <i class="fas fa-terminal text-cyan-400"></i>
                        Panduan PoC AI
                    </button>
                </div>
            </div>

            <!-- 2. Statistics Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card Total -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Deteksi</span>
                        <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                            <i class="fas fa-list-check"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-800" x-text="stats.total"></span>
                        <span class="text-xs text-slate-500">event tercatat</span>
                    </div>
                </div>

                <!-- Card Barang Hilang (Alert) -->
                <div class="bg-white rounded-2xl p-5 border border-rose-200 shadow-xs hover:shadow-md transition-all relative overflow-hidden group">
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-rose-500/10 rounded-full group-hover:scale-125 transition-transform"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-rose-600 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                            Barang Hilang / Berpindah
                        </span>
                        <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                            <i class="fas fa-box-open"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-rose-600" x-text="stats.missing_items"></span>
                        <span class="text-xs font-bold text-rose-600">Perlu Perhatian</span>
                    </div>
                </div>

                <!-- Card Kendaraan -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Lalu Lintas Kendaraan</span>
                        <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                            <i class="fas fa-car-side"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-800" x-text="stats.vehicles"></span>
                        <span class="text-xs text-slate-500">mobil/motor</span>
                    </div>
                </div>

                <!-- Card Pejalan Kaki -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Deteksi Orang</span>
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <i class="fas fa-person-walking"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-800" x-text="stats.persons"></span>
                        <span class="text-xs text-slate-500">individu teramati</span>
                    </div>
                </div>
            </div>

            <!-- 3. AI Executive Narrative Summary -->
            <div class="bg-gradient-to-r from-purple-900 via-indigo-900 to-slate-900 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                <div class="absolute right-0 top-0 translate-x-12 -translate-y-12 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/30 border border-purple-400/30 flex items-center justify-center text-purple-200 flex-shrink-0 mt-0.5">
                            <i class="fas fa-wand-magic-sparkles text-lg"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-white tracking-wide">Ringkasan Otomatis AI (Ollama Narrator)</h3>
                                <span class="text-[10px] uppercase font-bold tracking-widest px-2 py-0.5 rounded bg-purple-500/30 text-purple-200 border border-purple-400/20" x-text="summarySource"></span>
                            </div>
                            <p class="text-sm text-slate-300 mt-1.5 leading-relaxed max-w-4xl" x-text="summaryText"></p>
                        </div>
                    </div>

                    <div class="flex-shrink-0">
                        <button type="button" @click="refreshSummary()" 
                                :disabled="isGeneratingSummary"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 border border-white/20 text-white transition-all shadow-sm disabled:opacity-50">
                            <i class="fas fa-rotate" :class="{'fa-spin': isGeneratingSummary}"></i>
                            <span x-text="isGeneratingSummary ? 'Menganalisis...' : 'Perbarui Ringkasan'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 4. Filter Toolbar & Smart Search Bar -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <!-- Search Input -->
                    <div class="md:col-span-5 relative">
                        <i class="fas fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                        <input type="text" x-model="searchQuery" @input.debounce.300ms="applyFilters()"
                               placeholder="Cari kejadian (misal: laptop, mobil merah, ransel, lab)..." 
                               class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-slate-800 placeholder-slate-400 font-medium">
                    </div>

                    <!-- Camera Dropdown -->
                    <div class="md:col-span-4">
                        <select x-model="selectedCctv" @change="applyFilters()" 
                                class="w-full py-2.5 px-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-slate-700 font-medium">
                            <option value="">Semua Kamera CCTV</option>
                            @foreach($cctvs as $cctv)
                                <option value="{{ $cctv->id }}">{{ $cctv->nama_cctv }} ({{ $cctv->building->nama_gedung ?? 'Kampus' }})</option>
                            @endforeach
                            <!-- Demo fallback option -->
                            <option value="1">CCTV 01 - Lab Komputer Lantai 2 (Demo)</option>
                            <option value="2">CCTV 04 - Gerbang Masuk Utama (Demo)</option>
                            <option value="3">CCTV 02 - Meja Baca Perpustakaan (Demo)</option>
                        </select>
                    </div>

                    <!-- Event Category Filter -->
                    <div class="md:col-span-3">
                        <select x-model="selectedEventType" @change="applyFilters()"
                                class="w-full py-2.5 px-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-slate-700 font-medium">
                            <option value="all">Semua Jenis Kejadian</option>
                            <option value="missing_item">🚨 Barang Hilang / Berpindah</option>
                            <option value="vehicle_flow">🚗 Lalu Lintas Kendaraan</option>
                            <option value="presence_detected">🚶 Kehadiran Orang</option>
                        </select>
                    </div>
                </div>

                <!-- Quick Filter Pills -->
                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100">
                    <span class="text-xs font-bold text-slate-400 mr-1">Filter Objek:</span>
                    <button type="button" @click="setObjectFilter('all')" 
                            :class="selectedObject === 'all' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1 rounded-lg text-xs transition-colors">
                        Semua Objek
                    </button>
                    <button type="button" @click="setObjectFilter('laptop')" 
                            :class="selectedObject === 'laptop' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1 rounded-lg text-xs transition-colors">
                        💻 Laptop & Gadget
                    </button>
                    <button type="button" @click="setObjectFilter('backpack')" 
                            :class="selectedObject === 'backpack' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1 rounded-lg text-xs transition-colors">
                        🎒 Tas & Ransel
                    </button>
                    <button type="button" @click="setObjectFilter('car')" 
                            :class="selectedObject === 'car' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1 rounded-lg text-xs transition-colors">
                        🚗 Mobil
                    </button>
                    <button type="button" @click="setObjectFilter('motorcycle')" 
                            :class="selectedObject === 'motorcycle' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1 rounded-lg text-xs transition-colors">
                        🏍️ Motor
                    </button>
                    <button type="button" @click="setObjectFilter('person')" 
                            :class="selectedObject === 'person' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1 rounded-lg text-xs transition-colors">
                        🚶 Orang
                    </button>
                </div>
            </div>

            <!-- 5. Events Feed / Timeline Cards -->
            <div class="space-y-4">
                <div id="events-feed-header" class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-1">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-clock-rotate-left text-slate-400"></i>
                        Linimasa Investigasi Visual
                    </h2>
                    <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                        <span class="text-xs font-semibold text-slate-500 hidden sm:inline" 
                              x-text="'Menampilkan ' + paginatedEvents.length + ' dari ' + filteredEvents.length + ' catatan kejadian'"></span>
                        <span class="text-xs text-slate-300 hidden sm:inline">|</span>
                        <span class="text-xs font-bold text-purple-600" x-text="'Halaman ' + currentPage + ' / ' + totalPages"></span>

                        <!-- Tombol Halaman Mini di Header -->
                        <div x-show="totalPages > 1" class="flex items-center gap-1 ml-1">
                            <button type="button" @click="prevPage()" :disabled="currentPage === 1"
                                    title="Halaman Sebelumnya"
                                    class="w-7 h-7 rounded-lg border border-slate-200 text-xs flex items-center justify-center text-slate-600 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                                <i class="fas fa-chevron-left text-[10px]"></i>
                            </button>
                            <button type="button" @click="nextPage()" :disabled="currentPage === totalPages"
                                    title="Halaman Berikutnya"
                                    class="w-7 h-7 rounded-lg border border-slate-200 text-xs flex items-center justify-center text-slate-600 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                                <i class="fas fa-chevron-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div x-show="filteredEvents.length === 0" class="bg-white rounded-2xl p-12 text-center border border-slate-200/80">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-2xl">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-700 mt-4">Tidak ada kejadian yang cocok</h3>
                    <p class="text-sm text-slate-500 mt-1">Coba ubah kata kunci pencarian atau reset filter kamera Anda.</p>
                </div>

                <!-- Event Cards Repeater (Paginated for Ultra Fast Rendering) -->
                <template x-for="event in paginatedEvents" :key="event.id">
                    <div class="bg-white rounded-2xl border transition-all hover:shadow-md overflow-hidden"
                         :class="event.event_type === 'missing_item' ? 'border-rose-300 shadow-rose-100/50 ring-1 ring-rose-200' : 'border-slate-200/80 shadow-xs'">
                        
                        <div class="p-5 md:p-6">
                            <!-- Card Header -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <!-- Badge Event Type -->
                                    <template x-if="event.event_type === 'missing_item'">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-extrabold bg-rose-600 text-white shadow-xs">
                                            <i class="fas fa-triangle-exclamation animate-bounce"></i>
                                            BARANG HILANG / BERPINDAH
                                        </span>
                                    </template>
                                    <template x-if="event.event_type === 'vehicle_flow'">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <i class="fas fa-car-side"></i>
                                            LALU LINTAS KENDARAAN
                                        </span>
                                    </template>
                                    <template x-if="event.event_type === 'presence_detected'">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i class="fas fa-person"></i>
                                            DETEKSI ORANG
                                        </span>
                                    </template>

                                    <!-- Camera & Location -->
                                    <span class="text-sm font-black text-slate-800" x-text="event.cctv_name"></span>
                                    <span class="text-xs text-slate-400 font-medium hidden sm:inline" x-text="'(' + event.building_name + ')'"></span>
                                </div>

                                <!-- Time stamp -->
                                <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                                    <i class="far fa-clock text-slate-400"></i>
                                    <span class="font-mono text-slate-800 font-extrabold text-sm" x-text="event.event_time + ' WIB'"></span>
                                    <span class="text-slate-400">|</span>
                                    <span x-text="event.event_date"></span>
                                </div>
                            </div>

                            <!-- Narrative Box -->
                            <div class="mt-4 p-4 rounded-xl flex items-start gap-3"
                                 :class="event.event_type === 'missing_item' ? 'bg-rose-50/70 border border-rose-200/60' : 'bg-slate-50 border border-slate-100'">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5"
                                     :class="event.event_type === 'missing_item' ? 'bg-rose-100 text-rose-600' : 'bg-purple-100 text-purple-600'">
                                    <i class="fas" :class="event.event_type === 'missing_item' ? 'fa-bell' : 'fa-comment-dots'"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold leading-relaxed"
                                       :class="event.event_type === 'missing_item' ? 'text-rose-950 font-bold' : 'text-slate-700'"
                                       x-text="event.narrative"></p>
                                    
                                    <!-- Badges Metadata -->
                                    <div class="mt-2.5 flex flex-wrap items-center gap-2 text-xs">
                                        <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 font-semibold text-slate-600">
                                            Objek: <strong class="text-slate-900 capitalize" x-text="event.object_type"></strong>
                                        </span>
                                        <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 font-semibold text-slate-600">
                                            Warna: <strong class="text-slate-900 capitalize" x-text="event.color"></strong>
                                        </span>
                                        <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 font-semibold text-slate-600">
                                            AI Confidence: <strong class="text-purple-600" x-text="event.confidence + '%'"></strong>
                                        </span>
                                        <template x-if="event.last_seen_time">
                                            <span class="px-2 py-0.5 rounded-md bg-rose-100 border border-rose-200 font-semibold text-rose-700">
                                                Terakhir Terlihat Stabil: <strong x-text="event.last_seen_time + ' WIB'"></strong>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Snapshots Area -->
                            <div class="mt-5">
                                <!-- Khusus Missing Item: 3 Komparasi Foto -->
                                <template x-if="event.event_type === 'missing_item'">
                                    <div>
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                            <i class="fas fa-camera text-slate-400"></i>
                                            Bukti Visual Rekaman (Sebelum, Saat Kejadian, Sesudah)
                                        </p>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            <!-- Snapshot 1: Sebelum -->
                                            <div class="relative rounded-xl overflow-hidden border border-slate-200 group bg-slate-900 cursor-pointer"
                                                 @click="openImageModal(event.snapshot_before_url, 'Foto Sebelum Hilang: Objek Masih Berada di Tempat')">
                                                <img :src="event.snapshot_before_url" loading="lazy" alt="Sebelum" class="w-full h-40 object-cover group-hover:scale-105 transition-transform">
                                                <div class="absolute top-2 left-2 px-2 py-0.5 rounded bg-black/70 text-[11px] font-bold text-emerald-400 backdrop-blur-xs">
                                                    1. Sebelum (Stabil)
                                                </div>
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition-opacity">
                                                    <i class="fas fa-magnifying-glass-plus mr-1"></i> Perbesar
                                                </div>
                                            </div>

                                            <!-- Snapshot 2: Saat Kejadian -->
                                            <div class="relative rounded-xl overflow-hidden border border-rose-300 group bg-slate-900 cursor-pointer"
                                                 @click="openImageModal(event.snapshot_url, 'Foto Saat Kejadian: Terdeteksi Interaksi Objek / Orang')">
                                                <img :src="event.snapshot_url" loading="lazy" alt="Saat Kejadian" class="w-full h-40 object-cover group-hover:scale-105 transition-transform">
                                                <div class="absolute top-2 left-2 px-2 py-0.5 rounded bg-rose-600 text-[11px] font-bold text-white shadow-xs">
                                                    2. Saat Kejadian (Interaksi)
                                                </div>
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition-opacity">
                                                    <i class="fas fa-magnifying-glass-plus mr-1"></i> Perbesar
                                                </div>
                                            </div>

                                            <!-- Snapshot 3: Sesudah (Kosong) -->
                                            <div class="relative rounded-xl overflow-hidden border border-slate-200 group bg-slate-900 cursor-pointer"
                                                 @click="openImageModal(event.snapshot_after_url, 'Foto Sesudah Hilang: Lokasi Meja / Area Kosong')">
                                                <img :src="event.snapshot_after_url" loading="lazy" alt="Sesudah" class="w-full h-40 object-cover group-hover:scale-105 transition-transform">
                                                <div class="absolute top-2 left-2 px-2 py-0.5 rounded bg-black/70 text-[11px] font-bold text-rose-400 backdrop-blur-xs">
                                                    3. Sesudah (Kosong/Hilang)
                                                </div>
                                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition-opacity">
                                                    <i class="fas fa-magnifying-glass-plus mr-1"></i> Perbesar
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Deteksi Biasa: 1 Snapshot -->
                                <template x-if="event.event_type !== 'missing_item' && event.snapshot_url">
                                    <div class="flex items-center gap-4">
                                        <div class="relative w-48 h-28 rounded-xl overflow-hidden border border-slate-200 group bg-slate-900 cursor-pointer flex-shrink-0"
                                             @click="openImageModal(event.snapshot_url, event.narrative)">
                                            <img :src="event.snapshot_url" loading="lazy" alt="Snapshot" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition-opacity">
                                                <i class="fas fa-magnifying-glass-plus mr-1"></i> Perbesar
                                            </div>
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            <p class="font-bold text-slate-700 mb-1">Snapshot Cuplikan Kamera</p>
                                            <p>Klik pada foto untuk melihat tangkapan layar beresolusi penuh dari kamera saat deteksi dipicu.</p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Card Action Footer -->
                            <div class="mt-5 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                                <div class="text-xs text-slate-400 font-medium flex items-center gap-2">
                                    <span>ID Event: #AI-<span x-text="event.id"></span></span>
                                    <span>•</span>
                                    <span>Disimpan secara otomatis oleh AI Node</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <!-- Direct Link to Playback -->
                                    <a :href="'{{ route('playback.index') }}?cctv_id=' + event.cctv_id" 
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 transition-colors shadow-xs">
                                        <i class="fas fa-circle-play mr-1"></i> Buka Rekaman (Playback)
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Pagination Controls Bar (Smooth & Fast) -->
                <div x-show="filteredEvents.length > perPage" class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
                    <div class="text-xs text-slate-500 font-medium">
                        Menampilkan <span class="font-bold text-slate-800" x-text="(currentPage - 1) * perPage + 1"></span> - 
                        <span class="font-bold text-slate-800" x-text="Math.min(currentPage * perPage, filteredEvents.length)"></span> 
                        dari <span class="font-bold text-slate-800" x-text="filteredEvents.length"></span> kejadian
                    </div>

                    <div class="flex items-center gap-1.5 flex-wrap justify-center">
                        <!-- Tombol Sebelumnya -->
                        <button type="button" @click="prevPage()" :disabled="currentPage === 1"
                                class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                            <i class="fas fa-chevron-left mr-1"></i> Sebelumnya
                        </button>

                        <!-- Nomor Halaman -->
                        <template x-for="(page, idx) in pageNumbers" :key="idx">
                            <div class="inline-flex items-center">
                                <span x-show="page === '...'" class="px-2 py-1 text-slate-400 text-xs font-bold select-none">...</span>
                                <button x-show="page !== '...'" type="button" @click="goToPage(page)"
                                        :class="currentPage === page ? 'bg-purple-600 text-white shadow-xs font-black' : 'border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold'"
                                        class="w-8 h-8 rounded-xl text-xs flex items-center justify-center transition-all"
                                        x-text="page">
                                </button>
                            </div>
                        </template>

                        <!-- Tombol Berikutnya -->
                        <button type="button" @click="nextPage()" :disabled="currentPage === totalPages"
                                class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                            Berikutnya <i class="fas fa-chevron-right ml-1"></i>
                        </button>
                    </div>

                    <!-- Pilihan Jumlah Per Halaman -->
                    <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
                        <span>Per halaman:</span>
                        <select x-model.number="perPage" @change="currentPage = 1"
                                class="py-1 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-bold focus:ring-purple-500 focus:border-purple-500">
                            <option value="12">12</option>
                            <option value="24">24</option>
                            <option value="48">48</option>
                        </select>
                    </div>
                </div>

                <!-- Tombol Muat Data Tambahan dari Server (Jika DB memiliki ribuan data) -->
                <div x-show="hasMoreServerData" class="text-center pt-2">
                    <button type="button" @click="loadMoreFromServer()" :disabled="isLoadingMore"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 shadow-xs hover:border-purple-300 hover:text-purple-700 transition-all disabled:opacity-50">
                        <template x-if="isLoadingMore">
                            <i class="fas fa-circle-notch fa-spin text-purple-600"></i>
                        </template>
                        <template x-if="!isLoadingMore">
                            <i class="fas fa-database text-purple-600"></i>
                        </template>
                        <span x-text="isLoadingMore ? 'Mengambil data dari server...' : 'Muat 250 Data Terdahulu dari Database'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 6. Modal Zoom Image -->
        <div x-show="showImageModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             @keydown.escape.window="showImageModal = false">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-4xl w-full overflow-hidden shadow-2xl relative"
                 @click.outside="showImageModal = false">
                <div class="p-4 border-b border-slate-800 flex items-center justify-between">
                    <p class="text-sm font-bold text-white" x-text="modalImageCaption"></p>
                    <button @click="showImageModal = false" class="text-slate-400 hover:text-white text-lg">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-2 bg-black flex items-center justify-center max-h-[75vh] overflow-hidden">
                    <img :src="modalImageUrl" alt="Zoom" class="max-h-[70vh] w-auto object-contain rounded-lg">
                </div>
            </div>
        </div>

        <!-- 7. Modal Panduan PoC AI -->
        <div x-show="showPoCModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             @keydown.escape.window="showPoCModal = false">
            <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl relative space-y-4"
                 @click.outside="showPoCModal = false">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-black text-slate-800 flex items-center gap-2">
                        <i class="fas fa-terminal text-purple-600"></i>
                        Panduan Menjalankan AI Analyzer
                    </h3>
                    <button @click="showPoCModal = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <div class="text-sm text-slate-600 space-y-3 leading-relaxed">
                    <p>Sistem AI ini ditenagai skrip Python yang sudah siap di dalam folder <code>scripts/ai_analyzer.py</code>. Anda dapat mengujinya dengan skenario berikut:</p>
                    
                    <div class="bg-slate-900 text-slate-200 p-4 rounded-xl font-mono text-xs space-y-2">
                        <p class="text-slate-400"># 1. Jalankan Uji Coba Demo Sintetis (Tanpa kamera/video asli):</p>
                        <p class="text-emerald-400">python scripts/ai_analyzer.py --demo</p>
                        <p class="text-slate-400 mt-2"># 2. Jalankan pada 1 File Video MP4 Rekaman CCTV riil:</p>
                        <p class="text-emerald-400">python scripts/ai_analyzer.py --video /path/to/record.mp4 --cctv-id 1</p>
                        <p class="text-slate-400 mt-2"># 3. Jalankan dan langsung push hasil deteksi ke Web Master:</p>
                        <p class="text-emerald-400">python scripts/ai_analyzer.py --video record.mp4 --master-url https://cctv.unpad.net</p>
                    </div>

                    <div class="bg-purple-50 p-3 rounded-xl border border-purple-100 text-xs text-purple-900">
                        <strong>Integrasi Ollama:</strong> Jika Ollama berjalan di <code>http://localhost:11434</code>, skrip akan otomatis memanggil model (seperti <code>qwen2.5:1.5b</code>) untuk narasi bahasa manusia. Jika Ollama tidak aktif, skrip tetap berjalan lancar dengan kalimat template otomatis.
                    </div>
                </div>

                <div class="text-right pt-2 border-t border-slate-100">
                    <button @click="showPoCModal = false" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800">
                        Tutup Panduan
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function intelligentApp() {
            return {
                events: @json($initialEvents),
                filteredEvents: [],
                stats: @json($stats),
                searchQuery: '',
                selectedCctv: '',
                selectedObject: 'all',
                selectedEventType: 'all',
                summaryText: 'Ringkasan Aktivitas Terpantau: Sepanjang hari ini tercatat beberapa pergerakan kendaraan normal di area gerbang masuk serta aktivitas di ruang laboratorium. Terdeteksi 1 anomali perpindahan barang (laptop warna silver di Lab Komputer pada pukul 11:15 WIB) yang memerlukan atensi petugas piket.',
                summarySource: 'AI Synthesizer',
                isGeneratingSummary: false,
                showImageModal: false,
                modalImageUrl: '',
                modalImageCaption: '',
                showPoCModal: false,

                currentPage: 1,
                perPage: 12,
                serverPage: 1,
                hasMoreServerData: false,
                isLoadingMore: false,

                init() {
                    this.filteredEvents = this.events;
                    if (this.stats && this.stats.total > this.events.length) {
                        this.hasMoreServerData = true;
                    }
                },

                get paginatedEvents() {
                    const start = (this.currentPage - 1) * this.perPage;
                    return this.filteredEvents.slice(start, start + this.perPage);
                },

                get totalPages() {
                    return Math.max(1, Math.ceil(this.filteredEvents.length / this.perPage));
                },

                get pageNumbers() {
                    const total = this.totalPages;
                    const current = this.currentPage;
                    const pages = [];
                    if (total <= 7) {
                        for (let i = 1; i <= total; i++) pages.push(i);
                    } else {
                        pages.push(1);
                        if (current > 3) pages.push('...');
                        const start = Math.max(2, current - 1);
                        const end = Math.min(total - 1, current + 1);
                        for (let i = start; i <= end; i++) pages.push(i);
                        if (current < total - 2) pages.push('...');
                        pages.push(total);
                    }
                    return pages;
                },

                goToPage(p) {
                    if (p >= 1 && p <= this.totalPages) {
                        this.currentPage = p;
                        document.getElementById('events-feed-header')?.scrollIntoView({ behavior: 'smooth' });
                    }
                },

                prevPage() {
                    this.goToPage(this.currentPage - 1);
                },

                nextPage() {
                    this.goToPage(this.currentPage + 1);
                },

                applyFilters() {
                    this.currentPage = 1;
                    const q = this.searchQuery.toLowerCase().trim();
                    const cctv = this.selectedCctv;
                    const obj = this.selectedObject;
                    const type = this.selectedEventType;

                    this.filteredEvents = this.events.filter(item => {
                        // Filter CCTV
                        if (cctv && item.cctv_id != cctv) return false;

                        // Filter Object
                        if (obj !== 'all' && item.object_type !== obj) return false;

                        // Filter Event Type
                        if (type !== 'all' && item.event_type !== type) return false;

                        // Search Query
                        if (q) {
                            const hay = (item.narrative + ' ' + item.object_type + ' ' + item.color + ' ' + item.cctv_name + ' ' + item.building_name).toLowerCase();
                            if (!hay.includes(q)) return false;
                        }

                        return true;
                    });
                },

                setObjectFilter(obj) {
                    this.selectedObject = obj;
                    this.currentPage = 1;
                    this.applyFilters();
                },

                openImageModal(url, caption) {
                    if (!url) return;
                    this.modalImageUrl = url;
                    this.modalImageCaption = caption || 'Detail Foto Kejadian';
                    this.showImageModal = true;
                },

                async refreshSummary() {
                    this.isGeneratingSummary = true;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const resp = await fetch('{{ route('intelligent.summary') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || '',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                cctv_id: this.selectedCctv,
                                date: new Date().toISOString().slice(0, 10)
                            })
                        });

                        const res = await resp.json();
                        if (res.status === 'success') {
                            this.summaryText = res.summary;
                            this.summarySource = res.source === 'ollama' ? 'Ollama LLM Live' : 'AI Synthesizer';
                        }
                    } catch (e) {
                        console.error('Gagal memuat ringkasan:', e);
                    } finally {
                        this.isGeneratingSummary = false;
                    }
                },

                async loadMoreFromServer() {
                    if (this.isLoadingMore) return;
                    this.isLoadingMore = true;
                    try {
                        this.serverPage++;
                        const resp = await fetch(`{{ route('intelligent.events') }}?limit=250&page=${this.serverPage}`);
                        const res = await resp.json();
                        if (res.status === 'success' && res.data && res.data.length > 0) {
                            const existingIds = new Set(this.events.map(e => e.id));
                            const newEvents = res.data.filter(e => !existingIds.has(e.id));
                            this.events = [...this.events, ...newEvents];
                            this.applyFilters();
                            if (this.events.length >= res.total || res.data.length < 250) {
                                this.hasMoreServerData = false;
                            }
                        } else {
                            this.hasMoreServerData = false;
                        }
                    } catch (e) {
                        console.error('Gagal mengambil data tambahan dari server:', e);
                    } finally {
                        this.isLoadingMore = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>
