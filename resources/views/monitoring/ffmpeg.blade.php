<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">System Health Monitor</h2>
                <p class="text-slate-500 text-sm">Memantau status perekaman dari seluruh Node secara terpusat.</p>
            </div>
            
            <div class="flex items-center gap-3 w-full md:w-auto">
                <form action="{{ route('ffmpeg.monitor') }}" method="GET" class="flex items-center gap-2 flex-1 md:flex-none">
                    <select name="server_id" onchange="this.form.submit()" class="bg-white border border-slate-200 text-slate-700 text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 block w-full p-2.5 shadow-sm">
                        <option value="">Semua Server Node</option>
                        @foreach($servers as $srv)
                            <option value="{{ $srv->id }}" {{ $selectedServerId == $srv->id ? 'selected' : '' }}>
                                Node {{ $srv->id }} ({{ $srv->ip_address }})
                            </option>
                        @endforeach
                    </select>
                </form>

                <a href="{{ route('ffmpeg.monitor') }}" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-lg hover:bg-slate-50 transition shadow-sm text-sm font-bold flex items-center gap-2 shrink-0">
                    <i class="fas fa-sync-alt"></i>
                </a>
            </div>
        </div>

        <div class="glass-effect rounded-2xl border border-cyan-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100 text-xs uppercase text-slate-500 font-bold">
                    <tr>
                        <th class="p-4">Kamera</th>
                        <th class="p-4">Node</th>
                        <th class="p-4">Lokasi</th>
                        <th class="p-4 text-center">Perekaman</th>
                        <th class="p-4">File Terakhir</th>
                        <th class="p-4">Update</th>
                        <th class="p-4 text-right">Ukuran</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach($statusData as $cam)
                    <tr class="border-b border-slate-50 hover:bg-white/40 transition">
                        <td class="p-4 font-bold text-slate-700">
                            {{ $cam->name }}
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded border border-blue-100 text-[10px] font-mono">
                                {{ $cam->server_ip }}
                            </span>
                        </td>
                        <td class="p-4 text-slate-500">
                            {{ $cam->building }}
                        </td>
                        <td class="p-4 text-center">
                            @if($cam->is_recording)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-[10px] font-bold uppercase tracking-wide border border-green-200 shadow-sm">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-400 text-[10px] font-bold uppercase tracking-wide border border-slate-200">
                                    <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="p-4 font-mono text-[10px] text-slate-400 truncate max-w-[150px]" title="{{ $cam->filename }}">
                            {{ $cam->filename }}
                        </td>
                        <td class="p-4 text-slate-600 text-xs">
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