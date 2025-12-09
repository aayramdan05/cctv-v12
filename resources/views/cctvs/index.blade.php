<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-500">Master Data</span>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Kamera CCTV</span>
            </div>
        </div>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Master Kamera</h2>
                <p class="text-slate-500">Daftar seluruh perangkat CCTV yang terdaftar</p>
            </div>
            <a href="{{ route('cctv.create') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all duration-300 flex items-center">
                <i class="fas fa-plus mr-2"></i> Tambah Kamera
            </a>
        </div>

        <div class="glass-effect rounded-2xl p-6 border border-cyan-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-wider border-b border-cyan-100">
                            <th class="pb-4 pl-4 font-semibold">Kode</th>
                            <th class="pb-4 font-semibold">Nama / Lokasi</th>
                            <th class="pb-4 font-semibold">IP Address</th>
                            <th class="pb-4 font-semibold">RTSP URL</th>
                            <th class="pb-4 pr-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-600">
                        @forelse ($cctvs as $cctv)
                            <tr class="hover:bg-cyan-50/50 transition-colors border-b border-slate-50 last:border-none">
                                <td class="py-4 pl-4 font-medium text-cyan-600">{{ $cctv->kode_cctv }}</td>
                                <td class="py-4">
                                    <div class="font-medium text-slate-800">{{ $cctv->nama_cctv }}</div>
                                    <div class="text-xs text-slate-400 mt-1">
                                        <i class="fas fa-building mr-1"></i> {{ $cctv->building->nama_gedung }}
                                    </div>
                                </td>
                                <td class="py-4 font-mono text-xs">{{ $cctv->ip ?? '-' }}</td>
                                <td class="py-4 font-mono text-xs text-slate-400 truncate max-w-[150px]" title="{{ $cctv->rtsp_url }}">
                                    {{ $cctv->rtsp_url }}
                                </td>
                                <td class="py-4 pr-4 text-right space-x-2">
                                    <a href="{{ route('cctv.edit', $cctv->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-cyan-300 hover:text-cyan-600 transition-all shadow-sm">
                                        <i class="fas fa-pencil-alt text-xs"></i>
                                    </a>
                                    <form action="{{ route('cctv.destroy', $cctv->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus kamera ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-red-300 hover:text-red-600 transition-all shadow-sm">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-12 text-center text-slate-400">Belum ada data kamera.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $cctvs->links() }}</div>
        </div>
    </main>
</x-app-layout>