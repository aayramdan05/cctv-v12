<x-app-layout>
    <div x-data="intelligentApp()" class="pt-16 md:pt-18 pb-20 px-3 md:px-6 max-w-[1600px] mx-auto space-y-3.5 font-sans text-slate-800">

        <!-- 1. Compact Header Bar -->
        <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white/95 backdrop-blur-md px-4 py-3 rounded-xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-purple-600 to-indigo-600 flex items-center justify-center text-white shadow-xs">
                    <i class="fas fa-brain text-base"></i>
                </div>
                <div>
                    <h1 class="text-lg font-black text-slate-800 tracking-tight flex items-center gap-2">
                        AI Video Analytics & Event Log
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 border border-purple-200">v2.1</span>
                    </h1>
                    <p class="text-xs text-slate-500 font-medium hidden sm:block">Audit forensic kejadian, pelacakan objek, & deteksi kehilangan barang CCTV</p>
                </div>
            </div>

            <!-- Badges & Action -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    YOLOv8 Active
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                    <i class="fas fa-robot text-purple-500 text-[11px]"></i>
                    Ollama Narrator
                </span>
            </div>
        </header>

        <!-- 2. Compact Metric Strip & Interactive KPI Filter -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
            <!-- Total Deteksi -->
            <div @click="filterByType('all')" 
                 :class="selectedEventType === 'all' && selectedObject === 'all' ? 'ring-2 ring-purple-600 bg-purple-50/40 border-purple-300' : 'bg-white hover:border-slate-300'"
                 class="p-3 rounded-xl border border-slate-200/80 shadow-xs transition-all cursor-pointer select-none flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Deteksi</span>
                    <span class="text-xl font-black text-slate-800 leading-tight mt-0.5 block" x-text="stats.total"></span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-sm">
                    <i class="fas fa-list-check"></i>
                </div>
            </div>

            <!-- Barang Hilang (Priority Alert) -->
            <div @click="filterByType('missing_item')"
                 :class="selectedEventType === 'missing_item' ? 'ring-2 ring-rose-600 bg-rose-50 border-rose-300' : 'bg-white hover:border-rose-300'"
                 class="p-3 rounded-xl border border-rose-200 shadow-xs transition-all cursor-pointer select-none flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-ping"></span>
                        Barang Hilang
                    </span>
                    <span class="text-xl font-black text-rose-600 leading-tight mt-0.5 block" x-text="stats.missing_items"></span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center text-sm">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </div>

            <!-- Kendaraan -->
            <div @click="filterByType('vehicle_flow')"
                 :class="selectedEventType === 'vehicle_flow' ? 'ring-2 ring-blue-600 bg-blue-50/40 border-blue-300' : 'bg-white hover:border-slate-300'"
                 class="p-3 rounded-xl border border-slate-200/80 shadow-xs transition-all cursor-pointer select-none flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Kendaraan</span>
                    <span class="text-xl font-black text-blue-600 leading-tight mt-0.5 block" x-text="stats.vehicles"></span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                    <i class="fas fa-car-side"></i>
                </div>
            </div>

            <!-- Orang -->
            <div @click="filterByType('presence_detected')"
                 :class="selectedEventType === 'presence_detected' ? 'ring-2 ring-emerald-600 bg-emerald-50/40 border-emerald-300' : 'bg-white hover:border-slate-300'"
                 class="p-3 rounded-xl border border-slate-200/80 shadow-xs transition-all cursor-pointer select-none flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Orang / Pejalan</span>
                    <span class="text-xl font-black text-emerald-600 leading-tight mt-0.5 block" x-text="stats.persons"></span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                    <i class="fas fa-person-walking"></i>
                </div>
            </div>
        </div>

        <!-- 3. AI Narrative Mini-Summary (Compact Collapsible) -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-purple-950 rounded-xl px-4 py-2.5 text-white shadow-xs flex items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2.5 flex-1 min-w-0">
                <div class="w-6 h-6 rounded-md bg-purple-500/30 flex items-center justify-center text-purple-200 flex-shrink-0">
                    <i class="fas fa-wand-magic-sparkles text-xs"></i>
                </div>
                <div class="truncate flex-1">
                    <span class="font-bold text-purple-300 mr-1.5" x-text="'[' + summarySource + ']'"></span>
                    <span class="text-slate-200 font-normal truncate" x-text="summaryText"></span>
                </div>
            </div>
            <button type="button" @click="refreshSummary()" :disabled="isGeneratingSummary"
                    title="Perbarui Analisa Narasi AI"
                    class="flex-shrink-0 px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-white font-bold text-[11px] transition-colors disabled:opacity-50 flex items-center gap-1.5">
                <i class="fas fa-rotate" :class="{'fa-spin': isGeneratingSummary}"></i>
                <span class="hidden sm:inline" x-text="isGeneratingSummary ? 'Memproses...' : 'Perbarui'"></span>
            </button>
        </div>

        <!-- 4. Unified High-Productivity Filter Bar with TIME FILTER -->
        <div class="bg-white rounded-xl p-3 border border-slate-200/80 shadow-xs space-y-2.5">
            <!-- Row 1: Search, Camera, Event Type, Time Filter, View Mode -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 text-xs">
                <!-- Search Box -->
                <div class="sm:col-span-4 relative">
                    <i class="fas fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input type="text" x-model="searchQuery" @input.debounce.350ms="applyFilters(true)"
                           placeholder="Cari objek, warna, narasi, lokasi..." 
                           class="w-full pl-8 pr-7 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-1 focus:ring-purple-500 focus:border-purple-500 font-medium text-slate-800 placeholder-slate-400">
                    <button x-show="searchQuery" @click="searchQuery = ''; applyFilters(true)" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </div>

                <!-- Camera Select -->
                <div class="sm:col-span-3">
                    <select x-model="selectedCctv" @change="applyFilters(true)" 
                            class="w-full py-1.5 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-1 focus:ring-purple-500 font-medium text-slate-700">
                        <option value="">Semua Kamera CCTV</option>
                        @foreach($cctvs as $cctv)
                            <option value="{{ $cctv->id }}">{{ $cctv->nama_cctv }} ({{ $cctv->building->nama_gedung ?? 'Kampus' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Time Filter Preset Dropdown -->
                <div class="sm:col-span-3">
                    <select x-model="timePreset" @change="handleTimePresetChange()" 
                            class="w-full py-1.5 px-2.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-1 focus:ring-purple-500 font-bold text-slate-700">
                        <option value="today">📅 Waktu: Hari Ini (Default)</option>
                        <option value="yesterday">⏪ Waktu: Kemarin</option>
                        <option value="last_7_days">🗓️ Waktu: 7 Hari Terakhir</option>
                        <option value="all">🕒 Waktu: Semua Waktu</option>
                        <option value="custom">⚙️ Waktu: Rentang Kustom (Jam/Tgl)...</option>
                    </select>
                </div>

                <!-- View Mode Switcher -->
                <div class="sm:col-span-2 flex items-center justify-end gap-1 bg-slate-100 p-0.5 rounded-lg border border-slate-200 select-none">
                    <button type="button" @click="viewMode = 'table'" 
                            :class="viewMode === 'table' ? 'bg-white text-purple-700 font-black shadow-xs' : 'text-slate-500 hover:text-slate-800 font-semibold'"
                            class="flex-1 py-1 px-2 rounded text-[11px] transition-all flex items-center justify-center gap-1">
                        <i class="fas fa-table-list"></i>
                        <span>Tabel</span>
                    </button>
                    <button type="button" @click="viewMode = 'cards'" 
                            :class="viewMode === 'cards' ? 'bg-white text-purple-700 font-black shadow-xs' : 'text-slate-500 hover:text-slate-800 font-semibold'"
                            class="flex-1 py-1 px-2 rounded text-[11px] transition-all flex items-center justify-center gap-1">
                        <i class="fas fa-grip-vertical"></i>
                        <span>Kartu</span>
                    </button>
                </div>
            </div>

            <!-- Row 2: Custom Date & Time Drawer (Muncul jika pilih 'custom' atau klik tombol rentang) -->
            <div x-show="showCustomTime || timePreset === 'custom'" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="bg-purple-50/60 p-2.5 rounded-lg border border-purple-200 grid grid-cols-1 sm:grid-cols-5 gap-2 text-xs items-center">
                <div>
                    <label class="block text-[10px] font-bold text-purple-900 uppercase">Dari Tanggal:</label>
                    <input type="date" x-model="customStartDate" @change="applyFilters(true)" 
                           class="w-full mt-0.5 py-1 px-2 text-xs bg-white border border-purple-300 rounded focus:ring-1 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-purple-900 uppercase">Jam Mulai:</label>
                    <input type="time" step="1" x-model="customStartTime" @change="applyFilters(true)" 
                           class="w-full mt-0.5 py-1 px-2 text-xs bg-white border border-purple-300 rounded focus:ring-1 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-purple-900 uppercase">Sampai Tanggal:</label>
                    <input type="date" x-model="customEndDate" @change="applyFilters(true)" 
                           class="w-full mt-0.5 py-1 px-2 text-xs bg-white border border-purple-300 rounded focus:ring-1 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-purple-900 uppercase">Jam Selesai:</label>
                    <input type="time" step="1" x-model="customEndTime" @change="applyFilters(true)" 
                           class="w-full mt-0.5 py-1 px-2 text-xs bg-white border border-purple-300 rounded focus:ring-1 focus:ring-purple-500">
                </div>
                <div class="flex items-end gap-1.5 pt-3 sm:pt-0">
                    <button type="button" @click="applyFilters(true)" 
                            class="flex-1 py-1.5 px-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded text-xs transition-colors shadow-xs">
                        Terapkan
                    </button>
                    <button type="button" @click="resetTimeFilter()" 
                            class="py-1.5 px-2 bg-white hover:bg-slate-100 text-slate-600 font-semibold border border-slate-300 rounded text-xs transition-colors">
                        Reset
                    </button>
                </div>
            </div>

            <!-- Row 3: Quick Filter Chips -->
            <div class="flex flex-wrap items-center gap-1.5 pt-1.5 border-t border-slate-100 text-xs">
                <span class="text-[11px] font-bold text-slate-400 mr-1">Objek:</span>
                
                <button type="button" @click="filterByType('all')" 
                        :class="selectedObject === 'all' && selectedEventType === 'all' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-2.5 py-0.5 rounded text-[11px] transition-colors">
                    Semua
                </button>

                <!-- Tombol Cepat Barang Hilang (Prioritas Utama) -->
                <button type="button" @click="filterByType('missing_item')" 
                        :class="selectedEventType === 'missing_item' ? 'bg-rose-600 text-white font-extrabold shadow-xs' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 font-bold'"
                        class="px-2.5 py-0.5 rounded text-[11px] transition-colors inline-flex items-center gap-1">
                    <i class="fas fa-triangle-exclamation"></i>
                    🚨 Barang Hilang (<span x-text="stats.missing_items"></span>)
                </button>

                <button type="button" @click="setObjectFilter('laptop')" 
                        :class="selectedObject === 'laptop' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-2.5 py-0.5 rounded text-[11px] transition-colors">
                    💻 Laptop
                </button>
                <button type="button" @click="setObjectFilter('backpack')" 
                        :class="selectedObject === 'backpack' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-2.5 py-0.5 rounded text-[11px] transition-colors">
                    🎒 Tas / Ransel
                </button>
                <button type="button" @click="setObjectFilter('car')" 
                        :class="selectedObject === 'car' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-2.5 py-0.5 rounded text-[11px] transition-colors">
                    🚗 Mobil
                </button>
                <button type="button" @click="setObjectFilter('motorcycle')" 
                        :class="selectedObject === 'motorcycle' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-2.5 py-0.5 rounded text-[11px] transition-colors">
                    🏍️ Motor
                </button>
                <button type="button" @click="setObjectFilter('person')" 
                        :class="selectedObject === 'person' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-2.5 py-0.5 rounded text-[11px] transition-colors">
                    🚶 Orang
                </button>
            </div>
        </div>

        <!-- 5. Events Feed / Log Viewer -->
        <div class="space-y-2">
            <!-- Feed Sub-Header & Counter -->
            <div id="events-feed-header" class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-1 text-xs">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-700 flex items-center gap-1.5">
                        <i class="fas fa-list text-slate-400"></i>
                        Log Riwayat Aktivitas & Investigasi
                    </span>
                    <span x-show="isLoadingEvents" class="inline-flex items-center gap-1 text-[11px] text-purple-600 font-bold ml-1">
                        <i class="fas fa-circle-notch fa-spin"></i> Memuat data...
                    </span>
                </div>

                <div class="flex items-center gap-2 text-slate-500 font-medium">
                    <span>Menampilkan <strong class="text-slate-800" x-text="paginatedEvents.length"></strong> dari <strong class="text-slate-800" x-text="filteredEvents.length"></strong> log</span>
                    <span class="text-slate-300">|</span>
                    <span class="font-bold text-purple-700" x-text="'Hal ' + currentPage + ' / ' + totalPages"></span>
                    
                    <!-- Quick Page Chevrons -->
                    <div x-show="totalPages > 1" class="flex items-center gap-1 ml-1">
                        <button type="button" @click="prevPage()" :disabled="currentPage === 1"
                                class="w-6 h-6 rounded border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed">
                            <i class="fas fa-chevron-left text-[9px]"></i>
                        </button>
                        <button type="button" @click="nextPage()" :disabled="currentPage === totalPages"
                                class="w-6 h-6 rounded border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed">
                            <i class="fas fa-chevron-right text-[9px]"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div x-show="filteredEvents.length === 0" class="bg-white rounded-xl p-8 text-center border border-slate-200/80">
                <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl">
                    <i class="fas fa-inbox"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700 mt-2">Tidak ada log kejadian yang cocok</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    <span x-show="timePreset === 'today'">Belum ada kejadian tercatat untuk <strong>Hari Ini</strong>.</span>
                    <span x-show="timePreset !== 'today'">Ubah kata kunci, kamera, atau sesuaikan filter waktu Anda.</span>
                </p>
                <div class="mt-3">
                    <button type="button" @click="timePreset = 'all'; applyFilters(true)" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 transition-colors inline-flex items-center gap-1.5">
                        <i class="fas fa-clock-rotate-left"></i>
                        <span>Tampilkan Semua Riwayat Waktu</span>
                    </button>
                </div>
            </div>

            <!-- MODE A: TABEL LOG PADAT (Default - Sleek High Density Table) -->
            <div x-show="viewMode === 'table' && filteredEvents.length > 0" 
                 class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-2.5 px-3">WAKTU</th>
                                <th class="py-2.5 px-3">TIPE & STATUS</th>
                                <th class="py-2.5 px-3">KAMERA & LOKASI</th>
                                <th class="py-2.5 px-3">OBJEK</th>
                                <th class="py-2.5 px-3">NARASI / DETAIL KEJADIAN</th>
                                <th class="py-2.5 px-3 text-center">BUKTI FOTO</th>
                                <th class="py-2.5 px-3 text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="event in paginatedEvents" :key="event.id">
                                <tr class="hover:bg-purple-50/30 transition-colors group"
                                    :class="event.event_type === 'missing_item' ? 'bg-rose-50/30 border-l-4 border-l-rose-500' : ''">
                                    
                                    <!-- 1. Waktu -->
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <div class="font-mono font-black text-slate-800 text-xs flex items-center gap-1"
                                             :class="event.event_type === 'missing_item' ? 'text-rose-600' : ''">
                                            <i class="far fa-clock text-[10px] text-slate-400"></i>
                                            <span x-text="event.event_time"></span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 block font-medium" x-text="event.event_date"></span>
                                    </td>

                                    <!-- 2. Tipe & Status -->
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <template x-if="event.event_type === 'missing_item'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black bg-rose-600 text-white shadow-xs">
                                                <i class="fas fa-triangle-exclamation"></i>
                                                BARANG HILANG
                                            </span>
                                        </template>
                                        <template x-if="event.event_type === 'vehicle_flow'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                <i class="fas fa-car-side text-[9px]"></i>
                                                KENDARAAN
                                            </span>
                                        </template>
                                        <template x-if="event.event_type === 'presence_detected'">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i class="fas fa-person-walking text-[9px]"></i>
                                                ORANG
                                            </span>
                                        </template>
                                    </td>

                                    <!-- 3. Kamera & Lokasi -->
                                    <td class="py-2.5 px-3">
                                        <span class="font-bold text-slate-800 block truncate max-w-[150px]" x-text="event.cctv_name"></span>
                                        <span class="text-[10px] text-slate-400 block truncate max-w-[150px]" x-text="event.building_name"></span>
                                    </td>

                                    <!-- 4. Objek & Warna -->
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-slate-800 capitalize" x-text="event.object_type"></span>
                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 capitalize font-medium" x-text="event.color"></span>
                                        </div>
                                        <span class="text-[10px] text-purple-600 font-semibold block" x-text="'AI ' + event.confidence + '%'"></span>
                                    </td>

                                    <!-- 5. Narasi Ringkas -->
                                    <td class="py-2.5 px-3 max-w-md">
                                        <p class="text-xs text-slate-700 line-clamp-1 leading-snug font-medium"
                                           :class="event.event_type === 'missing_item' ? 'text-rose-950 font-bold' : ''"
                                           :title="event.narrative"
                                           x-text="event.narrative"></p>
                                        <template x-if="event.last_seen_time">
                                            <span class="text-[10px] text-rose-500 font-semibold mt-0.5 block">
                                                Terakhir stabil di tempat: <span x-text="event.last_seen_time + ' WIB'"></span>
                                            </span>
                                        </template>
                                    </td>

                                    <!-- 6. Bukti Visual (Mini Preview Thumbnails) -->
                                    <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                        <!-- Jika Missing Item: 3 Mini Thumbnails -->
                                        <template x-if="event.event_type === 'missing_item'">
                                            <div class="inline-flex items-center gap-1 cursor-pointer" 
                                                 @click="openForensicModal(event)"
                                                 title="Klik untuk membuka investigasi komparasi foto">
                                                <!-- Thumb Sebelum -->
                                                <div class="relative w-9 h-6 rounded overflow-hidden border border-emerald-400 bg-slate-900 shadow-2xs group-hover:scale-105 transition-transform" title="1. Sebelum">
                                                    <img :src="event.snapshot_before_url" loading="lazy" class="w-full h-full object-cover">
                                                </div>
                                                <span class="text-[9px] text-slate-300">→</span>
                                                <!-- Thumb Saat Kejadian -->
                                                <div class="relative w-9 h-6 rounded overflow-hidden border border-amber-400 bg-slate-900 shadow-2xs group-hover:scale-105 transition-transform" title="2. Saat Kejadian">
                                                    <img :src="event.snapshot_url" loading="lazy" class="w-full h-full object-cover">
                                                </div>
                                                <span class="text-[9px] text-slate-300">→</span>
                                                <!-- Thumb Sesudah -->
                                                <div class="relative w-9 h-6 rounded overflow-hidden border border-rose-500 bg-slate-900 shadow-2xs group-hover:scale-105 transition-transform" title="3. Sesudah">
                                                    <img :src="event.snapshot_after_url" loading="lazy" class="w-full h-full object-cover">
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Jika Deteksi Biasa: 1 Mini Thumbnail -->
                                        <template x-if="event.event_type !== 'missing_item' && event.snapshot_url">
                                            <div class="inline-block relative w-10 h-7 rounded overflow-hidden border border-slate-200 bg-slate-900 cursor-pointer shadow-2xs group-hover:scale-105 transition-transform"
                                                 @click="openForensicModal(event)">
                                                <img :src="event.snapshot_url" loading="lazy" class="w-full h-full object-cover">
                                            </div>
                                        </template>
                                    </td>

                                    <!-- 7. Aksi Cepat -->
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" @click="openForensicModal(event)" 
                                                    title="Lihat Detail Investigasi & Foto Resolusi Penuh"
                                                    class="px-2.5 py-1 rounded bg-slate-100 hover:bg-purple-100 text-slate-700 hover:text-purple-700 font-bold text-[11px] transition-colors border border-slate-200">
                                                <i class="fas fa-eye mr-0.5"></i> Bukti
                                            </button>
                                            <a :href="'{{ route('playback.index') }}?cctv_id=' + event.cctv_id" 
                                               target="_blank" title="Buka Rekaman Video di CCTV ini"
                                               class="p-1 w-6 h-6 rounded bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-[11px] transition-colors border border-purple-200 inline-flex items-center justify-center">
                                                <i class="fas fa-circle-play text-[11px]"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODE B: KARTU RAMPING (Compact Modern Cards) -->
            <div x-show="viewMode === 'cards' && filteredEvents.length > 0" class="space-y-2.5">
                <template x-for="event in paginatedEvents" :key="event.id">
                    <div class="bg-white rounded-xl border transition-all hover:shadow-sm p-3.5"
                         :class="event.event_type === 'missing_item' ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200/80 shadow-xs'">
                        
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <!-- Left: Info & Narasi -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-1.5">
                                    <template x-if="event.event_type === 'missing_item'">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-600 text-white shadow-xs">
                                            🚨 BARANG HILANG
                                        </span>
                                    </template>
                                    <template x-if="event.event_type === 'vehicle_flow'">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            🚗 KENDARAAN
                                        </span>
                                    </template>
                                    <template x-if="event.event_type === 'presence_detected'">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            🚶 ORANG
                                        </span>
                                    </template>

                                    <span class="font-black text-slate-800 text-xs" x-text="event.cctv_name"></span>
                                    <span class="text-slate-400 text-[11px]" x-text="'(' + event.building_name + ')'"></span>

                                    <span class="text-slate-300">•</span>
                                    <span class="font-mono text-slate-600 font-bold text-xs" x-text="event.event_time + ' WIB'"></span>
                                    <span class="text-slate-400 text-[10px]" x-text="event.event_date"></span>
                                </div>

                                <p class="text-xs font-semibold leading-relaxed"
                                   :class="event.event_type === 'missing_item' ? 'text-rose-950 font-bold' : 'text-slate-700'"
                                   x-text="event.narrative"></p>

                                <div class="mt-2 flex flex-wrap items-center gap-1.5 text-[10px]">
                                    <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-semibold border border-slate-200">
                                        Objek: <strong class="text-slate-900 capitalize" x-text="event.object_type"></strong>
                                    </span>
                                    <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-semibold border border-slate-200">
                                        Warna: <strong class="text-slate-900 capitalize" x-text="event.color"></strong>
                                    </span>
                                    <span class="px-1.5 py-0.2 rounded bg-purple-50 text-purple-700 font-semibold border border-purple-200">
                                        AI Conf: <strong x-text="event.confidence + '%'"></strong>
                                    </span>
                                    <template x-if="event.last_seen_time">
                                        <span class="px-1.5 py-0.2 rounded bg-rose-100 text-rose-700 font-bold border border-rose-200">
                                            Stabil Terakhir: <span x-text="event.last_seen_time + ' WIB'"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Right: Thumbnails Strip & Action -->
                            <div class="flex items-center gap-2 flex-shrink-0 self-end md:self-center">
                                <!-- Bukti 3 Foto untuk Missing Item -->
                                <template x-if="event.event_type === 'missing_item'">
                                    <div class="flex items-center gap-1.5 cursor-pointer" @click="openForensicModal(event)">
                                        <div class="w-16 h-11 rounded-lg overflow-hidden border border-emerald-400 bg-slate-900" title="1. Sebelum">
                                            <img :src="event.snapshot_before_url" loading="lazy" class="w-full h-full object-cover">
                                        </div>
                                        <div class="w-16 h-11 rounded-lg overflow-hidden border border-amber-400 bg-slate-900" title="2. Saat Kejadian">
                                            <img :src="event.snapshot_url" loading="lazy" class="w-full h-full object-cover">
                                        </div>
                                        <div class="w-16 h-11 rounded-lg overflow-hidden border border-rose-500 bg-slate-900" title="3. Sesudah">
                                            <img :src="event.snapshot_after_url" loading="lazy" class="w-full h-full object-cover">
                                        </div>
                                    </div>
                                </template>

                                <!-- Bukti 1 Foto untuk Normal -->
                                <template x-if="event.event_type !== 'missing_item' && event.snapshot_url">
                                    <div class="w-20 h-12 rounded-lg overflow-hidden border border-slate-200 bg-slate-900 cursor-pointer" @click="openForensicModal(event)">
                                        <img :src="event.snapshot_url" loading="lazy" class="w-full h-full object-cover">
                                    </div>
                                </template>

                                <div class="flex flex-col gap-1 ml-1">
                                    <button type="button" @click="openForensicModal(event)" 
                                            class="px-2.5 py-1 rounded bg-slate-900 hover:bg-slate-800 text-white font-bold text-[11px] transition-colors shadow-xs">
                                        Bukti
                                    </button>
                                    <a :href="'{{ route('playback.index') }}?cctv_id=' + event.cctv_id" 
                                       target="_blank"
                                       class="px-2.5 py-1 rounded bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-[11px] text-center border border-purple-200 transition-colors">
                                        Video
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </template>
            </div>

            <!-- 6. Compact Pagination Controls -->
            <div x-show="filteredEvents.length > perPage" 
                 class="bg-white rounded-xl p-2.5 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs">
                <div class="text-slate-500 font-medium text-[11px]">
                    Menampilkan <span class="font-bold text-slate-800" x-text="(currentPage - 1) * perPage + 1"></span> - 
                    <span class="font-bold text-slate-800" x-text="Math.min(currentPage * perPage, filteredEvents.length)"></span> 
                    dari <span class="font-bold text-slate-800" x-text="filteredEvents.length"></span> log
                </div>

                <div class="flex items-center gap-1">
                    <button type="button" @click="prevPage()" :disabled="currentPage === 1"
                            class="px-2.5 py-1 rounded-lg border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left mr-0.5"></i> Prev
                    </button>

                    <template x-for="(page, idx) in pageNumbers" :key="idx">
                        <div>
                            <span x-show="page === '...'" class="px-1 text-slate-400 font-bold">...</span>
                            <button x-show="page !== '...'" type="button" @click="goToPage(page)"
                                    :class="currentPage === page ? 'bg-purple-600 text-white font-black' : 'border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold'"
                                    class="w-7 h-7 rounded-lg text-xs flex items-center justify-center transition-all"
                                    x-text="page">
                            </button>
                        </div>
                    </template>

                    <button type="button" @click="nextPage()" :disabled="currentPage === totalPages"
                            class="px-2.5 py-1 rounded-lg border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed">
                        Next <i class="fas fa-chevron-right ml-0.5"></i>
                    </button>
                </div>

                <div class="flex items-center gap-1.5 text-[11px] text-slate-500 font-medium">
                    <span>Per hal:</span>
                    <select x-model.number="perPage" @change="currentPage = 1"
                            class="py-0.5 px-1.5 text-xs bg-slate-50 border border-slate-200 rounded font-bold text-slate-700">
                        <option value="15">15</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <!-- Muat Data Terdahulu dari Server -->
            <div x-show="hasMoreServerData" class="text-center pt-1">
                <button type="button" @click="loadMoreFromServer()" :disabled="isLoadingMore"
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-bold bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 shadow-xs hover:border-purple-300 hover:text-purple-700 transition-all disabled:opacity-50">
                    <i class="fas" :class="isLoadingMore ? 'fa-circle-notch fa-spin text-purple-600' : 'fa-database text-purple-600'"></i>
                    <span x-text="isLoadingMore ? 'Mengambil data dari server...' : 'Muat 250 Data Terdahulu dari Database'"></span>
                </button>
            </div>
        </div>

        <!-- 7. Forensic Evidence Viewer Modal (Full Resolution Modal) -->
        <div x-show="showForensicModal" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-3 md:p-6 bg-black/85 backdrop-blur-md"
             @keydown.escape.window="showForensicModal = false">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-5xl w-full overflow-hidden shadow-2xl relative flex flex-col max-h-[92vh]"
                 @click.outside="showForensicModal = false">
                
                <!-- Modal Header -->
                <div class="p-3.5 px-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/70">
                    <div class="flex items-center gap-2.5">
                        <template x-if="activeForensicEvent?.event_type === 'missing_item'">
                            <span class="px-2 py-0.5 rounded text-[11px] font-extrabold bg-rose-600 text-white">
                                🚨 BARANG HILANG
                            </span>
                        </template>
                        <template x-if="activeForensicEvent?.event_type !== 'missing_item'">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-600 text-white uppercase" x-text="activeForensicEvent?.event_type"></span>
                        </template>
                        <h3 class="text-sm font-black text-white" x-text="activeForensicEvent?.cctv_name"></h3>
                        <span class="text-xs text-slate-400" x-text="'(' + activeForensicEvent?.building_name + ')'"></span>
                    </div>

                    <button @click="showForensicModal = false" class="text-slate-400 hover:text-white text-base w-7 h-7 flex items-center justify-center rounded-lg hover:bg-slate-800 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div class="p-4 md:p-5 overflow-y-auto space-y-4 flex-1">
                    
                    <!-- Narrative & Meta Header -->
                    <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700/80 text-xs">
                        <p class="text-slate-200 text-sm font-semibold leading-relaxed" x-text="activeForensicEvent?.narrative"></p>
                        
                        <div class="mt-2.5 flex flex-wrap items-center gap-2 text-[11px] text-slate-300 pt-2 border-t border-slate-700/50">
                            <span>🕒 Waktu Deteksi: <strong class="text-white" x-text="activeForensicEvent?.event_time + ' WIB (' + activeForensicEvent?.event_date + ')'"></strong></span>
                            <span>•</span>
                            <span>Objek: <strong class="text-white capitalize" x-text="activeForensicEvent?.object_type + ' (' + activeForensicEvent?.color + ')'"></strong></span>
                            <span>•</span>
                            <span>Confidence: <strong class="text-purple-400" x-text="activeForensicEvent?.confidence + '%'"></strong></span>
                            <template x-if="activeForensicEvent?.last_seen_time">
                                <span class="text-rose-400 font-bold">• Terakhir Stabil: <span x-text="activeForensicEvent?.last_seen_time + ' WIB'"></span></span>
                            </template>
                        </div>
                    </div>

                    <!-- 3 Forensic Visual Snapshots (Khusus Missing Item) -->
                    <template x-if="activeForensicEvent?.event_type === 'missing_item'">
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <i class="fas fa-camera text-slate-500"></i>
                                Komparasi Kronologis (Sebelum, Saat Kejadian, Sesudah)
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <!-- 1. Sebelum -->
                                <div class="relative rounded-xl overflow-hidden border border-emerald-500/80 group bg-black">
                                    <img :src="activeForensicEvent?.snapshot_before_url" class="w-full h-56 object-contain bg-black">
                                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded bg-black/80 text-[10px] font-bold text-emerald-400 border border-emerald-500/50">
                                        1. SEBELUM (STABIL)
                                    </div>
                                </div>

                                <!-- 2. Saat Kejadian -->
                                <div class="relative rounded-xl overflow-hidden border border-amber-500/80 group bg-black">
                                    <img :src="activeForensicEvent?.snapshot_url" class="w-full h-56 object-contain bg-black">
                                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded bg-amber-600 text-[10px] font-bold text-white shadow-xs">
                                        2. SAAT KEJADIAN (INTERAKSI)
                                    </div>
                                </div>

                                <!-- 3. Sesudah -->
                                <div class="relative rounded-xl overflow-hidden border border-rose-500/80 group bg-black">
                                    <img :src="activeForensicEvent?.snapshot_after_url" class="w-full h-56 object-contain bg-black">
                                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded bg-rose-600 text-[10px] font-bold text-white shadow-xs">
                                        3. SESUDAH (HILANG / KOSONG)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- 1 Snapshot (Deteksi Normal) -->
                    <template x-if="activeForensicEvent?.event_type !== 'missing_item' && activeForensicEvent?.snapshot_url">
                        <div class="rounded-xl overflow-hidden border border-slate-800 bg-black flex items-center justify-center p-2">
                            <img :src="activeForensicEvent?.snapshot_url" class="max-h-[60vh] w-auto object-contain rounded-lg">
                        </div>
                    </template>
                </div>

                <!-- Modal Footer -->
                <div class="p-3 px-5 border-t border-slate-800 bg-slate-950/70 flex items-center justify-between">
                    <span class="text-xs text-slate-500">ID Event: #AI-<span x-text="activeForensicEvent?.id"></span></span>
                    
                    <div class="flex items-center gap-2">
                        <a :href="'{{ route('playback.index') }}?cctv_id=' + activeForensicEvent?.cctv_id" 
                           target="_blank"
                           class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-xl text-xs font-bold bg-purple-600 hover:bg-purple-700 text-white transition-colors shadow-xs">
                            <i class="fas fa-circle-play mr-0.5"></i> Putar Rekaman Video (Playback)
                        </a>
                        <button type="button" @click="showForensicModal = false"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors">
                            Tutup
                        </button>
                    </div>
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

                // Time Filter States - Default Hari Ini (Today)
                timePreset: 'today', // 'today', 'yesterday', 'last_7_days', 'all', 'custom'
                showCustomTime: false,
                customStartDate: '',
                customEndDate: '',
                customStartTime: '',
                customEndTime: '',

                // View Mode ('table' | 'cards')
                viewMode: 'table',

                // Narrative Summary
                summaryText: 'Aktivitas Normal Terpantau: Tercatat pergerakan rutin kendaraan di area gerbang dan orang melintas. Terdeteksi beberapa objek barang berharga yang dipantau stabil di area kamera.',
                summarySource: 'AI Synthesizer',
                isGeneratingSummary: false,

                // Forensic Evidence Modal
                showForensicModal: false,
                activeForensicEvent: null,

                // Pagination
                currentPage: 1,
                perPage: 15,
                serverPage: 1,
                hasMoreServerData: false,
                isLoadingMore: false,
                isLoadingEvents: false,

                init() {
                    this.timePreset = 'today';
                    this.applyFilters();
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

                handleTimePresetChange() {
                    if (this.timePreset === 'custom') {
                        this.showCustomTime = true;
                    } else {
                        this.showCustomTime = false;
                        this.applyFilters(true);
                    }
                },

                resetTimeFilter() {
                    this.timePreset = 'today';
                    this.showCustomTime = false;
                    this.customStartDate = '';
                    this.customEndDate = '';
                    this.customStartTime = '';
                    this.customEndTime = '';
                    this.applyFilters(true);
                },

                filterByType(type) {
                    this.selectedEventType = type;
                    this.selectedObject = 'all';
                    this.currentPage = 1;
                    this.applyFilters(true);
                },

                setObjectFilter(obj) {
                    this.selectedObject = obj;
                    if (obj !== 'all' && (obj === 'laptop' || obj === 'backpack') && this.selectedEventType === 'vehicle_flow') {
                        this.selectedEventType = 'all';
                    }
                    this.currentPage = 1;
                    this.applyFilters(true);
                },

                async applyFilters(forceServer = false) {
                    this.currentPage = 1;
                    const q = this.searchQuery.toLowerCase().trim();
                    const cctv = this.selectedCctv;
                    const obj = this.selectedObject;
                    const type = this.selectedEventType;
                    const preset = this.timePreset;

                    // Hitung tanggal acuan lokal browser (WIB) untuk preset
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const day = String(now.getDate()).padStart(2, '0');
                    const todayStr = `${year}-${month}-${day}`;

                    const yDate = new Date(now);
                    yDate.setDate(yDate.getDate() - 1);
                    const yYear = yDate.getFullYear();
                    const yMonth = String(yDate.getMonth() + 1).padStart(2, '0');
                    const yDay = String(yDate.getDate()).padStart(2, '0');
                    const yesterdayStr = `${yYear}-${yMonth}-${yDay}`;

                    const sevenDaysAgo = new Date(now);
                    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);

                    // 1. Filter di memori lokal browser
                    this.filteredEvents = this.events.filter(item => {
                        // Filter CCTV
                        if (cctv && item.cctv_id != cctv) return false;

                        // Filter Event Type
                        if (type !== 'all' && item.event_type !== type) return false;

                        // Filter Object (grup cerdas)
                        if (obj === 'laptop') {
                            if (!['laptop', 'cell phone'].includes(item.object_type)) return false;
                        } else if (obj === 'backpack') {
                            if (!['backpack', 'handbag', 'suitcase'].includes(item.object_type)) return false;
                        } else if (obj === 'car') {
                            if (!['car', 'truck', 'bus'].includes(item.object_type)) return false;
                        } else if (obj !== 'all' && item.object_type !== obj) {
                            return false;
                        }

                        // Filter Waktu Preset
                        const itemDate = item.date_ymd || (item.raw_datetime ? item.raw_datetime.slice(0, 10) : '');
                        const itemTime = item.event_time || (item.raw_datetime ? item.raw_datetime.slice(11, 19) : '');

                        if (preset === 'today') {
                            if (itemDate && itemDate !== todayStr) return false;
                        } else if (preset === 'yesterday') {
                            if (itemDate && itemDate !== yesterdayStr) return false;
                        } else if (preset === 'last_7_days') {
                            if (itemDate) {
                                const d = new Date(itemDate);
                                if (d < sevenDaysAgo) return false;
                            }
                        } else if (preset === 'custom') {
                            if (this.customStartDate && itemDate && itemDate < this.customStartDate) return false;
                            if (this.customEndDate && itemDate && itemDate > this.customEndDate) return false;
                            if (this.customStartTime && itemTime && itemTime < this.customStartTime) return false;
                            if (this.customEndTime && itemTime && itemTime > this.customEndTime) return false;
                        }

                        // Search Query
                        if (q) {
                            const hay = (item.narrative + ' ' + item.object_type + ' ' + item.color + ' ' + item.cctv_name + ' ' + item.building_name).toLowerCase();
                            if (!hay.includes(q)) return false;
                        }

                        return true;
                    });

                    // 2. Jika filter aktif ATAU hasil lokal tidak ditemukan, ambil real-time dari database server!
                    if (forceServer || this.filteredEvents.length === 0 || type === 'missing_item' || preset === 'custom') {
                        await this.fetchFilteredFromServer();
                    }
                },

                async fetchFilteredFromServer() {
                    this.isLoadingEvents = true;
                    try {
                        const params = new URLSearchParams({
                            limit: 250,
                            cctv_id: this.selectedCctv || '',
                            event_type: this.selectedEventType || 'all',
                            object_type: this.selectedObject || 'all',
                            time_preset: this.timePreset || 'all',
                            start_date: this.customStartDate || '',
                            end_date: this.customEndDate || '',
                            start_time: this.customStartTime || '',
                            end_time: this.customEndTime || '',
                            search: this.searchQuery || ''
                        });
                        const resp = await fetch(`{{ route('intelligent.events') }}?${params.toString()}`);
                        const res = await resp.json();
                        if (res.status === 'success' && res.data) {
                            const existingIds = new Set(this.events.map(e => e.id));
                            const newEvents = res.data.filter(e => !existingIds.has(e.id));
                            this.events = [...newEvents, ...this.events];
                            this.filteredEvents = res.data;
                            if (res.stats) {
                                this.stats = res.stats;
                            }
                            this.currentPage = 1;
                        }
                    } catch (e) {
                        console.error('Gagal mengambil data terfilter dari server:', e);
                    } finally {
                        this.isLoadingEvents = false;
                    }
                },

                openForensicModal(event) {
                    if (!event) return;
                    this.activeForensicEvent = event;
                    this.showForensicModal = true;
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
                        const params = new URLSearchParams({
                            limit: 250,
                            page: this.serverPage,
                            cctv_id: this.selectedCctv || '',
                            event_type: this.selectedEventType || 'all',
                            object_type: this.selectedObject || 'all',
                            time_preset: this.timePreset || 'today',
                            start_date: this.customStartDate || '',
                            end_date: this.customEndDate || '',
                            start_time: this.customStartTime || '',
                            end_time: this.customEndTime || '',
                            search: this.searchQuery || ''
                        });
                        const resp = await fetch(`{{ route('intelligent.events') }}?${params.toString()}`);
                        const res = await resp.json();
                        if (res.status === 'success' && res.data && res.data.length > 0) {
                            const existingIds = new Set(this.events.map(e => e.id));
                            const newEvents = res.data.filter(e => !existingIds.has(e.id));
                            this.events = [...this.events, ...newEvents];
                            if (res.stats) {
                                this.stats = res.stats;
                            }
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
