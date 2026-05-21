<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-500">Infrastruktur</span>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Master Server</span>
            </div>
        </div>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Server Nodes</h2>
                <p class="text-slate-500">Kelola node server perekam (NVR) terdistribusi.</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="document.getElementById('deployModal').classList.remove('hidden')" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-cyan-500 hover:border-cyan-300 transition-all shadow-sm" title="Panduan Instalasi Node">
                    <i class="fas fa-info-circle text-lg"></i>
                </button>
                <a href="{{ route('servers.create') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium shadow-lg hover:shadow-cyan-500/50 transition-all flex items-center gap-2">
                    <i class="fas fa-server"></i> Tambah Node
                </a>
            </div>
        </div>

        <!-- Deploy Modal -->
        <div id="deployModal" class="fixed inset-0 z-[100] flex items-center justify-center hidden bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="text-xl font-bold text-slate-800"><i class="fas fa-rocket text-cyan-500 mr-2"></i> Panduan Deploy Node Baru</h3>
                    <button onclick="document.getElementById('deployModal').classList.add('hidden')" class="text-slate-400 hover:text-red-500 transition">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto flex-1 custom-scrollbar text-sm text-slate-600 space-y-6">
                    <p>Ikuti langkah-langkah berikut untuk menambahkan Node CCTV baru (misal: <code>node02</code> di IP <code>10.69.69.42</code>).</p>
                    
                    <div class="space-y-4">
                        <h4 class="font-bold text-slate-800">Tahap 1: Persiapan di Server Node Baru (10.69.69.42)</h4>
                        <ol class="list-decimal pl-5 space-y-2">
                            <li>Login ke server node baru via SSH.</li>
                            <li>Pastikan OS menggunakan Ubuntu 22.04 / 24.04.</li>
                            <li>Download script instalasi node:<br>
                                <code class="block bg-slate-800 text-cyan-400 p-2 rounded mt-1 select-all">wget -O install_node.sh https://raw.githubusercontent.com/.../install_node.sh</code>
                                <em class="text-xs text-slate-400">*Sesuaikan URL dengan lokasi repository script instalasi Anda. Anda bisa memindahkan file <code>scripts/install_node.sh</code> dari master ke node baru.</em>
                            </li>
                            <li>Jalankan script instalasi:<br>
                                <code class="block bg-slate-800 text-cyan-400 p-2 rounded mt-1 select-all">chmod +x install_node.sh && sudo ./install_node.sh</code>
                            </li>
                            <li>Saat diminta, masukkan IP Master Server (<code>10.69.69.21</code>).</li>
                        </ol>

                        <h4 class="font-bold text-slate-800 pt-4">Tahap 2: Konfigurasi Nginx di Master Server (10.69.69.21)</h4>
                        <p>Setelah node berhasil diinstall, Anda harus memberitahu Master Server (Nginx) tentang keberadaan node ini agar WebRTC dan Storage dapat diakses.</p>
                        <ol class="list-decimal pl-5 space-y-2">
                            <li>Buka file Nginx di master:<br>
                                <code class="block bg-slate-800 text-cyan-400 p-2 rounded mt-1 select-all">sudo nano /etc/nginx/sites-available/cctv-unpad.conf</code>
                            </li>
                            <li>Tambahkan blok konfigurasi untuk Node 2 (sesuaikan ID Database dengan angka pada path Nginx):<br>
<pre class="bg-slate-800 text-green-400 p-3 rounded mt-1 text-xs overflow-x-auto"><code># Whitelist WebSocket Node 2
location = /node2/api/ws {
    rewrite ^/node2/(.*) /$1 break;
    proxy_pass http://10.69.69.42:80;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
}

# Whitelist Static Assets Node 2
location ~ ^/node2/.*\.(js|css|json|png|jpg|ico)$ {
    rewrite ^/node2/(.*) /$1 break;
    proxy_pass http://10.69.69.42:80;
    proxy_set_header Host $host;
}

# Storage & Playback Node 2
location /node2/storage/ {
    auth_request /auth-video;
    rewrite ^/node2/(.*) /$1 break;
    proxy_pass http://10.69.69.42:80;
    proxy_set_header Host $host;
}

location /node2/playback/ {
    auth_request /auth-video;
    rewrite ^/node2/(.*) /$1 break;
    proxy_pass http://10.69.69.42:80;
    proxy_set_header Host $host;
    proxy_buffering off;
}

