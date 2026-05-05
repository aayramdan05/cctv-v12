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
            <a href="{{ route('servers.create') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium shadow-lg hover:shadow-cyan-500/50 transition-all flex items-center gap-2">
                <i class="fas fa-server"></i> Tambah Node
            </a>
        </div>

        <!-- Search Bar -->
        <div class="glass-effect rounded-2xl p-4 mb-6 border border-cyan-100">
            <form action="{{ route('servers.index') }}" method="GET" class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Cari Server</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-3 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama server atau IP Address..." 
                               class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 focus:border-cyan-400 transition-all text-sm">
                    </div>
                </div>
                <button type="submit" class="px-8 py-2 bg-slate-800 text-white rounded-xl font-bold hover:bg-slate-700 transition-all shadow-sm">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('servers.index') }}" class="px-4 py-2 bg-slate-100 text-slate-500 rounded-xl font-bold hover:bg-slate-200 transition-all flex items-center">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>

        <div class="glass-effect rounded-2xl p-6 border border-cyan-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="pb-4 pl-4">Nama Server</th>
                        <th class="pb-4">IP Address</th>
                        <th class="pb-4">Lokasi</th>
                        <th class="pb-4 text-center">Beban</th>
                        <th class="pb-4 text-center">Status</th>
                        <th class="pb-4 pr-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-600">
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
            <div class="mt-4">{{ $servers->links() }}</div>
        </div>
    </main>
</x-app-layout>