<x-app-layout>
    <div class="font-body-md text-slate-800 pb-32 pt-6 px-6 max-w-[1600px] mx-auto" x-data="{ 
        camFilter: 'all', 
        searchQuery: '',
        sortCol: 'nama',
        sortAsc: true,
        page: 1, 
        perPage: 20,
        async reloadCameras() {
            try {
                let res = await fetch('{{ route('events.camerasJson') }}');
                let data = await res.json();
                this.cameras = data;
            } catch (e) { console.error('Failed to reload cameras', e); }
        },
        cameras: [
            @foreach($cameras as $cam)
            {
                id: {{ $cam->id }},
                nama: '{{ addslashes($cam->nama_cctv) }}',
                ip: '{{ $cam->ip }}',
                port: '{{ $cam->onvif_port ?? 80 }}',
                onvif_status: '{{ $cam->onvif_status ?? ( (!empty($cam->onvif_user) || !empty($cam->onvif_password)) ? "configured" : "unconfigured" ) }}',
                onvif_error: '{{ addslashes($cam->onvif_error ?? "") }}',
                editUrl: '{{ route('cctv.edit', $cam->id) }}'
            },
            @endforeach
        ],
        get filteredCameras() {
            let filtered = this.cameras.filter(c => {
                let matchFilter = true;
                if (this.camFilter === 'online') matchFilter = c.onvif_status === 'online';
                if (this.camFilter === 'failed') matchFilter = c.onvif_status === 'failed';
                if (this.camFilter === 'configured') matchFilter = c.onvif_status === 'configured';
                if (this.camFilter === 'unconfigured') matchFilter = c.onvif_status === 'unconfigured';
                
                let searchLower = this.searchQuery.toLowerCase();
                let matchSearch = this.searchQuery === '' || 
                                  c.nama.toLowerCase().includes(searchLower) || 
                                  c.ip.toLowerCase().includes(searchLower);
                return matchFilter && matchSearch;
            });

            return filtered.sort((a, b) => {
                let valA = a[this.sortCol];
                let valB = b[this.sortCol];
                
                if (valA < valB) return this.sortAsc ? -1 : 1;
                if (valA > valB) return this.sortAsc ? 1 : -1;
                return 0;
            });
        },
        get paginatedCameras() {
            let start = (this.page - 1) * this.perPage;
            return this.filteredCameras.slice(start, start + this.perPage);
        },
        get totalPages() {
            return Math.ceil(this.filteredCameras.length / this.perPage) || 1;
        },
        sortBy(col) {
            if (this.sortCol === col) {
                this.sortAsc = !this.sortAsc;
            } else {
                this.sortCol = col;
                this.sortAsc = true;
            }
            this.page = 1;
        }
    }">
        <!-- Header -->
        <header class="mb-6">
            <div class="flex items-center text-xs text-slate-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-cyan-600"><i class="fas fa-home"></i></a>
                <span class="mx-2">/</span>
                <span class="text-slate-500">Manajemen</span>
                <span class="mx-2">/</span>
                <a href="{{ route('events.index') }}" class="text-slate-500 hover:text-cyan-600">ONVIF Status</a>
                <span class="mx-2">/</span>
                <span class="text-slate-700 font-medium">Expanded</span>
            </div>
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
                        <a href="{{ route('events.index') }}" class="text-slate-400 hover:text-cyan-600 transition-colors">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        ONVIF Status (Full View)
                    </h1>
                    <p class="text-slate-500 mt-1 text-sm">Tampilan penuh tabel status ONVIF dengan 20 data per halaman.</p>
                </div>
            </div>
        </header>

        <main>
            <!-- Cameras ONVIF Status Table -->
            <section class="bg-white/50 glass-effect border border-cyan-100 rounded-xl overflow-hidden shadow-sm">
                <div class="px-5 py-3 border-b border-cyan-100 bg-white/30 flex flex-col md:flex-row md:justify-between items-start md:items-center gap-4 sticky top-0 z-20">
                    <div class="flex items-center gap-4">
                        <div class="flex bg-white border border-cyan-100 rounded text-[10px] font-bold shadow-sm overflow-hidden">
                            <button @click="camFilter = 'all'; page = 1" :class="camFilter === 'all' ? 'bg-cyan-600 text-white' : 'text-slate-600 hover:bg-slate-50'" class="px-3 py-1 transition-colors">ALL</button>
                            <button @click="camFilter = 'online'; page = 1" :class="camFilter === 'online' ? 'bg-emerald-600 text-white' : 'text-slate-600 hover:bg-slate-50'" class="px-3 py-1 transition-colors border-l border-cyan-100">ONLINE</button>
                            <button @click="camFilter = 'failed'; page = 1" :class="camFilter === 'failed' ? 'bg-red-600 text-white' : 'text-slate-600 hover:bg-slate-50'" class="px-3 py-1 transition-colors border-l border-cyan-100">FAILED</button>
                            <button @click="camFilter = 'configured'; page = 1" :class="camFilter === 'configured' ? 'bg-blue-500 text-white' : 'text-slate-600 hover:bg-slate-50'" class="px-3 py-1 transition-colors border-l border-cyan-100">CONFIG</button>
                            <button @click="camFilter = 'unconfigured'; page = 1" :class="camFilter === 'unconfigured' ? 'bg-amber-500 text-white' : 'text-slate-600 hover:bg-slate-50'" class="px-3 py-1 transition-colors border-l border-cyan-100">UNCONFIGURED</button>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 w-full md:w-auto h-[34px]">
                        <!-- Search Box -->
                        <div class="relative flex-1 md:w-64 h-full">
                            <input type="text" x-model="searchQuery" @input="page = 1" placeholder="Cari Kamera/IP..." class="w-full h-full text-xs pl-8 pr-3 border border-cyan-200 rounded-lg focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400 shadow-sm">
                            <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        </div>
                        <button @click="reloadCameras()" class="px-2.5 h-full flex items-center justify-center text-slate-400 hover:text-cyan-600 transition-colors bg-white border border-cyan-100 rounded shadow-sm" title="Reload Tabel">
                            <i class="fas fa-redo-alt text-xs"></i>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto min-h-[500px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-slate-50/90 backdrop-blur-sm shadow-sm z-10">
                            <tr class="text-[11px] text-slate-500 uppercase border-b border-cyan-100 font-medium">
                                <th class="px-5 py-2.5 font-medium cursor-pointer hover:bg-slate-100 transition-colors" @click="sortBy('onvif_status')">
                                    Status ONVIF 
                                    <i class="fas ml-1" :class="sortCol === 'onvif_status' ? (sortAsc ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort text-slate-300'"></i>
                                </th>
                                <th class="px-4 py-2.5 font-medium cursor-pointer hover:bg-slate-100 transition-colors" @click="sortBy('nama')">
                                    Nama Kamera
                                    <i class="fas ml-1" :class="sortCol === 'nama' ? (sortAsc ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort text-slate-300'"></i>
                                </th>
                                <th class="px-4 py-2.5 font-medium cursor-pointer hover:bg-slate-100 transition-colors" @click="sortBy('ip')">
                                    IP Address
                                    <i class="fas ml-1" :class="sortCol === 'ip' ? (sortAsc ? 'fa-sort-up' : 'fa-sort-down') : 'fa-sort text-slate-300'"></i>
                                </th>
                                <th class="px-4 py-2.5 font-medium">ONVIF Port</th>
                                <th class="px-5 py-2.5 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cyan-100/50 text-sm">
                            <template x-for="cam in paginatedCameras" :key="cam.id">
                                <tr class="hover:bg-cyan-50/50 transition-colors" :class="cam.onvif_status === 'failed' ? 'bg-red-50/30' : (cam.onvif_status === 'unconfigured' ? 'bg-amber-50/30' : '')">
                                    <td class="px-5 py-2">
                                        <div x-show="cam.onvif_status === 'online'" class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full shadow-[0_0_5px_rgba(16,185,129,0.5)]"></span>
                                            <span class="text-[11px] text-emerald-700 font-medium">ONLINE</span>
                                        </div>
                                        <div x-show="cam.onvif_status === 'failed'" class="flex items-center gap-2 group relative">
                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse shadow-[0_0_5px_rgba(239,68,68,0.5)]"></span>
                                            <span class="text-[11px] text-red-700 font-medium cursor-help" :title="cam.onvif_error">FAILED</span>
                                        </div>
                                        <div x-show="cam.onvif_status === 'configured'" class="flex items-center gap-2" title="Menunggu koneksi dari Agent">
                                            <span class="w-1.5 h-1.5 bg-blue-500 rounded-full shadow-[0_0_5px_rgba(59,130,246,0.5)]"></span>
                                            <span class="text-[11px] text-blue-700 font-medium">CONFIGURED</span>
                                        </div>
                                        <div x-show="cam.onvif_status === 'unconfigured'" class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full shadow-[0_0_5px_rgba(245,158,11,0.5)]"></span>
                                            <span class="text-[11px] text-amber-700 font-medium">UNCONFIGURED</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-video text-xs" :class="cam.onvif_status === 'online' ? 'text-emerald-500' : (cam.onvif_status === 'failed' ? 'text-red-500' : 'text-slate-400')"></i>
                                            <span class="text-slate-700" x-text="cam.nama"></span>
                                        </div>
                                        <div x-show="cam.onvif_status === 'failed' && cam.onvif_error" class="text-[10px] text-red-500 mt-0.5 truncate max-w-xs" x-text="cam.onvif_error" :title="cam.onvif_error"></div>
                                    </td>
                                    <td class="px-4 py-2">
                                        <code class="font-mono text-xs text-slate-600" x-text="cam.ip"></code>
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="bg-slate-100 px-1.5 py-0.5 rounded text-[10px] text-slate-600" x-text="cam.port"></span>
                                    </td>
                                    <td class="px-5 py-2 text-right">
                                        <a :href="cam.editUrl" class="p-1.5 text-slate-400 hover:text-cyan-600 hover:bg-cyan-50 rounded-lg transition-colors inline-block" title="Edit Kamera">
                                            <i class="fas fa-cog text-xs"></i>
                                        </a>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredCameras.length === 0">
                                <td colspan="5" class="px-5 py-8 text-center text-slate-500 text-sm">
                                    Tidak ada data kamera.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-5 py-3 bg-white/30 border-t border-cyan-100 flex items-center justify-between text-xs text-slate-500 sticky bottom-0 bg-slate-50">
                    <div>
                        Menampilkan <span x-text="filteredCameras.length > 0 ? (page - 1) * perPage + 1 : 0"></span> - <span x-text="Math.min(page * perPage, filteredCameras.length)"></span> dari <span x-text="filteredCameras.length"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="page--" :disabled="page === 1" class="px-2 py-1 bg-white border border-cyan-200 rounded hover:bg-slate-50 disabled:opacity-50 transition-colors">&laquo; Prev</button>
                        <button @click="page++" :disabled="page === totalPages || totalPages === 0" class="px-2 py-1 bg-white border border-cyan-200 rounded hover:bg-slate-50 disabled:opacity-50 transition-colors">Next &raquo;</button>
                    </div>
                </div>
            </section>
        </main>
    </div>
</x-app-layout>
