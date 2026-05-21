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

        <div class="flex items-center justify-between mb-8" x-data="{ deployModalOpen: false }">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Server Nodes</h2>
                <p class="text-slate-500">Kelola node server perekam (NVR) terdistribusi.</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="deployModalOpen = true; document.body.classList.add('overflow-hidden');" class="px-4 py-2.5 rounded-xl bg-white text-slate-600 font-medium border border-slate-200 hover:bg-slate-50 hover:text-cyan-600 transition-all flex items-center gap-2 shadow-sm" title="Panduan Instalasi Node">
                    <i class="fas fa-book-open text-cyan-500"></i> Panduan Deploy
                </button>
                <a href="{{ route('servers.create') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium shadow-lg hover:shadow-cyan-500/50 transition-all flex items-center gap-2">
                    <i class="fas fa-plus"></i> Tambah Node
                </a>
            </div>

            <!-- Deploy Modal (Enhanced UI) -->
            <template x-teleport="body">
                <div x-show="deployModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 transition-opacity">
                    <div @click.away="deployModalOpen = false; document.body.classList.remove('overflow-hidden');" class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden relative">
                        
                        <!-- Modal Header -->
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-gradient-to-r from-slate-50 to-white">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-cyan-100 flex items-center justify-center text-cyan-600 shadow-inner">
                                    <i class="fas fa-server text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-slate-800">Panduan Deploy Node Baru</h3>
                                    <p class="text-xs text-slate-500 mt-1">Langkah-langkah menambahkan NVR worker ke dalam cluster.</p>
                                </div>
                            </div>
                            <button @click="deployModalOpen = false; document.body.classList.remove('overflow-hidden');" class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400 hover:bg-red-50 hover:text-red-500 transition">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>

                <!-- Modal Body -->
                <div class="p-0 overflow-y-auto flex-1 custom-scrollbar bg-slate-50/50">
                    
                    <div class="p-6 space-y-6">
                        
                        <!-- Tahap 1 -->
                        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                            <div class="bg-slate-100/50 px-5 py-3 border-b border-slate-200 flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-cyan-500 text-white flex items-center justify-center text-xs font-bold shrink-0 shadow">1</span>
                                <h4 class="font-bold text-slate-800 text-sm">Persiapan di Server Node Baru (misal: 10.69.69.42)</h4>
                            </div>
                            <div class="p-5 text-sm text-slate-600 space-y-4">
                                <p>Pastikan server baru menggunakan <strong class="text-slate-800">Ubuntu 22.04 LTS atau 24.04 LTS</strong> bersih (belum terinstall web server). Login via SSH, lalu jalankan perintah berikut:</p>
                                
                                <div class="space-y-4">
                                    <div class="border border-slate-100 rounded-lg p-3 bg-slate-50">
                                        <p class="mb-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2"><i class="fab fa-github text-slate-700 text-sm"></i> A. Clone Repository (Hanya folder Script & Nginx)</p>
                                        <pre class="bg-slate-900 border border-slate-700 text-cyan-300 p-3 rounded-lg text-xs overflow-x-auto font-mono leading-relaxed select-all"><code>mkdir -p ~/cctv-setup && cd ~/cctv-setup
git init
git remote add origin https://github.com/aayramdan05/cctv-v12.git
git config core.sparseCheckout true
echo "scripts/" >> .git/info/sparse-checkout
echo "nginx/" >> .git/info/sparse-checkout
git pull origin main</code></pre>
                                    </div>

                                    <div class="border border-slate-100 rounded-lg p-3 bg-slate-50">
                                        <p class="mb-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2"><i class="fas fa-terminal text-slate-700 text-sm"></i> B. Eksekusi Script Installer</p>
                                        <pre class="bg-slate-900 border border-slate-700 text-cyan-300 p-3 rounded-lg text-xs overflow-x-auto font-mono leading-relaxed select-all"><code>cd scripts
