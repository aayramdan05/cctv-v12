<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Riwayat Kejadian (Events)</span>
            </div>
        </div>

        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">Intelligence Events</h1>
                <p class="text-slate-500 mt-1">Daftar deteksi pergerakan dari semua kamera ONVIF.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div class="glass-effect rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200">
                                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Waktu</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Kamera</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Lokasi</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Tipe</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($events as $event)
                            <tr class="hover:bg-cyan-50/30 transition-colors {{ !$event->is_read ? 'bg-orange-50/40' : '' }}">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-slate-700">
                                        {{ $event->event_time->format('H:i:s') }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $event->event_time->format('d M Y') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-cyan-100 flex items-center justify-center text-cyan-600">
                                            <i class="fas fa-video text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-700">{{ $event->cctv->nama_cctv }}</div>
                                            <div class="text-xs text-slate-400">{{ $event->cctv->kode_cctv }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-medium text-slate-600">
                                        <i class="fas fa-building mr-1 opacity-50"></i>
                                        {{ $event->cctv->building->nama_gedung }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700 uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-1.5 animate-pulse"></span>
                                        {{ $event->event_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('cctv.index', ['search' => $event->cctv->nama_cctv]) }}" class="text-cyan-600 hover:text-cyan-700 font-bold text-xs flex items-center gap-1">
                                        <i class="fas fa-eye"></i> Lihat Kamera
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fas fa-bell-slash text-4xl mb-3 opacity-20"></i>
                                    <p>Belum ada kejadian yang terdeteksi.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($events->hasPages())
                <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-200">
                    {{ $events->links() }}
                </div>
                @endif
            </div>
        </div>
    </main>
</x-app-layout>
