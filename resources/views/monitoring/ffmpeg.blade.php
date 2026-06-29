<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        <!-- Breadcrumb -->
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-500">Manajemen</span>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">System Health</span>
            </div>
        </div>

        <div class="mb-8">
            <h2 class="text-3xl font-bold text-slate-800 mb-2">System Health Monitoring</h2>
            <p class="text-slate-500 font-medium">Status perekaman kamera real-time di setiap node server.</p>
        </div>

        <!-- Server Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            @foreach($serverStats as $stat)
            <div class="glass-effect overflow-hidden rounded-2xl border border-cyan-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">{{ $stat->name }}</h3>
                        <p class="text-sm text-slate-400 font-mono">{{ $stat->ip }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-bold text-cyan-600">{{ $stat->active }}</span>
                        <span class="text-slate-400 font-medium">/ {{ $stat->total }} Merekam</span>
                    </div>
                </div>
                
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="bg-cyan-500 h-2 rounded-full transition-all duration-500" style="width: {{ $stat->total > 0 ? ($stat->active / $stat->total) * 100 : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Wrapper for AlpineJS Context -->
        <div x-data="{
            loading: false,
            async updateTable() {
                this.loading = true;
                const form = document.getElementById('filter-form');
                const params = new URLSearchParams(new FormData(form)).toString();
                
                try {
                    const res = await fetch(`{{ route('ffmpeg.monitor') }}?${params}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    
                    document.getElementById('cctv-table-body').innerHTML = doc.getElementById('cctv-table-body').innerHTML;
                    document.getElementById('pagination-container').innerHTML = doc.getElementById('pagination-container').innerHTML;
                    
                    window.history.pushState({}, '', `?${params}`);
                } catch (e) {
                    console.error('Filter error:', e);
                } finally {
                    this.loading = false;
                }
            }
        }">
            <!-- Filter Bar -->
            <div class="glass-effect rounded-2xl p-6 border border-cyan-100 mb-6">
                <form id="filter-form" action="{{ route('ffmpeg.monitor') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full" @submit.prevent="updateTable()">
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[240px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-slate-400 text-xs"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               @input.debounce.500ms="updateTable()"
                               placeholder="Cari nama kamera, kode, atau IP..." 
                               class="w-full pl-9 pr-10 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white/50 shadow-sm">
                        
                        <div x-show="loading" class="absolute inset-y-0 right-3 flex items-center">
                            <i class="fas fa-circle-notch fa-spin text-cyan-500 text-xs"></i>
                        </div>
                    </div>

                    <!-- Server Node Dropdown -->
                    <div class="relative">
                        <select name="server_id" @change="updateTable()" 
                                class="w-48 pl-4 pr-10 py-2 rounded-xl border-slate-200 focus:ring-2 focus:ring-cyan-100 focus:border-cyan-400 transition-all text-sm bg-white cursor-pointer shadow-sm appearance-none">
                            <option value="">Semua Node</option>
                            @foreach($servers as $s)
                                <option value="{{ $s->id }}" {{ request('server_id') == $s->id ? 'selected' : '' }}>Node {{ $s->id }} ({{ $s->name }})</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-3 text-[10px] text-slate-400 pointer-events-none"></i>
                    </div>

                    <!-- Reset Button -->
                    @if(request()->anyFilled(['search', 'server_id']))
                        <a href="{{ route('ffmpeg.monitor') }}" class="text-[10px] font-bold text-slate-400 hover:text-red-500 uppercase tracking-wider flex items-center transition-colors ml-2">
                            <i class="fas fa-times-circle mr-1"></i> Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Detail Kamera Table -->
            <div class="glass-effect rounded-2xl p-6 border border-cyan-100">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-slate-400 text-xs uppercase tracking-wider border-b border-cyan-100 select-none">
                                <th class="pb-4 pl-4 font-semibold w-12">No.</th>
                                <th class="pb-4 font-semibold w-40">Server Node</th>
                                <th class="pb-4 font-semibold w-64">Nama Kamera</th>
                                <th class="pb-4 font-semibold w-36">IP Address</th>
                                <th class="pb-4 font-semibold w-32 text-center">Status</th>
                                <th class="pb-4 font-semibold w-48">Terakhir Rekam</th>
                                <th class="pb-4 font-semibold w-32">Ukuran File</th>
                                <th class="pb-4 pr-4 font-semibold w-56">File Terakhir</th>
                            </tr>
                        </thead>
                        <tbody id="cctv-table-body" class="text-sm text-slate-600">
                            @forelse($cctvs as $index => $cctv)
                                @php
                                    $isRecording = false;
                                    $lastUpdateText = 'Never';
                                    $fileSize = '-';
                                    $filename = '-';

                                    if ($cctv->latest_rec_created_at) {
                                        $createdTime = \Carbon\Carbon::parse($cctv->latest_rec_created_at);
                                        if ($createdTime->diffInMinutes(now()) < 25) {
                                            $isRecording = true;
                                        }
                                        $lastUpdateText = $createdTime->diffForHumans();
                                        $fileSize = $cctv->latest_rec_size_mb ? round($cctv->latest_rec_size_mb, 2) . ' MB' : '0 MB';
                                        $filename = $cctv->latest_rec_filename;
                                    }
                                @endphp
                                <tr class="hover:bg-cyan-50/50 transition-colors border-b border-slate-50 last:border-none">
                                    <td class="py-4 pl-4 font-medium text-slate-400">
                                        {{ ($cctvs->currentPage() - 1) * $cctvs->perPage() + $index + 1 }}
                                    </td>
                                    <td class="py-4 font-semibold text-slate-700">
                                        @if($cctv->server)
                                            Node {{ $cctv->server_id }} ({{ $cctv->server->name }})
                                        @else
                                            SINGLE / MASTER
                                        @endif
                                    </td>
                                    <td class="py-4">
                                        <div class="font-semibold text-slate-800">{{ $cctv->nama_cctv }}</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5 font-mono">Kode: {{ $cctv->kode_cctv }}</div>
                                    </td>
                                    <td class="py-4 font-mono text-xs">{{ $cctv->ip ?? '-' }}</td>
                                    <td class="py-4 text-center">
                                        @if($isRecording)
                                            <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold uppercase tracking-wider inline-block w-24">
                                                Recording
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 bg-slate-100 text-slate-500 rounded-full text-[10px] font-bold uppercase tracking-wider inline-block w-24">
                                                Idle
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 text-slate-500 font-medium">{{ $lastUpdateText }}</td>
                                    <td class="py-4 font-semibold text-slate-700">{{ $fileSize }}</td>
                                    <td class="py-4 pr-4 font-mono text-[10px] text-slate-400 truncate max-w-xs" title="{{ $filename }}">{{ $filename }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-slate-400">
                                        <i class="fas fa-heartbeat text-4xl text-slate-200 mb-3 block"></i>
                                        Tidak ada data kamera yang terhubung atau pencarian tidak cocok.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Container -->
                <div id="pagination-container" class="mt-6">
                    {{ $cctvs->links() }}
                </div>
            </div>
        </div>
    </main>
</x-app-layout>