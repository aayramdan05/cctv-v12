<header id="header" class="fixed top-0 left-0 right-0 h-16 glass-effect z-50 border-b border-cyan-100">
    <div class="h-full px-6 flex items-center justify-between">
        
        <!-- Logo & Sidebar Toggle -->
        <div class="flex items-center space-x-4">
            <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-cyan-600 focus:outline-none transition-transform active:scale-95">
                <i class="fas fa-bars text-xl"></i>
            </button>

            <img src="{{ asset('unpad-cctv.png') }}" alt="Logo" class="w-10 h-10 rounded-xl shadow-lg">
            <div class="hidden md:block">
                <h1 class="text-lg font-bold text-slate-800 tracking-tight">CCTV UNPAD</h1>
                <p class="text-xs text-slate-500">Monitoring System</p>
            </div>
        </div>
        
        <!-- Right Menu -->
        <div class="flex items-center space-x-4">
            
            <!-- NOTIFIKASI DINAMIS (Menggantikan tombol statis lama) -->
            @if(auth()->user()->role === 'admin')
                <x-notification-dropdown />
            @endif
            
            <!-- Profile Dropdown -->
            <div class="relative ml-3" x-data="{ open: false }">
                <button @click="open = ! open" class="flex items-center space-x-3 focus:outline-none group">
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-bold text-slate-700 group-hover:text-cyan-600 transition-colors">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wider">{{ Auth::user()->role }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-cyan-100 to-blue-100 border-2 border-white shadow-sm flex items-center justify-center text-cyan-600 font-bold text-lg group-hover:border-cyan-300 transition-all">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <i class="fas fa-chevron-down text-xs text-slate-300 group-hover:text-cyan-500 transition-colors duration-200" :class="{'rotate-180': open}"></i>
                </button>

                <div x-show="open" @click.away="open = false" x-transition
                     class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 py-1 focus:outline-none border border-slate-100"
                     style="display: none;">
                    <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50 rounded-t-xl">
                        <p class="text-xs text-slate-500">Signed in as</p>
                        <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-cyan-50 hover:text-cyan-700 transition-colors">
                        <i class="fas fa-user-circle w-5 mr-2 text-slate-400"></i> Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors rounded-b-xl">
                            <i class="fas fa-sign-out-alt w-5 mr-2 text-red-400"></i> Log Out
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>