chmod +x install_node.sh
sudo ./install_node.sh</code></pre>
                                        <div class="mt-3 flex items-start gap-3 text-amber-700 bg-amber-50 p-3 rounded-lg border border-amber-200 shadow-sm">
                                            <i class="fas fa-exclamation-triangle mt-0.5 text-amber-500 text-lg"></i>
                                            <div class="text-xs leading-relaxed">
                                                <p class="font-bold mb-1">PENTING:</p>
                                                <ul class="list-disc pl-4 space-y-1">
                                                    <li>Masukkan <strong>IP Master Server</strong> (contoh: <code>10.69.69.21</code>).</li>
                                                    <li>Script akan otomatis menginstall <strong>go2rtc</strong> dan mendaftarkan service daemon.</li>
                                                    <li>Konfigurasi live streaming disimpan di <code>/home/aay/go2rtc.yaml</code>.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tahap 2 -->
                        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                            <div class="bg-slate-100/50 px-5 py-3 border-b border-slate-200 flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-cyan-500 text-white flex items-center justify-center text-xs font-bold shrink-0 shadow">2</span>
                                <h4 class="font-bold text-slate-800 text-sm">Daftarkan Node di Aplikasi</h4>
                            </div>
                            <div class="p-5 text-sm text-slate-600">
                                <p class="mb-3">Sebelum menyambungkan Nginx di Master, kita harus mendapatkan <strong>ID Server</strong> dari database aplikasi.</p>
                                <div class="bg-slate-50 border border-slate-100 rounded-lg p-4">
                                    <ol class="list-decimal pl-5 space-y-2">
                                        <li>Tutup panduan ini dan klik tombol <strong class="text-slate-800 bg-white px-2 py-0.5 border border-slate-200 rounded text-xs shadow-sm"><i class="fas fa-plus text-cyan-500 mr-1"></i> Tambah Node</strong>.</li>
                                        <li>Isi formulir dengan IP Node baru (contoh: <code>10.69.69.42</code>) dan nama (contoh: <code>Node 02</code>).</li>
                                        <li>Setelah disimpan, perhatikan tabel server. Lihat angka ID yang diberikan oleh database (misal <strong class="text-cyan-600 font-bold bg-cyan-50 px-1 rounded">ID: 2</strong>). Angka ini sangat krusial untuk langkah Nginx selanjutnya.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- Tahap 3 -->
                        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                            <div class="bg-slate-100/50 px-5 py-3 border-b border-slate-200 flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-cyan-500 text-white flex items-center justify-center text-xs font-bold shrink-0 shadow">3</span>
                                <h4 class="font-bold text-slate-800 text-sm">Routing Nginx di Master Server (10.69.69.21)</h4>
                            </div>
                            <div class="p-5 text-sm text-slate-600 space-y-4">
                                <p>Login SSH kembali ke <strong class="text-slate-800">Master Server</strong> (tempat aplikasi ini berada), lalu edit konfigurasi Nginx:</p>
                                <pre class="bg-slate-900 border border-slate-700 text-cyan-300 p-2.5 rounded-lg text-xs font-mono inline-block shadow-sm select-all"><code>sudo nano /etc/nginx/sites-available/cctv-unpad.conf</code></pre>
                                
                                <p class="leading-relaxed">Tambahkan blok berikut. <strong class="text-red-500">PENTING:</strong> Ganti tulisan <code>/node2/</code> sesuai dengan <strong class="text-slate-800">ID Database</strong> yang didapat pada Tahap 2, dan ganti <code>10.69.69.42</code> dengan IP Node baru Anda.</p>

<pre class="bg-slate-900 border border-slate-700 text-green-400 p-4 rounded-lg text-[11px] overflow-x-auto font-mono leading-relaxed shadow-inner"><code># =========================================================
#  NODE 2 (10.69.69.42)
# =========================================================

# 1. Whitelist WebSocket (di bagian atas file)
location = /node2/api/ws {
    rewrite ^/node2/(.*) /$1 break;
    proxy_pass http://10.69.69.42:80;
    proxy_http_version 1.1;
    proxy_set_header Upgrade \$http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host \$host;
    proxy_set_header X-Real-IP \$remote_addr;
    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
    proxy_set_header Origin "";
}

# 2. Whitelist Static Assets (di bagian atas file)
location ~ ^/node2/.*\.(js|css|json|png|jpg|ico)$ {
    rewrite ^/node2/(.*) /$1 break;
    proxy_pass http://10.69.69.42:80;
    proxy_set_header Host \$host;
    proxy_set_header X-Real-IP \$remote_addr;
}

# 3. Blok Utama (Storage, Playback, WebRTC - letakkan di tengah file)
location /node2/storage/ {
    auth_request /auth-video;
    rewrite ^/node2/(.*) /$1 break;
    proxy_pass http://10.69.69.42:80;
    proxy_set_header Host \$host;
    proxy_set_header X-Real-IP \$remote_addr;
    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto \$scheme;
}

location /node2/playback/ {
    auth_request /auth-video;
    rewrite ^/node2/(.*) /$1 break;
    proxy_pass http://10.69.69.42:80;
    proxy_set_header Host \$host;
    proxy_set_header X-Real-IP \$remote_addr;
    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
    proxy_buffering off;
}

location /node2/ {
    auth_request /auth-video;
    rewrite ^/node2/(.*) /$1 break;
    proxy_pass http://10.69.69.42:80;
    proxy_http_version 1.1;
    proxy_set_header Upgrade \$http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host \$host;
    proxy_set_header Origin "";
}</code></pre>
                                <p>Simpan file, pastikan syntax benar, lalu restart Nginx:</p>
                                <pre class="bg-slate-900 border border-slate-700 text-cyan-300 p-2.5 rounded-lg text-xs font-mono inline-block shadow-sm select-all"><code>sudo nginx -t && sudo systemctl reload nginx</code></pre>
                            </div>
                        </div>

                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="p-5 border-t border-slate-100 bg-white flex justify-end">
                    <button onclick="document.getElementById('deployModal').classList.add('hidden'); document.body.classList.remove('overflow-hidden');" class="px-6 py-2.5 rounded-xl bg-slate-800 text-white font-bold hover:bg-slate-700 transition shadow-lg hover:shadow-slate-800/30">
                        Paham & Tutup
                    </button>
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