<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCTV Unpad - Welcome</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #fafafa;
            margin: 0;
            padding: 0;
            overflow: hidden; 
        }

        /* Container that moves up to simulate scrolling down */
        .scroll-container {
            height: 100vh;
            width: 100vw;
            animation: autoScroll 24s infinite cubic-bezier(0.65, 0, 0.35, 1);
        }

        /* 4 slides: Slide 1, Slide 2, Slide 3 (Features), Slide 4 (Footer) */
        @keyframes autoScroll {
            0%, 20% { transform: translateY(0); }
            25%, 45% { transform: translateY(-100vh); }
            50%, 70% { transform: translateY(-200vh); }
            75%, 95% { transform: translateY(-300vh); }
            98%, 100% { transform: translateY(0); }
        }

        .slide {
            height: 100vh;
            width: 100vw;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Connecting lines for Slide 1 */
        .line {
            position: absolute;
            background-color: #e5e7eb;
            z-index: 0;
        }

        /* Slide 1 Elements */
        .center-app {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #a855f7, #6366f1);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);
            z-index: 10;
        }

        .floating-node {
            position: absolute;
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            background: white;
            z-index: 10;
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Slide 2 Elements - Orbiting spinner */
        .orbit-container {
            position: absolute;
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .orbit-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px dashed #cbd5e1;
            animation: spin 8s linear infinite;
        }

        .orbit-dot {
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 10px;
            height: 10px;
            background-color: #3b82f6;
            border-radius: 50%;
            box-shadow: 0 0 10px #3b82f6;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        .slide-2-icon {
            width: 75px;
            height: 75px;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: #475569;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            z-index: 5;
        }
        
        /* Features Layout */
        .feature-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            border: 1px solid #f1f5f9;
            transition: transform 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>

    <!-- Fixed Header with Login Button -->
    <header class="fixed top-0 left-0 w-full z-50 px-6 py-4 flex justify-between items-center bg-white/90 backdrop-blur-md border-b border-gray-100 shadow-sm">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('unpad-cctv.png') }}" alt="Logo" class="w-8 h-8 rounded-lg shadow-sm">
            <span class="font-bold text-lg tracking-tight text-gray-800">CCTV UNPAD</span>
        </div>
        <div class="flex space-x-6 items-center">
            <a href="{{ route('login') }}" class="px-5 py-2 bg-black text-white rounded-full text-sm font-semibold hover:bg-gray-800 transition-colors shadow-lg shadow-black/20">
                Log In
            </a>
        </div>
    </header>

    <div class="scroll-container">
        
        <!-- SLIDE 1: Hero & Concept 1 -->
        <div class="slide">
            <div class="relative w-full max-w-4xl h-[280px] flex items-center justify-center mt-12 mb-6">
                
                <!-- Center Main Icon -->
                <div class="center-app">
                    <i class="fas fa-video text-white text-4xl"></i>
                </div>

                <!-- Lines (Horizontal) -->
                <div class="line" style="width: 220px; height: 2px; top: 50%; left: 20%;"></div>
                <div class="line" style="width: 220px; height: 2px; top: 50%; right: 20%;"></div>
                
                <!-- Left Nodes -->
                <div class="floating-node" style="top: 15%; left: 25%; background: #fef08a; color: #a16207; animation-delay: 0s;">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div class="floating-node" style="top: 40%; left: 10%; color: #3b82f6; animation-delay: 0.5s;">
                    <i class="fas fa-server"></i>
                </div>
                <div class="floating-node" style="top: 65%; left: 28%; background: #38bdf8; color: white; animation-delay: 1s;">
                    <i class="fas fa-shield-halved"></i>
                </div>

                <!-- Right Nodes -->
                <div class="floating-node" style="top: 15%; right: 28%; background: #ef4444; color: white; animation-delay: 0.2s;">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="floating-node" style="top: 40%; right: 10%; color: #0f172a; animation-delay: 0.7s;">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="floating-node" style="top: 65%; right: 25%; color: #10b981; animation-delay: 1.2s;">
                    <i class="fas fa-network-wired"></i>
                </div>
            </div>

            <div class="text-center px-4 max-w-3xl">
                <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight mb-5 text-gray-900">
                    Sistem Pemantauan <br> Kampus Pintar
                </h1>
                <p class="text-gray-500 text-base md:text-lg mb-8">
                    CCTV Unpad adalah platform pemantauan modern dan terpusat yang dirancang khusus untuk memastikan dan menjaga keamanan seluruh lingkungan akademik Anda.
                </p>
                @auth
                    <a href="{{ url('/dashboard') }}" class="px-8 py-3.5 bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-transform hover:-translate-y-1 inline-block">
                        Buka Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-8 py-3.5 bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-transform hover:-translate-y-1 inline-block">
                        Masuk ke Sistem
                    </a>
                @endauth
            </div>
        </div>

        <!-- SLIDE 2: Floating Core Concepts -->
        <div class="slide bg-gray-50/50">
            
            <!-- Center Icon -->
            <div class="w-20 h-20 bg-white rounded-3xl shadow-xl flex items-center justify-center mb-6 relative z-20">
                <i class="fas fa-shield-alt text-4xl text-purple-600"></i>
            </div>
            
            <div class="text-center px-4 relative z-20 max-w-2xl">
                <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight mb-5 text-gray-900">
                    Solusi Keamanan <br> Terpusat
                </h1>
                <p class="text-gray-500 text-base md:text-lg mb-8">
                    Sederhanakan proses pemantauan CCTV dalam satu platform terpusat untuk meningkatkan kewaspadaan dan transparansi keamanan di Universitas Padjadjaran.
                </p>
            </div>

            <!-- Floating Orbiting Icons (Left Side) -->
            <div class="absolute top-[25%] left-[12%]">
                <div class="orbit-container">
                    <div class="orbit-ring"><div class="orbit-dot"></div></div>
                    <div class="slide-2-icon"><i class="fas fa-video text-blue-500"></i></div>
                </div>
            </div>
            <div class="absolute top-[55%] left-[5%]">
                <div class="orbit-container" style="animation-delay: -2s;">
                    <div class="orbit-ring" style="animation-duration: 10s;"><div class="orbit-dot bg-red-500 box-shadow-red"></div></div>
                    <div class="slide-2-icon"><i class="fas fa-exclamation-triangle text-red-500"></i></div>
                </div>
            </div>
            <div class="absolute top-[75%] left-[18%]">
                <div class="orbit-container" style="animation-delay: -4s;">
                    <div class="orbit-ring" style="animation-duration: 12s; animation-direction: reverse;"><div class="orbit-dot bg-green-500"></div></div>
                    <div class="slide-2-icon"><i class="fas fa-server text-green-600"></i></div>
                </div>
            </div>

            <!-- Floating Orbiting Icons (Right Side) -->
            <div class="absolute top-[30%] right-[12%]">
                <div class="orbit-container" style="animation-delay: -1s;">
                    <div class="orbit-ring" style="animation-duration: 9s; animation-direction: reverse;"><div class="orbit-dot bg-purple-500"></div></div>
                    <div class="slide-2-icon"><i class="fas fa-desktop text-purple-500"></i></div>
                </div>
            </div>
            <div class="absolute top-[60%] right-[5%]">
                <div class="orbit-container" style="animation-delay: -3s;">
                    <div class="orbit-ring" style="animation-duration: 11s;"><div class="orbit-dot bg-yellow-500"></div></div>
                    <div class="slide-2-icon"><i class="fas fa-users text-yellow-500"></i></div>
                </div>
            </div>
            <div class="absolute top-[80%] right-[22%]">
                <div class="orbit-container" style="animation-delay: -5s;">
                    <div class="orbit-ring" style="animation-duration: 7s;"><div class="orbit-dot bg-teal-500"></div></div>
                    <div class="slide-2-icon"><i class="fas fa-hdd text-teal-600"></i></div>
                </div>
            </div>
            
        </div>

        <!-- SLIDE 3: Features Grid (New Slide) -->
        <div class="slide w-full px-4 sm:px-6 lg:px-8 bg-white pt-10">
            <div class="max-w-6xl w-full mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight">Diciptakan untuk semua <br>kebutuhan keamanan</h2>
                    <p class="mt-4 text-slate-500 text-lg">Platform multifungsi yang dapat diandalkan oleh seluruh pimpinan dan operator kampus.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="feature-card flex flex-col items-center text-center">
                        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-6 shadow-sm border border-blue-100">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">Streaming Ultra Cepat</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            Nikmati video langsung dengan jeda kurang dari 1 detik berkat implementasi teknologi WebRTC melalui Go2RTC.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="feature-card flex flex-col items-center text-center">
                        <div class="w-16 h-16 bg-cyan-50 text-cyan-600 rounded-2xl flex items-center justify-center text-2xl mb-6 shadow-sm border border-cyan-100">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">Pengecekan Otomatis</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            Sistem secara rutin memonitor koneksi ratusan kamera dan mengirimkan laporan jika ditemukan kamera yang offline.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="feature-card flex flex-col items-center text-center">
                        <div class="w-16 h-16 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-2xl mb-6 shadow-sm border border-purple-100">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">Hak Akses Fleksibel</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            Manajemen peran yang ketat memastikan hanya personel berwenang yang dapat melihat rekaman area tertentu.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SLIDE 4: Footer -->
        <div class="slide bg-slate-900 text-white justify-end pb-10">
            <div class="max-w-4xl mx-auto text-center w-full px-6">
                <div class="mb-8">
                    <img src="{{ asset('unpad-cctv.png') }}" alt="Logo" class="w-14 h-14 mx-auto rounded-xl opacity-90 mb-5 shadow-lg">
                    <h2 class="text-3xl font-bold mb-3 tracking-tight">Siap memantau area kampus?</h2>
                    <p class="text-slate-400 mb-8 text-sm">Akses dashboard terpusat sekarang juga.</p>
                    <a href="{{ route('login') }}" class="px-8 py-3 bg-white text-black font-bold rounded-full shadow-lg hover:scale-105 transition-transform inline-block">
                        Mulai Sekarang
                    </a>
                </div>
                <div class="border-t border-slate-800 pt-6 mt-10 text-slate-500 text-xs flex justify-between items-center flex-col sm:flex-row gap-4">
                    <span>&copy; {{ date('Y') }} Universitas Padjadjaran.</span>
                    <span class="flex space-x-4">
                        <a href="#" class="hover:text-white transition-colors">Bantuan</a>
                        <a href="#" class="hover:text-white transition-colors">Privasi</a>
                    </span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
