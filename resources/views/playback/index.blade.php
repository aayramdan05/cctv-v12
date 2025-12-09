<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Galeri Rekaman (7 Hari Terakhir)</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse($recordings as $rec)
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition">
                    <video controls class="w-full rounded-lg mb-3 bg-black aspect-video">
                        <source src="{{ $rec['url'] }}" type="video/mp4">
                        Browser Anda tidak support video.
                    </video>
                    
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-700 truncate w-40" title="{{ $rec['name'] }}">
                                {{ $rec['name'] }}
                            </p>
                            <p class="text-[10px] text-slate-500">{{ $rec['time'] }}</p>
                        </div>
                        <span class="text-[10px] bg-slate-100 px-2 py-1 rounded text-slate-600">
                            {{ $rec['size'] }}
                        </span>
                    </div>
                    
                    <div class="mt-3 text-right">
                        <a href="{{ $rec['url'] }}" download class="text-xs text-cyan-600 hover:underline">
                            <i class="fas fa-download mr-1"></i> Download
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-10 text-slate-400">
                    Belum ada file rekaman.
                </div>
            @endforelse
        </div>
    </main>
</x-app-layout>