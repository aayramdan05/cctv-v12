<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'CCTV Unpad') }} - Smart Monitoring</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-nav { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(226, 232, 240, 0.6); }
        .text-gradient { background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .blob { position: absolute; filter: blur(80px); opacity: 0.4; z-index: -1; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 relative overflow-x-hidden">

    <div class="blob bg-cyan-300 w-96 h-96 rounded-full top-0 left-0 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="blob bg-blue-300 w-96 h-96 rounded-full bottom-0 right-0 translate-x-1/2 translate-y-1/2"></div>

    <nav class="fixed top-0 w-full z-50 glass-nav transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-500 flex items-center justify-center text-white shadow-lg shadow-cyan-500/30">
                        <i class="fas fa-university text-lg"></i>
                    </div>
                    <div>
                        <span class="block text-lg font-bold tracking-tight text-slate-800">CCTV UNPAD</span>
                        <span class="block text-[10px] font-medium text-slate-500 uppercase tracking-wider">Network Infrastructure</span>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-6 py-2.5 rounded-full bg-slate-900 text-white text-sm font-medium hover:bg-slate-800 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <i class="fas fa-chart-line mr-2"></i> Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-6 py-2.5 rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-sm font-bold hover:shadow-lg hover:shadow-cyan-500/30 transition-all transform hover:-translate-y-0.5">
                                Log in <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            
            <span class="inline-block py-1 px-3 rounded-full bg-cyan-50 border border-cyan-100 text-cyan-600 text-xs font-bold tracking-wide uppercase mb-6">
                Secure • Realtime • Integrated
            </span>
            
            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-slate-900 mb-6">
                Smart Campus <br>
                <span class="text-gradient">Monitoring System</span>
            </h1>
            
            <p class="mt-4 max-w-2xl mx-auto text-xl text-slate-500 leading-relaxed">
                Platform pemantauan keamanan terpusat untuk lingkungan Universitas Padjadjaran. 
                Dilengkapi dengan teknologi streaming latensi rendah dan analisis status real-time.
            </p>
            
            <div class="mt-10 flex justify-center gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="px-8 py-4 rounded-2xl bg-slate-900 text-white font-bold text-lg shadow-xl hover:bg-slate-800 transition-all flex items-center">
                        Buka Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-8 py-4 rounded-2xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold text-lg shadow-xl shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all flex items-center">
                        Masuk ke Sistem
                    </a>
                @endauth
                
                <a href="#features" class="px-8 py-4 rounded-2xl bg-white text-slate-700 font-bold text-lg border border-slate-200 hover:border-cyan-300 hover:text-cyan-600 transition-all flex items-center shadow-sm">
                    Pelajari Fitur
                </a>
            </div>

            <div class="mt-16 relative mx-auto max-w-5xl">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-50 via-transparent to-transparent z-10"></div>
                <div class="rounded-2xl border-8 border-slate-900 bg-slate-900 shadow-2xl overflow-hidden">
                    <div class="aspect-video bg-slate-800 relative flex items-center justify-center overflow-hidden">
                        <div class="absolute inset-0 opacity-50">
                            <div class="h-full w-full" style="background-image: radial-gradient(#3b82f6 1px, transparent 1px); background-size: 30px 30px;"></div>
                        </div>
                        <div class="z-10 text-center">
                            <i class="fas fa-video text-6xl text-slate-700 mb-4"></i>
                            <p class="text-slate-500 font-mono text-sm">Secure Dashboard Preview</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-20 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-900">Fitur Unggulan</h2>
                <p class="mt-4 text-lg text-slate-500">Teknologi mutakhir untuk keamanan kampus yang maksimal.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-cyan-200 transition-all hover:shadow-lg group">
                    <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-3">Low Latency Streaming</h3>
                    <p class="text-slate-500 leading-relaxed">
                        Menggunakan teknologi WebRTC via Go2RTC untuk menghadirkan video real-time dengan delay di bawah 1 detik.
                    </p>
                </div>

                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-cyan-200 transition-all hover:shadow-lg group">
                    <div class="w-14 h-14 rounded-2xl bg-cyan-100 text-cyan-600 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-server"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-3">Auto Health Check</h3>
                    <p class="text-slate-500 leading-relaxed">
                        Sistem otomatis mengecek status koneksi 300+ kamera setiap 5 menit dan memberikan notifikasi jika ada yang offline.
                    </p>
                </div>

                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-cyan-200 transition-all hover:shadow-lg group">
                    <div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-3">Secure Access</h3>
                    <p class="text-slate-500 leading-relaxed">
                        Manajemen hak akses berbasis Role (RBAC) dan enkripsi kredensial RTSP untuk keamanan data maksimal.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-slate-900 text-slate-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center space-x-3 mb-4 md:mb-0">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-400 to-blue-500 flex items-center justify-center text-white">
                    <i class="fas fa-university text-xs"></i>
                </div>
                <span class="font-bold text-white">CCTV UNPAD</span>
            </div>
            
            <div class="text-sm text-slate-500">
                &copy; {{ date('Y') }} Universitas Padjadjaran. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>