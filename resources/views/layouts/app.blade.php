<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CCTV Unpad') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <script src="https://cdn.plot.ly/plotly-3.1.1.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/csp@3.x.x/dist/cdn.min.js"></script>
    <style>
        ::-webkit-scrollbar { display: none;}
        body { font-family: 'Inter', sans-serif; }
        .glass-effect { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .neon-border { border: 1px solid rgba(6, 182, 212, 0.4); box-shadow: 0 0 10px rgba(6, 182, 212, 0.2); }
        .hover-glow:hover { box-shadow: 0 0 20px rgba(6, 182, 212, 0.4); transform: translateY(-2px); transition: all 0.3s ease; }
        .video-frame { border: 2px solid rgba(6, 182, 212, 0.5); border-radius: 12px; position: relative; overflow: hidden; }
        .sidebar-item { transition: all 0.3s ease; }
        .sidebar-item:hover { background: rgba(6, 182, 212, 0.1); border-left: 3px solid #06b6d4; box-shadow: 0 0 15px rgba(6, 182, 212, 0.2); }
        .sidebar-item.active { background: rgba(6, 182, 212, 0.15); border-left: 3px solid #06b6d4; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-white to-blue-50 min-h-screen" x-data="{ sidebarOpen: false }">

    @include('layouts.partials.header')

    @include('layouts.partials.sidebar')

    <div class="pt-16 w-full transition-all duration-300 ease-in-out"
         :class="{'md:pl-64': sidebarOpen}">
        {{ $slot }}
    </div>

    <!-- Global Flash Notification -->
    @if(session('success') || session('error') || session('warning'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-4"
             class="fixed bottom-6 right-6 z-[9999] max-w-sm w-full"
        >
            <div class="flex items-center p-4 rounded-2xl shadow-2xl border backdrop-blur-md 
                @if(session('success')) bg-emerald-500/90 border-emerald-400 text-white @endif
                @if(session('error')) bg-red-500/90 border-red-400 text-white @endif
                @if(session('warning')) bg-amber-500/90 border-amber-400 text-white @endif
            ">
                <div class="mr-3 text-xl">
                    @if(session('success')) <i class="fas fa-check-circle"></i> @endif
                    @if(session('error')) <i class="fas fa-exclamation-triangle"></i> @endif
                    @if(session('warning')) <i class="fas fa-info-circle"></i> @endif
                </div>
                <div class="flex-1 mr-4">
                    <p class="text-sm font-bold">
                        @if(session('success')) Success @endif
                        @if(session('error')) Error @endif
                        @if(session('warning')) Warning @endif
                    </p>
                    <p class="text-xs opacity-90">{{ session('success') ?? session('error') ?? session('warning') }}</p>
                </div>
                <button @click="show = false" class="text-white hover:opacity-75 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

    @stack('scripts')
</body>
</html>