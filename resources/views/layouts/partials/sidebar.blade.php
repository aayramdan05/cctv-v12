<div x-show="sidebarOpen" 
     @click="sidebarOpen = false"
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/50 z-30 backdrop-blur-sm md:hidden" 
     style="display: none;">
</div>

<aside id="sidebar" 
       class="fixed inset-y-0 left-0 w-64 glass-effect border-r border-cyan-100 z-40 transform transition-transform duration-300 ease-in-out -translate-x-full mt-16"
       :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
>
    <nav class="p-4 space-y-2 h-[calc(100vh-64px)] overflow-y-auto">
        
        @can('dashboard_view')
        <a href="{{ route('dashboard') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line w-5 {{ request()->routeIs('dashboard') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
            <span class="font-medium text-sm">Dashboard</span>
        </a>
        @endcan

        @can('live_monitoring')
        <a href="{{ route('monitoring.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('monitoring.index') ? 'active' : '' }}">
            <i class="fas fa-video w-5 {{ request()->routeIs('monitoring.index') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
            <span class="font-medium text-sm">Live Monitoring</span>
        </a>
        @endcan

        @can('map_view')
        <a href="{{ route('map.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('map.index') ? 'active' : '' }}">
            <i class="fas fa-map-location-dot w-5 {{ request()->routeIs('map.index') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
            <span class="font-medium text-sm">Map Monitoring</span>
        </a>
        @endcan

        @canany(['building_manage', 'user_view', 'cctv_view', 'server_manage', 'api_key_manage', 'report_view'])
            <div class="pt-4 mt-4 border-t border-cyan-100">
                <p class="px-4 text-xs font-bold text-slate-400 uppercase mb-2">Manajemen</p>
                
                @can('building_manage')
                    <a href="{{ route('faculties.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('faculties.*') ? 'active' : '' }}">
                        <i class="fas fa-university w-5 {{ request()->routeIs('faculties.*') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
                        <span class="font-medium text-sm">Master Fakultas</span>
                    </a>

                    <a href="{{ route('building.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('building.*') ? 'active' : '' }}">
                        <i class="fas fa-building w-5 {{ request()->routeIs('building.*') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
                        <span class="font-medium text-sm">Master Gedung</span>
                    </a>
                @endcan
                    
                @can('user_view')
                    <a href="{{ route('users.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fas fa-users w-5 {{ request()->routeIs('users.*') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
                        <span class="font-medium text-sm">Manage Users</span>
                    </a>
                @endcan

                @can('cctv_view')
                <a href="{{ route('cctv.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('cctv.*') ? 'active' : '' }}">
                    <i class="fas fa-camera w-5 {{ request()->routeIs('cctv.*') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
                    <span class="font-medium text-sm">Master Kamera</span>
                </a>
                @endcan

                @can('server_manage')
                    <a href="{{ route('servers.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('servers.*') ? 'active' : '' }}">
                        <i class="fas fa-server w-5 {{ request()->routeIs('servers.*') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
                        <span class="font-medium text-sm">Master Server</span>
                    </a>

                    <a href="{{ route('ffmpeg.monitor') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('ffmpeg.monitor') ? 'active' : '' }}">
                        <i class="fas fa-heartbeat w-5 {{ request()->routeIs('ffmpeg.monitor') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
                        <span class="font-medium text-sm">System Health</span>
                    </a>
                @endcan

                @can('api_key_manage')
                    <a href="{{ route('api.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('api.index') ? 'active' : '' }}">
                        <i class="fas fa-key w-5 {{ request()->routeIs('api.index') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
                        <span class="font-medium text-sm">API</span>
                    </a>
                @endcan

                @can('report_view')
                    <a href="{{ route('reports.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="fas fa-file-invoice w-5 {{ request()->routeIs('reports.*') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
                        <span class="font-medium text-sm">Report CCTV</span>
                    </a>
                @endcan

            </div>
        @endcanany

        @can('playback_view')
            <a href="{{ route('playback.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('playback.*') ? 'active' : '' }}">
                <i class="fas fa-history w-5 {{ request()->routeIs('playback.*') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
                <span class="font-medium text-sm">Recording</span>
            </a>
        @endcan

        @canany(['event_view', 'notification_manage'])
            <div class="pt-4 mt-4 border-t border-cyan-100">
                @can('event_view')
                <a href="{{ route('events.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('events.*') ? 'active' : '' }} relative">
                    <i class="fas fa-robot w-5 {{ request()->routeIs('events.*') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
                    <span class="font-medium text-sm">Intelligence Events</span>
                    
                    @php
                        $unreadEvents = \App\Models\CameraEvent::where('is_read', false)->count();
                    @endphp
                    @if($unreadEvents > 0)
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 bg-orange-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full animate-pulse">
                            {{ $unreadEvents }}
                        </span>
                    @endif
                </a>
                @endcan

                @can('notification_manage')
                <a href="{{ route('notifications.index') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <i class="fas fa-bell w-5 {{ request()->routeIs('notifications.*') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
                    <span>Notifikasi</span>
                    
                    <!-- Badge Counter (Merah) -->
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="absolute right-2 top-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </a>
                @endcan
            </div>
        @endcanany
        
        @if(auth()->user()->role === 'superadmin')
            <div class="pt-4 mt-4 border-t border-cyan-100">
                <p class="px-4 text-xs font-bold text-slate-400 uppercase mb-2">Super Admin</p>
                <a href="{{ route('superadmin.logs') }}" class="sidebar-item flex items-center space-x-3 px-4 py-3 rounded-xl text-slate-700 {{ request()->routeIs('superadmin.logs') ? 'active' : '' }}">
                    <i class="fas fa-history w-5 {{ request()->routeIs('superadmin.logs') ? 'text-cyan-500' : 'text-slate-400' }}"></i>
                    <span class="font-medium text-sm">Log Aktivitas</span>
                </a>
            </div>
        @endif
        
    </nav>
</aside>