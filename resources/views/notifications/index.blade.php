<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8 h-screen overflow-y-auto">
        
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-slate-800">
                    <i class="fas fa-bell text-cyan-600 mr-2"></i>Pusat Notifikasi & Export
                </h2>
                
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <form action="{{ route('notifications.readAll') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-sm text-slate-500 hover:text-cyan-600 font-semibold underline">
                            Tandai semua dibaca
                        </button>
                    </form>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                @forelse($notifications as $notification)
                    <div class="p-4 border-b border-slate-100 flex items-start gap-4 transition hover:bg-slate-50 {{ $notification->read_at ? 'opacity-70' : 'bg-cyan-50/50' }}">
                        
                        <!-- Icon -->
                        <div class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center {{ $notification->data['color'] ?? 'text-cyan-500 bg-cyan-100' }}">
                            <i class="{{ $notification->data['icon'] ?? 'fas fa-info-circle' }}"></i>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-slate-800 text-sm {{ $notification->read_at ? '' : 'text-cyan-700' }}">
                                    {{ $notification->data['title'] ?? 'Notifikasi' }}
                                    @if(!$notification->read_at)
                                        <span class="ml-2 px-2 py-0.5 bg-red-100 text-red-600 text-[10px] rounded-full">BARU</span>
                                    @endif
                                </h3>
                                <span class="text-xs text-slate-400 whitespace-nowrap ml-2">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                            
                            <p class="text-sm text-slate-600 mt-1">{{ $notification->data['message'] ?? '' }}</p>

                            <!-- Filename & Download Button -->
                            @if(isset($notification->data['download_url']))
                                <div class="mt-3 flex items-center gap-3">
                                    <a href="{{ $notification->data['download_url'] }}" target="_blank" 
                                       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                                        <i class="fas fa-download"></i> Download File
                                    </a>
                                    <span class="text-xs text-slate-400 font-mono bg-slate-100 px-2 py-1 rounded">
                                        {{ $notification->data['filename'] }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Mark as Read Button (If Unread) -->
                        @if(!$notification->read_at)
                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-slate-400 hover:text-cyan-600" title="Tandai sudah dibaca">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="p-10 text-center text-slate-400 flex flex-col items-center">
                        <i class="far fa-bell-slash text-4xl mb-3 opacity-30"></i>
                        <p>Belum ada notifikasi.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </main>
</x-app-layout>