# Live Stream WebRTC Node 2
location /node2/ {
    auth_request /auth-video;
    rewrite ^/node2/(.*) /$1 break;
    proxy_pass http://10.69.69.42:80;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
}</code></pre>
                            </li>
                            <li>Uji dan reload Nginx:<br>
                                <code class="block bg-slate-800 text-cyan-400 p-2 rounded mt-1 select-all">sudo nginx -t && sudo systemctl reload nginx</code>
                            </li>
                        </ol>

                        <h4 class="font-bold text-slate-800 pt-4">Tahap 3: Daftarkan di Aplikasi</h4>
                        <ol class="list-decimal pl-5 space-y-2">
                            <li>Klik tombol <strong>Tambah Node</strong> di halaman ini.</li>
                            <li>Masukkan Nama: <code>Node 02</code></li>
                            <li>Masukkan IP: <code>10.69.69.42</code></li>
                            <li>Pastikan ID yang dihasilkan oleh database sesuai dengan angka di blok Nginx (misal Nginx <code>/node2/</code> = Database ID <code>2</code>). Jika ID Database melompat (misal 3), ubah path Nginx menjadi <code>/node3/</code>.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-effect rounded-2xl p-6 border border-cyan-100 overflow-hidden" x-data="{
            loading: false,
            sortBy: '{{ request('sort_by', 'created_at') }}',
            sortDir: '{{ request('sort_dir', 'desc') }}',
            handleSort(field) {
                if (this.sortBy === field) {
                    this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortBy = field;
                    this.sortDir = 'asc';
                }
                this.$nextTick(() => { this.updateTable(); });
            },
            async updateTable() {
                this.loading = true;
                const form = document.getElementById('filter-form');
                const params = new URLSearchParams(new FormData(form)).toString();
                try {
                    const res = await fetch(`{{ route('servers.index') }}?${params}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    document.getElementById('server-table-body').innerHTML = doc.getElementById('server-table-body').innerHTML;
                    document.getElementById('pagination-container').innerHTML = doc.getElementById('pagination-container').innerHTML;
                    window.history.pushState({}, '', `?${params}`);
                } finally {
                    this.loading = false;
                }
            }
        }">
            
        <!-- Seamless Search Bar -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <form id="filter-form" action="{{ route('servers.index') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full" @submit.prevent="updateTable()">
                <input type="hidden" name="sort_by" :value="sortBy">
                <input type="hidden" name="sort_dir" :value="sortDir">
                <div class="relative flex-1 min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-slate-400 text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           @input.debounce.500ms="updateTable()"
                           placeholder="Cari nama server atau IP..." 
                           class="w-full pl-9 pr-4 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white/50 shadow-sm">
                    <div x-show="loading" class="absolute inset-y-0 right-3 flex items-center">
                        <i class="fas fa-circle-notch fa-spin text-cyan-500 text-xs"></i>
                    </div>
                </div>

                @if(request('search'))
                    <a href="{{ route('servers.index') }}" class="text-[10px] font-bold text-slate-400 hover:text-red-500 uppercase tracking-wider flex items-center transition-colors ml-2">
                        <i class="fas fa-times-circle mr-1"></i> Reset
                    </a>
                @endif
            </form>
        </div>


            <table class="w-full text-left border-collapse">
                <thead class="text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100 select-none">
                    <tr>
                        <th class="pb-4 pl-4 font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('name')">
                            Nama Server
                            <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'name' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                        </th>
                        <th class="pb-4 font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('ip_address')">
                            IP Address
                            <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'ip_address' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                        </th>
                        <th class="pb-4 font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('location')">
                            Lokasi
                            <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'location' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                        </th>
                        <th class="pb-4 text-center font-semibold">Beban</th>
                        <th class="pb-4 text-center font-semibold cursor-pointer hover:text-cyan-500 transition-colors group" @click="handleSort('is_active')">
                            Status
                            <i class="fas text-[10px] ml-1 transition-opacity" :class="sortBy === 'is_active' ? (sortDir === 'asc' ? 'fa-sort-up text-cyan-500' : 'fa-sort-down text-cyan-500') : 'fa-sort text-slate-300 opacity-0 group-hover:opacity-100'"></i>
                        </th>
                        <th class="pb-4 pr-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody id="server-table-body" class="text-sm text-slate-600">
                    @forelse ($servers as $server)
                    <tr class="hover:bg-slate-50/50 transition border-b border-slate-50 last:border-none">
                        <td class="py-4 pl-4 font-bold text-slate-700">{{ $server->name }}</td>
                        <td class="py-4 font-mono text-cyan-600">{{ $server->ip_address }}</td>
                        <td class="py-4">{{ $server->location ?? '-' }}</td>
                        <td class="py-4 text-center">
                            <span class="px-2 py-1 bg-slate-100 rounded text-xs font-bold">{{ $server->cctvs_count }} Kamera</span>
                        </td>
                        <td class="py-4 text-center">
                            @if($server->is_active)
                                <span class="px-2 py-1 bg-green-100 text-green-600 rounded-full text-xs font-bold">Active</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-600 rounded-full text-xs font-bold">Inactive</span>
                            @endif
                        </td>
                        <td class="py-4 pr-4 text-right flex justify-end gap-2">
                            <a href="{{ route('servers.edit', $server->id) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border hover:border-cyan-300 hover:text-cyan-600 text-slate-400 transition shadow-sm"><i class="fas fa-pencil-alt"></i></a>
                            
                            <form action="{{ route('servers.destroy', $server->id) }}" method="POST" onsubmit="return confirm('Yakin hapus server ini? Pastikan kosong dulu.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white border hover:border-red-300 hover:text-red-600 text-slate-400 transition shadow-sm"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-10 text-center text-slate-400">Belum ada server node.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div id="pagination-container" class="mt-4">{{ $servers->links() }}</div>
        </div>
    </main>
</x-app-layout>