<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-500">Master Data</span>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Gedung</span>
            </div>
        </div>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">Master Gedung</h2>
                <p class="text-slate-500">Kelola data gedung dan lokasi fakultas</p>
            </div>
            <a href="{{ route('building.create') }}" 
               class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all duration-300 flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Tambah Gedung
            </a>
        </div>

        <div class="glass-effect rounded-2xl p-6 border border-cyan-100">
            
            <div class="flex items-center justify-between mb-6">
                <div class="relative">
                    <input type="text" placeholder="Cari gedung..." class="w-64 h-10 px-4 pl-10 rounded-xl bg-slate-50 border-none focus:ring-2 focus:ring-cyan-200 text-sm text-slate-700">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                </div>
                <div class="flex space-x-2">
                    <button class="p-2 text-slate-500 hover:text-cyan-600 transition-colors"><i class="fas fa-filter"></i></button>
                    <button class="p-2 text-slate-500 hover:text-cyan-600 transition-colors"><i class="fas fa-download"></i></button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-slate-400 text-xs uppercase tracking-wider border-b border-cyan-100">
                            <th class="pb-4 pl-4 font-semibold">Kode</th>
                            <th class="pb-4 font-semibold">Nama Gedung</th>
                            <th class="pb-4 font-semibold">Fakultas</th>
                            <th class="pb-4 pr-4 text-right font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-600">
                        @forelse ($buildings as $building)
                            <tr class="hover:bg-cyan-50/50 transition-colors group border-b border-slate-50 last:border-none">
                                <td class="py-4 pl-4 font-medium text-cyan-600">
                                    {{ $building->kode_gedung }}
                                </td>
                                <td class="py-4 font-medium text-slate-800">
                                    {{ $building->nama_gedung }}
                                </td>
                                <td class="py-4">
                                    <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-medium">
                                        {{ $building->fakultas ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 pr-4 text-right space-x-2">
                                    <a href="{{ route('building.edit', $building->id) }}" 
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-cyan-300 hover:text-cyan-600 transition-all shadow-sm">
                                        <i class="fas fa-pencil-alt text-xs"></i>
                                    </a>
                                    <form action="{{ route('building.destroy', $building->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus gedung ini? Semua CCTV di dalamnya juga akan terhapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:border-red-300 hover:text-red-600 transition-all shadow-sm">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <i class="fas fa-building text-4xl mb-3 opacity-50"></i>
                                        <p>Belum ada data gedung.</p>
                                        <p class="text-xs">Silakan tambah data baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $buildings->links() }}
            </div>

        </div>
    </main>
</x-app-layout>