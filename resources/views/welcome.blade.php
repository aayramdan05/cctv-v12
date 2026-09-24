<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'CCTV Unpad') }} - Smart Monitoring</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #FAFAFA; overflow-x: hidden; }
        .glass-nav { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); }
        
        /* Wavy abstract background similar to the image */
        .bg-waves {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: -1;
            background: 
                radial-gradient(ellipse at 0% 10%, rgba(241, 245, 249, 1) 0%, transparent 50%),
                radial-gradient(ellipse at 100% 90%, rgba(241, 245, 249, 1) 0%, transparent 50%);
            background-color: #ffffff;
        }

        .border-left-accent {
            border-left: 8px solid #1A202C;
            padding-left: 1.5rem;
        }

        /* Animations */
        @keyframes fadeInRise {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        
        .animate-buttons {
            opacity: 0;
            animation: fadeInRise 0.8s ease-out forwards;
            animation-delay: 1.5s; /* Delayed animation */
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .floating { animation: float 5s ease-in-out infinite; }
        .floating-delayed { animation: float 6s ease-in-out infinite; animation-delay: -2.5s; }
    </style>
</head>
<body class="text-slate-800">

    <div class="bg-waves"></div>

    <!-- Navigation -->
    <nav class="absolute top-0 w-full z-50 transition-all duration-300 pt-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('unpad-cctv.png') }}" alt="Logo" class="w-10 h-10 rounded-xl shadow-sm">
                    <div class="hidden sm:block">
                        <span class="block text-lg font-bold tracking-tight text-slate-800">CCTV UNPAD</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Hero Section -->
    <section class="min-h-screen relative flex items-center pt-20 overflow-hidden">
        
        <!-- Small Top Right Camera -->
        <div class="absolute top-24 right-10 md:right-24 w-28 md:w-40 opacity-90 floating-delayed hidden md:block">
            <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <!-- Wall mount / Stand -->
                <path d="M 160 80 L 160 140 L 180 140 L 180 80 Z" fill="#CFD8DC" />
                <path d="M 120 100 L 160 110 L 160 130 L 120 120 Z" fill="#B0BEC5" />
                <!-- Camera Body -->
                <path d="M 40 60 L 130 80 L 120 140 L 30 120 Z" fill="#90A4AE" />
                <path d="M 40 60 L 130 80 L 130 90 L 40 70 Z" fill="#78909C" />
                <ellipse cx="40" cy="90" rx="15" ry="32" transform="rotate(-15 40 90)" fill="#1A202C" />
                <!-- Lens -->
                <ellipse cx="36" cy="90" rx="6" ry="12" transform="rotate(-15 36 90)" fill="#4299e1" opacity="0.8" />
                <!-- Small details -->
                <circle cx="45" cy="70" r="2" fill="#fff" opacity="0.5" />
                <circle cx="35" cy="110" r="2" fill="#fff" opacity="0.5" />
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="flex flex-col md:flex-row items-center justify-between gap-12">
                
                <!-- Left Side: Large Camera Illustration -->
                <div class="w-full md:w-1/2 relative flex justify-center items-center h-[500px]">
                    
                    <!-- Small Bottom Left Camera -->
                    <div class="absolute bottom-0 left-0 md:-left-10 w-32 md:w-44 opacity-90 floating z-10 hidden sm:block">
                        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                            <!-- Stand -->
                            <path d="M 40 160 L 40 100 L 20 100 L 20 160 Z" fill="#CFD8DC" />
                            <path d="M 40 130 L 80 120 L 80 140 L 40 150 Z" fill="#B0BEC5" />
                            <!-- Camera Body -->
                            <path d="M 70 140 L 160 120 L 170 60 L 80 80 Z" fill="#90A4AE" />
                            <path d="M 70 140 L 160 120 L 160 110 L 70 130 Z" fill="#78909C" />
                            <ellipse cx="160" cy="90" rx="15" ry="32" transform="rotate(15 160 90)" fill="#1A202C" />
                            <!-- Lens -->
                            <ellipse cx="164" cy="90" rx="6" ry="12" transform="rotate(15 164 90)" fill="#4299e1" opacity="0.8" />
                            <!-- Small details -->
                            <circle cx="155" cy="70" r="2" fill="#fff" opacity="0.5" />
                            <circle cx="165" cy="110" r="2" fill="#fff" opacity="0.5" />
                        </svg>
                    </div>

                    <!-- Main Dome Camera -->
                    <div class="w-64 md:w-80 relative z-20">
                        <svg viewBox="0 0 200 200" class="w-full drop-shadow-xl" xmlns="http://www.w3.org/2000/svg">
                            <!-- Top Wall Mount -->
                            <rect x="25" y="45" width="150" height="10" rx="2" fill="#90A4AE" />
                            <!-- Dome Base -->
                            <path d="M 30 55 L 170 55 L 170 85 Q 170 125 150 125 L 50 125 Q 30 125 30 85 Z" fill="#CFD8DC" />
                            <!-- Shading on Base -->
                            <path d="M 30 55 L 170 55 L 170 65 L 30 65 Z" fill="#B0BEC5" />
                            
                            <!-- Dark Dome -->
                            <circle cx="100" cy="115" r="55" fill="#1A202C" />
                            <!-- Inner Lens Ring -->
                            <circle cx="100" cy="115" r="38" fill="#2D3748" />
                            
                            <!-- LEDs Grid Pattern (like in the image) -->
                            <!-- Inner circle LEDs -->
                            <circle cx="100" cy="88" r="4.5" fill="#90CAF9" />
                            <circle cx="119" cy="95" r="4.5" fill="#90CAF9" />
                            <circle cx="127" cy="115" r="4.5" fill="#90CAF9" />
                            <circle cx="119" cy="135" r="4.5" fill="#90CAF9" />
                            <circle cx="100" cy="142" r="4.5" fill="#90CAF9" />
                            <circle cx="81" cy="135" r="4.5" fill="#90CAF9" />
                            <circle cx="73" cy="115" r="4.5" fill="#90CAF9" />
                            <circle cx="81" cy="95" r="4.5" fill="#90CAF9" />
                            
                            <!-- Outer circle LEDs -->
                            <circle cx="100" cy="73" r="5" fill="#64B5F6" />
                            <circle cx="120" cy="78" r="5" fill="#64B5F6" />
                            <circle cx="136" cy="92" r="5" fill="#64B5F6" />
                            <circle cx="143" cy="115" r="5" fill="#64B5F6" />
                            <circle cx="136" cy="138" r="5" fill="#64B5F6" />
                            <circle cx="120" cy="152" r="5" fill="#64B5F6" />
                            <circle cx="100" cy="157" r="5" fill="#64B5F6" />
                            <circle cx="80" cy="152" r="5" fill="#64B5F6" />
                            <circle cx="64" cy="138" r="5" fill="#64B5F6" />
                            <circle cx="57" cy="115" r="5" fill="#64B5F6" />
                            <circle cx="64" cy="92" r="5" fill="#64B5F6" />
                            <circle cx="80" cy="78" r="5" fill="#64B5F6" />

                            <!-- Center Lens -->
                            <circle cx="100" cy="115" r="16" fill="#0f172a" />
                            <!-- Reflection -->
                            <circle cx="104" cy="111" r="5" fill="#cbd5e1" opacity="0.6" />
                            <circle cx="98" cy="119" r="2" fill="#fff" opacity="0.3" />
                        </svg>
                    </div>
                </div>

                <!-- Right Side: Content -->
                <div class="w-full md:w-1/2 pt-10 md:pt-0">
                    <div class="border-left-accent mb-6">
                        <h2 class="text-xl md:text-2xl font-bold tracking-[0.15em] text-slate-800 uppercase mb-2">
                            Secure • Realtime • Integrated
                        </h2>
                        <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-slate-900 leading-[1.1]">
                            SMART CAMPUS <br>
                            <span class="text-slate-800">MONITORING</span> <br>
                            <span class="text-slate-800">SYSTEM</span>
                        </h1>
                    </div>
                    
                    <p class="text-slate-500 text-lg mb-10 pl-6 border-l-8 border-transparent max-w-lg leading-relaxed">
                        Platform pemantauan keamanan terpusat untuk lingkungan Universitas Padjadjaran. Dilengkapi dengan teknologi streaming latensi rendah dan analisis status real-time.
                    </p>

                    <!-- Buttons with Delayed Animation -->
                    <div class="pl-6 border-l-8 border-transparent animate-buttons flex flex-wrap gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-8 py-4 rounded-full bg-slate-900 text-white font-medium text-sm md:text-base hover:bg-slate-800 transition-all flex items-center shadow-md">
                                Buka Dashboard <i class="fas fa-chevron-right ml-3 text-xs opacity-70"></i>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-8 py-4 rounded-full bg-slate-900 text-white font-medium text-sm md:text-base hover:bg-slate-800 transition-all flex items-center shadow-md transform hover:scale-105">
                                Masuk ke Sistem <i class="fas fa-chevron-right ml-3 text-xs opacity-70"></i>
                            </a>
                        @endauth
                        
                        <a href="#features" class="px-8 py-4 rounded-full bg-white text-slate-700 font-medium text-sm md:text-base border border-slate-200 hover:bg-slate-50 transition-all flex items-center shadow-sm">
                            Pelajari Fitur
                        </a>
                    </div>
                </div>
                
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-900">Fitur Unggulan</h2>
                <p class="mt-4 text-lg text-slate-500">Teknologi mutakhir untuk keamanan kampus yang maksimal.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-3">Low Latency Streaming</h3>
                    <p class="text-slate-500 leading-relaxed">
                        Menggunakan teknologi WebRTC via Go2RTC untuk menghadirkan video real-time dengan delay di bawah 1 detik.
                    </p>
                </div>

                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-cyan-100 text-cyan-600 flex items-center justify-center text-xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fas fa-server"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-3">Auto Health Check</h3>
                    <p class="text-slate-500 leading-relaxed">
                        Sistem otomatis mengecek status koneksi 300+ kamera setiap 5 menit dan memberikan notifikasi jika ada yang offline.
                    </p>
                </div>

                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl mb-6 group-hover:scale-110 transition-transform">
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

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center space-x-3 mb-4 md:mb-0">
                <img src="{{ asset('unpad-cctv.png') }}" alt="Logo" class="w-8 h-8 rounded-lg grayscale opacity-70">
                <span class="font-semibold text-slate-500 tracking-tight">CCTV UNPAD</span>
            </div>
            
            <div class="text-sm text-slate-400">
                &copy; {{ date('Y') }} Universitas Padjadjaran. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>