<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-slate-800 mb-6">System Health Monitoring</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                @foreach($serverStats as $stat)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">{{ $stat->name }}</h3>
                                <p class="text-sm text-slate-500">{{ $stat->ip }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-bold text-cyan-600">{{ $stat->active }}</span>
                                <span class="text-slate-400">/ {{ $stat->total }} Online</span>
                            </div>
                        </div>
                        
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-cyan-500 h-2 rounded-full" style="width: {{ $stat->total > 0 ? ($stat->active / $stat->total) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @foreach($serverStats as $stat)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-slate-200 mb-8">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800">Detail Kamera: {{ $stat->name }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                                <th class="py-3 px-6 font-semibold">Nama Kamera</th>
                                <th class="py-3 px-6 font-semibold">Status</th>
                                <th class="py-3 px-6 font-semibold">Terakhir Rekam</th>
                                <th class="py-3 px-6 font-semibold">Ukuran File</th>
                                <th class="py-3 px-6 font-semibold">File Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @foreach($stat->details as $cam)
                            <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                                <td class="py-4 px-6 font-medium text-slate-700">{{ $cam->name }}</td>
                                <td class="py-4 px-6">
                                    @if($cam->status === 'Recording')
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold uppercase">Recording</span>
                                    @else
                                        <span class="px-2 py-1 bg-slate-100 text-slate-500 rounded-full text-[10px] font-bold uppercase tracking-wider">Idle</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-slate-500">{{ $cam->last_update }}</td>
                                <td class="py-4 px-6 font-mono text-xs">{{ $cam->file_size }}</td>
                                <td class="py-4 px-6 text-slate-400 italic text-xs">{{ $cam->filename }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</x-app-layout>