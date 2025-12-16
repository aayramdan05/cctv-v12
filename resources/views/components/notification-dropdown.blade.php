<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    
    <!-- Tombol Lonceng (Disesuaikan dengan Style Header Anda) -->
    <button @click="open = !open" class="relative w-9 h-9 rounded-full bg-white border border-slate-100 flex items-center justify-center hover:bg-slate-50 hover:border-cyan-200 transition-all group focus:outline-none">
        <i class="fas fa-bell text-slate-400 group-hover:text-cyan-500 transition-colors"></i>
        
        <!-- Badge Counter -->
        @if(auth()->user()->unreadNotifications->count() > 0)
            <span class="absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white animate-pulse"></span>
        @endif
    </button>

    <!-- Dropdown Panel -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         class="absolute right-0 z-50 mt-2 w-80 sm:w-96 origin-top-right rounded-xl bg-white shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none border border-slate-100"
         style="display: none;">
         
        <!-- Header Dropdown -->
        <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 rounded-t-xl">
            <h3 class="text-sm font-bold text-slate-700">Notifikasi</h3>
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[10px] font-bold text-cyan-600 hover:text-cyan-800 transition uppercase tracking-wide">
                        Tandai Dibaca
                    </button>
                </form>
            @endif
        </div>

        <!-- List Notifikasi -->
        <div class="max-h-[24rem] overflow-y-auto custom-scrollbar divide-y divide-slate-50">
            @forelse(auth()->user()->notifications->take(10) as $notification)
                <div class="px-4 py-3 hover:bg-slate-50 transition group {{ $notification->read_at ? 'opacity-60' : 'bg-cyan-50/20' }}">
                    <div class="flex gap-3">
                        
                        <!-- Icon -->
                        <div class="shrink-0 mt-1">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $notification->data['bg_color'] ?? 'bg-white border border-slate-100' }} {{ $notification->data['color'] ?? 'text-slate-500' }}">
                                <i class="{{ $notification->data['icon'] ?? 'fas fa-info' }} text-xs"></i>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-0.5">
                                <p class="text-xs font-bold text-slate-800 truncate">
                                    {{ $notification->data['title'] ?? 'System Notification' }}
                                </p>
                                <span class="text-[10px] text-slate-400 whitespace-nowrap ml-2">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                            
                            <p class="text-xs text-slate-600 leading-relaxed line-clamp-2">
                                {{ $notification->data['message'] ?? '' }}
                            </p>

                            <!-- Tombol Download (Jika ada) -->
                            @if(isset($notification->data['download_url']))
                                <a href="{{ $notification->data['download_url'] }}" target="_blank" 
                                   class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold rounded-lg shadow-sm transition w-full justify-center">
                                    <i class="fas fa-download"></i> Download ZIP
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center flex flex-col items-center justify-center text-slate-400">
                    <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mb-2">
                        <i class="far fa-bell-slash text-xl opacity-50"></i>
                    </div>
                    <p class="text-xs font-medium">Tidak ada notifikasi baru</p>
                </div>
            @endforelse
        </div>

        <!-- Footer Dropdown -->
        <a href="{{ route('notifications.index') }}" class="block w-full py-3 text-center text-xs font-bold text-slate-500 bg-slate-50 hover:bg-slate-100 hover:text-cyan-600 transition rounded-b-xl border-t border-slate-100">
            Lihat Semua Riwayat
        </a>
    </div>
</div>