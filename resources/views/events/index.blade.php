<x-app-layout>
    <div class="font-body-md text-slate-800 pb-32 pt-6 px-6 max-w-[1600px] mx-auto" x-data="{ activeTab: '{{ $activeTab }}' }">
        <!-- Header -->
        <header class="mb-6">
            <div class="flex items-center text-sm text-slate-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-cyan-600"><i class="fas fa-home"></i></a>
                <span class="mx-2">/</span>
                <span class="text-slate-500">Manajemen</span>
                <span class="mx-2">/</span>
                <span class="text-slate-700 font-medium">ONVIF Dashboard</span>
            </div>
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">ONVIF Dashboard</h1>
                    <p class="text-slate-500 mt-1">Pantau status konfigurasi ONVIF dan riwayat kejadian (Event Logs) kamera.</p>
                </div>
                @if($onvifEvents->count() > 0 || $intelEvents->count() > 0)
                    <form action="{{ route('events.markAllRead') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-cyan-200 rounded-lg text-sm font-semibold shadow-sm transition-all flex items-center gap-2">
                            <i class="fas fa-check-double text-cyan-500"></i> Mark All Read
                        </button>
                    </form>
                @endif
            </div>
        </header>

        <main class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- Total -->
                <div class="bg-white/50 glass-effect p-5 rounded-xl border border-cyan-100 flex flex-col shadow-sm">
                    <span class="text-xs font-semibold text-slate-500 mb-1 tracking-wider">TOTAL KAMERA</span>
                    <div class="flex items-end justify-between">
                        <span class="text-3xl font-bold">{{ $totalCameras }}</span>
                        <i class="fas fa-video text-slate-300 text-3xl"></i>
                    </div>
                </div>
                <!-- Configured -->
                <div class="bg-white/50 glass-effect p-5 rounded-xl border-l-4 border-l-emerald-500 border-cyan-100 flex flex-col shadow-sm">
                    <span class="text-xs font-semibold text-emerald-600 mb-1 tracking-wider">ONVIF CONFIGURED</span>
                    <div class="flex items-end justify-between">
                        <span class="text-3xl font-bold text-emerald-600">{{ $hasOnvifCount }}</span>
                        <i class="fas fa-check-circle text-emerald-200 text-3xl"></i>
                    </div>
                </div>
                <!-- Not Configured -->
                <div class="bg-white/50 glass-effect p-5 rounded-xl border-l-4 border-l-amber-500 border-cyan-100 flex flex-col shadow-sm">
                    <span class="text-xs font-semibold text-amber-600 mb-1 tracking-wider">NOT CONFIGURED</span>
                    <div class="flex items-end justify-between">
                        <span class="text-3xl font-bold text-amber-600">{{ $noOnvifCount }}</span>
                        <i class="fas fa-exclamation-circle text-amber-200 text-3xl"></i>
                    </div>
                </div>
            </div>

            <!-- Cameras ONVIF Status Table -->
            <section class="bg-white/50 glass-effect border border-cyan-100 rounded-xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-cyan-100 bg-white/30 flex justify-between items-center">
                    <h2 class="text-lg font-bold">Status Konfigurasi ONVIF Kamera</h2>
                </div>
                <div class="overflow-x-auto max-h-[400px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-slate-50/90 backdrop-blur-sm shadow-sm z-10">
                            <tr class="text-xs text-slate-500 uppercase border-b border-cyan-100">
                                <th class="px-6 py-3 font-semibold">Status ONVIF</th>
                                <th class="px-4 py-3 font-semibold">Nama Kamera</th>
                                <th class="px-4 py-3 font-semibold">IP Address</th>
                                <th class="px-4 py-3 font-semibold">ONVIF Port</th>
                                <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cyan-100/50">
                            @foreach($cameras as $cam)
                                @php
                                    $hasOnvif = !empty($cam->onvif_user) || !empty($cam->onvif_password);
                                @endphp
                                <tr class="hover:bg-cyan-50/50 transition-colors {{ !$hasOnvif ? 'bg-amber-50/30' : '' }}">
                                    <td class="px-6 py-3">
                                        @if($hasOnvif)
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 bg-emerald-500 rounded-full shadow-[0_0_5px_rgba(16,185,129,0.5)]"></span>
                                                <span class="text-xs font-semibold text-emerald-700">CONFIGURED</span>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse shadow-[0_0_5px_rgba(245,158,11,0.5)]"></span>
                                                <span class="text-xs font-semibold text-amber-700">MISSING</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-video {{ $hasOnvif ? 'text-cyan-600' : 'text-amber-500' }}"></i>
                                            <span class="font-medium text-slate-800">{{ $cam->nama_cctv }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <code class="font-mono text-sm text-slate-600">{{ $cam->ip }}</code>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="bg-slate-100 px-2 py-0.5 rounded text-[11px] font-bold text-slate-600">{{ $cam->onvif_port ?? 80 }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <a href="{{ route('cctv.edit', $cam->id) }}" class="p-2 text-slate-400 hover:text-cyan-600 hover:bg-cyan-50 rounded-lg transition-colors" title="Edit Kamera">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Logs Section -->
            <section class="bg-white/50 glass-effect border border-cyan-100 rounded-xl overflow-hidden shadow-sm mt-8">
                <div class="px-6 py-4 border-b border-cyan-100 bg-white/30 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h2 class="text-lg font-bold">Riwayat Kejadian (Logs)</h2>
                    <div class="flex bg-white border border-cyan-100 rounded-lg p-1 shadow-sm">
                        <button @click="activeTab = 'onvif'" :class="activeTab === 'onvif' ? 'bg-cyan-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50'" class="px-4 py-1.5 rounded text-xs font-bold transition-all">LOG ONVIF EVENT</button>
                        <button @click="activeTab = 'intelligence'" :class="activeTab === 'intelligence' ? 'bg-cyan-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50'" class="px-4 py-1.5 rounded text-xs font-bold transition-all">LOG INTELLIGENCE EVENT</button>
                    </div>
                </div>
                
                <!-- ONVIF Table -->
                <div x-show="activeTab === 'onvif'" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 shadow-sm">
                            <tr class="text-xs text-slate-500 uppercase border-b border-cyan-100">
                                <th class="px-6 py-3 font-semibold w-16">Status</th>
                                <th class="px-4 py-3 font-semibold">Waktu</th>
                                <th class="px-4 py-3 font-semibold">Kamera</th>
                                <th class="px-4 py-3 font-semibold">Lokasi</th>
                                <th class="px-4 py-3 font-semibold">Tipe Event</th>
                                <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cyan-100/50">
                            @forelse($onvifEvents as $event)
                                <tr class="hover:bg-cyan-50/50 transition-colors {{ !$event->is_read ? 'bg-cyan-50/30' : '' }}">
                                    <td class="px-6 py-3 text-center">
                                        @if(!$event->is_read)
                                            <span class="w-2.5 h-2.5 bg-orange-500 rounded-full inline-block animate-pulse shadow-[0_0_5px_rgba(249,115,22,0.6)]" title="Unread"></span>
                                        @else
                                            <span class="w-2.5 h-2.5 bg-slate-300 rounded-full inline-block" title="Read"></span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-800">{{ $event->created_at->format('d M Y') }}</span>
                                            <span class="text-xs text-slate-500">{{ $event->created_at->format('H:i:s') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('monitoring.index', ['cctv' => $event->cctv_id]) }}" class="font-medium text-cyan-600 hover:underline flex items-center gap-2">
                                            <i class="fas fa-video text-xs opacity-70"></i>
                                            {{ $event->cctv->nama_cctv ?? 'Unknown' }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-slate-600">
                                            {{ $event->cctv->building->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="bg-slate-100 px-2.5 py-1 rounded-md text-[11px] font-bold text-slate-600 uppercase border border-slate-200 shadow-sm">
                                            {{ $event->event_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        @if(!$event->is_read)
                                            <form action="{{ route('events.read', $event->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-2 bg-white border border-cyan-200 text-cyan-600 hover:bg-cyan-50 hover:border-cyan-300 rounded-lg shadow-sm transition-all" title="Tandai Dibaca">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                        <div class="flex flex-col items-center gap-3">
                                            <i class="fas fa-folder-open text-4xl text-slate-300"></i>
                                            <p class="text-sm">Tidak ada log event ONVIF saat ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($onvifEvents->hasPages())
                    <div x-show="activeTab === 'onvif'" class="px-6 py-4 bg-white/30 border-t border-cyan-100">
                        {{ $onvifEvents->links() }}
                    </div>
                @endif

                <!-- Intelligence Table -->
                <div x-show="activeTab === 'intelligence'" x-cloak class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50 shadow-sm">
                            <tr class="text-xs text-slate-500 uppercase border-b border-cyan-100">
                                <th class="px-6 py-3 font-semibold w-16">Status</th>
                                <th class="px-4 py-3 font-semibold">Waktu</th>
                                <th class="px-4 py-3 font-semibold">Kamera</th>
                                <th class="px-4 py-3 font-semibold">Lokasi</th>
                                <th class="px-4 py-3 font-semibold">Tipe Event</th>
                                <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cyan-100/50">
                            @forelse($intelEvents as $event)
                                <tr class="hover:bg-cyan-50/50 transition-colors {{ !$event->is_read ? 'bg-cyan-50/30' : '' }}">
                                    <td class="px-6 py-3 text-center">
                                        @if(!$event->is_read)
                                            <span class="w-2.5 h-2.5 bg-orange-500 rounded-full inline-block animate-pulse shadow-[0_0_5px_rgba(249,115,22,0.6)]" title="Unread"></span>
                                        @else
                                            <span class="w-2.5 h-2.5 bg-slate-300 rounded-full inline-block" title="Read"></span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-800">{{ $event->created_at->format('d M Y') }}</span>
                                            <span class="text-xs text-slate-500">{{ $event->created_at->format('H:i:s') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('monitoring.index', ['cctv' => $event->cctv_id]) }}" class="font-medium text-cyan-600 hover:underline flex items-center gap-2">
                                            <i class="fas fa-video text-xs opacity-70"></i>
                                            {{ $event->cctv->nama_cctv ?? 'Unknown' }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-slate-600">
                                            {{ $event->cctv->building->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="bg-slate-100 px-2.5 py-1 rounded-md text-[11px] font-bold text-slate-600 uppercase border border-slate-200 shadow-sm">
                                            {{ $event->event_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        @if(!$event->is_read)
                                            <form action="{{ route('events.read', $event->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="p-2 bg-white border border-cyan-200 text-cyan-600 hover:bg-cyan-50 hover:border-cyan-300 rounded-lg shadow-sm transition-all" title="Tandai Dibaca">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                        <div class="flex flex-col items-center gap-3">
                                            <i class="fas fa-folder-open text-4xl text-slate-300"></i>
                                            <p class="text-sm">Tidak ada log event Intelligence saat ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($intelEvents->hasPages())
                    <div x-show="activeTab === 'intelligence'" x-cloak class="px-6 py-4 bg-white/30 border-t border-cyan-100">
                        {{ $intelEvents->links() }}
                    </div>
                @endif
            </section>
        </main>
    </div>
</x-app-layout>
