<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">FFmpeg Health Monitor</h2>
                <p class="text-slate-500 text-sm">Memantau aktivitas perekaman di server ini secara real-time.</p>
            </div>
            
            <a href="{{ route('ffmpeg.monitor') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition shadow-sm text-sm font-bold flex items-center gap-2">
                <i class="fas fa-sync-alt"></i> Refresh Status
            </a>
        </div>

        <div class="glass-effect rounded-2xl border border-cyan-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100 text-xs uppercase text-slate-500 font-bold">
                    <tr>
                        <th class="p-4">Kamera</th>
                        <th class="p-4">Lokasi</th>
                        <th class="p-4">Status Perekaman</th>
                        <th class="p-4">File Aktif</th>
                        <th class="p-4">Update Terakhir</th>
                        <th class="p-4 text-right">Ukuran</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach($statusData as $cam)
                    <tr class="border-b border-slate-50 hover:bg-white/40 transition">
                        <td class="p-4 font-bold text-slate-700">
                            {{ $cam->name }}
                        </td>
                        <td class="p-4 text-slate-500">
                            {{ $cam->building }}
                        </td>
                        <td class="p-4">
                            @if($cam->is_recording)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-[10px] font-bold uppercase tracking-wide border border-green-200">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                    Running
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-[10px] font-bold uppercase tracking-wide border border-red-200">
                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                    Stopped
                                </span>
                            @endif
                        </td>
                        <td class="p-4 font-mono text-xs text-slate-500 truncate max-w-[200px]" title="{{ $cam->filename }}">
                            {{ $cam->filename }}
                        </td>
                        <td class="p-4 text-slate-600">
                            {{ $cam->last_update }}
                        </td>
                        <td class="p-4 text-right font-mono font-bold text-slate-700">
                            {{ $cam->file_size }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 flex gap-6 text-xs text-slate-400">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                <span>Running: Data tertulis < 60 detik lalu</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                <span>Stopped: Tidak ada update data > 1 menit</span>
            </div>
        </div>

    </main>
</x-app-layout>