<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCTV Unpad - Welcome</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    
    <!-- Swiper CSS for 3D Carousel -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

    <style>
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            overflow-x: hidden; 
        }

        /* Swiper Carousel Container */
        .swiper {
            width: 100%;
            padding-top: 100px; /* Offset for fixed header */
            padding-bottom: 50px;
            height: 100vh;
            box-sizing: border-box;
        }

        .swiper-slide {
            background-position: center;
            background-size: cover;
            width: 90%;
            max-width: 1100px;
            
            /* Dibuat 100% transparan kembali */
            background-color: transparent;

            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        @media (min-width: 768px) {
            .swiper-slide {
                width: 65%; /* Agar slide kiri & kanan bisa mengintip masuk ke layar */
            }
        }

        /* Slide 1 Elements */
        .center-app {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #a855f7, #6366f1);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);
            z-index: 10;
        }
        .line { position: absolute; background-color: #e5e7eb; z-index: 0; }
        
        .floating-node {
            position: absolute;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            z-index: 10;
            animation: float 4s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }

        /* Slide 2 Elements (Orbital Icons) */
        :root {
            --orbit-radius: 140px;
        }
        @media (min-width: 768px) {
            :root { --orbit-radius: 260px; } /* Diperkecil agar lebih dekat dengan text */
        }
        
        .orbit-system-intro {
            position: absolute;
            top: 50%; left: 50%;
            width: 1px; height: 1px;
            z-index: 10;
            pointer-events: none;
            opacity: 0;
            transform: scale(0);
        }

        /* Hanya jalankan animasi pop-out ketika slide sedang aktif / di tengah layar */
        .swiper-slide-active .orbit-system-intro {
            animation: pop-out 1.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        .orbit-icon {
            position: absolute;
            top: 50%; left: 50%;
            width: 60px; height: 60px;
            margin-top: -30px; margin-left: -30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .orbit-1 { animation: orbit1 20s linear infinite; }
        .orbit-2 { animation: orbit2 20s linear infinite; }
        .orbit-3 { animation: orbit3 20s linear infinite; }
        .orbit-4 { animation: orbit4 20s linear infinite; }

        @keyframes pop-out {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        @keyframes orbit1 {
            from { transform: rotate(0deg) translateX(var(--orbit-radius)) rotate(0deg); }
            to   { transform: rotate(360deg) translateX(var(--orbit-radius)) rotate(-360deg); }
        }
        @keyframes orbit2 {
            from { transform: rotate(90deg) translateX(var(--orbit-radius)) rotate(-90deg); }
            to   { transform: rotate(450deg) translateX(var(--orbit-radius)) rotate(-450deg); }
        }
        @keyframes orbit3 {
            from { transform: rotate(180deg) translateX(var(--orbit-radius)) rotate(-180deg); }
            to   { transform: rotate(540deg) translateX(var(--orbit-radius)) rotate(-540deg); }
        }
        @keyframes orbit4 {
            from { transform: rotate(270deg) translateX(var(--orbit-radius)) rotate(-270deg); }
            to   { transform: rotate(630deg) translateX(var(--orbit-radius)) rotate(-630deg); }
        }

        /* Slide 3 Feature Cards */
        /* Slide 3 Feature Cards */
        .feature-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border-radius: 30px;
            padding: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        /* Laser Sphere Background */
        .laser-sphere-container {
            position: absolute;
            top: 50%; left: 50%;
            width: 700px; height: 700px;
            transform: translate(-50%, -50%);
            perspective: 1200px;
            pointer-events: none;
            z-index: 0;
            opacity: 0.6;
        }
        @media (min-width: 768px) {
            .laser-sphere-container {
                width: 1100px; height: 1100px;
            }
        }
        .laser-ring {
            position: absolute;
            width: 100%; height: 100%;
            border-radius: 50%;
            border: 1px solid rgba(30, 58, 138, 0.05);
            border-top: 3px solid rgba(234, 88, 12, 0.7); /* Orange Tua */
            border-bottom: 3px solid rgba(30, 58, 138, 0.7); /* Biru Tua */
            box-shadow: 0 0 30px rgba(234, 88, 12, 0.2), inset 0 0 30px rgba(30, 58, 138, 0.2);
            filter: drop-shadow(0 0 10px rgba(234, 88, 12, 0.3));
        }
        .laser-ring-1 {
            animation: ring-spin-1 30s linear infinite;
        }

        @keyframes ring-spin-1 {
            0% { transform: rotateX(75deg) rotateY(0deg) rotateZ(0deg); }
            100% { transform: rotateX(75deg) rotateY(0deg) rotateZ(360deg); }
        }

        /* Responsive Background */
        .hero-bg {
            background-image: url('{{ asset('bg-mobile.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        @media (min-width: 768px) {
            .hero-bg {
                background-image: url('{{ asset('bg.png') }}');
            }
        }

        /* Center White Glow */
        .center-highlight {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 1500px;
            height: 1500px;
            background: radial-gradient(circle, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0.4) 30%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            filter: blur(40px);
        }
        @media (max-width: 768px) {
            .center-highlight {
                width: 800px;
                height: 800px;
            }
        }
    </style>
</head>
<body>

    <!-- Fixed Header with Center Logo -->
    <header class="fixed top-0 left-0 w-full z-50 px-6 py-4 flex justify-between items-center bg-transparent">
        <!-- Logo Kiri agar seimbang -->
        <div class="flex-1 flex justify-start items-center">
            <img src="{{ asset('logo-unpad.png') }}" alt="Logo Kiri" class="h-10">
        </div>
        
        <!-- Logo di Tengah -->
        <div class="flex flex-col items-center flex-1">
            <span class="text-[10px] font-extrabold tracking-[0.2em] text-blue-900 mb-1 uppercase leading-none" style="text-shadow: 0 2px 4px rgba(255,255,255,0.8);">CCTV</span>
            <div class="bg-white p-2 rounded-xl shadow-md border border-gray-100">
                <img src="{{ asset('logo-unpad-secondary.png') }}" alt="CCTV UNPAD" class="h-6">
            </div>
        </div>
        
        <!-- Tombol Login Kanan -->
        <div class="flex-1 flex justify-end">
            <a href="{{ route('login') }}" class="px-6 py-2 bg-orange-600 text-white rounded-full text-sm font-bold hover:bg-orange-500 transition-colors shadow-lg shadow-orange-900/30">
                Log In
            </a>
        </div>
    </header>

    <main>
        <!-- 3D Carousel Section -->
        <section class="h-screen w-full relative overflow-hidden hero-bg">
            
            <!-- Global Laser Sphere Background -->
            <div class="laser-sphere-container">
                <div class="laser-ring laser-ring-1"></div>
            </div>

            <!-- Center Highlight Overlay -->
            <div class="center-highlight"></div>

            <div class="swiper mySwiper relative z-10">
                <div class="swiper-wrapper">
                    
                    <!-- SLIDE 1: Sistem Pemantauan -->
                    <div class="swiper-slide p-6">
                        <div class="relative w-full max-w-3xl h-[250px] flex items-center justify-center mb-8 mt-4">
                            <!-- Center Main Icon -->
                            <div class="center-app"><i class="fas fa-video text-white text-4xl"></i></div>
                            <!-- Left Nodes -->
                            <div class="floating-node hidden md:flex bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-orange-500/40 rounded-full border border-white/50" style="top: 15%; left: 15%; animation-delay: 0s;"><i class="fas fa-lightbulb"></i></div>
                            <div class="floating-node hidden sm:flex bg-gradient-to-br from-emerald-400 to-teal-500 text-white shadow-lg shadow-teal-500/40 rounded-full border border-white/50" style="top: 40%; left: 5%; animation-delay: 0.5s;"><i class="fas fa-server"></i></div>
                            <div class="floating-node hidden md:flex bg-gradient-to-br from-purple-400 to-fuchsia-500 text-white shadow-lg shadow-fuchsia-500/40 rounded-full border border-white/50" style="top: 65%; left: 20%; animation-delay: 1s;"><i class="fas fa-shield-halved"></i></div>
                            <!-- Right Nodes -->
                            <div class="floating-node hidden md:flex bg-gradient-to-br from-cyan-400 to-blue-500 text-white shadow-lg shadow-blue-500/40 rounded-full border border-white/50" style="top: 15%; right: 20%; animation-delay: 0.2s;"><i class="fas fa-bolt"></i></div>
                            <div class="floating-node hidden sm:flex bg-gradient-to-br from-rose-400 to-red-500 text-white shadow-lg shadow-red-500/40 rounded-full border border-white/50" style="top: 40%; right: 5%; animation-delay: 0.7s;"><i class="fas fa-eye"></i></div>
                            <div class="floating-node hidden md:flex bg-gradient-to-br from-teal-400 to-sky-500 text-white shadow-lg shadow-sky-500/40 rounded-full border border-white/50" style="top: 65%; right: 15%; animation-delay: 1.2s;"><i class="fas fa-network-wired"></i></div>
                        </div>

                        <div class="text-center px-4 max-w-2xl mx-auto">
                            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 text-transparent bg-clip-text bg-gradient-to-r from-blue-900 via-blue-800 to-blue-600" style="filter: drop-shadow(0 2px 8px rgba(255,255,255,0.9));">
                                Sistem Pemantauan <br> Kampus Pintar
                            </h1>
                            <p class="text-slate-800 text-sm md:text-base mb-8 font-semibold" style="text-shadow: 0 1px 4px rgba(255,255,255,1);">
                                CCTV Unpad adalah platform pemantauan modern dan terpusat yang dirancang khusus untuk memastikan keamanan seluruh lingkungan akademik Anda.
                            </p>
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-8 py-3.5 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold rounded-full shadow-lg shadow-orange-500/30 hover:shadow-xl transition-transform hover:-translate-y-1 inline-block">Buka Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="px-8 py-3.5 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold rounded-full shadow-lg shadow-orange-500/30 hover:shadow-xl transition-transform hover:-translate-y-1 inline-block">Masuk ke Sistem</a>
                            @endauth
                        </div>
                    </div>

                    <!-- SLIDE 2: Solusi Keamanan -->
                    <div class="swiper-slide p-6">
                        <div class="w-20 h-20 flex items-center justify-center mb-6 relative z-20 mx-auto mt-4 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-[28px] shadow-xl shadow-indigo-500/40 border border-white/50">
                            <i class="fas fa-shield-alt text-4xl text-white"></i>
                        </div>
                        
                        <div class="text-center px-4 relative z-20 max-w-2xl mx-auto mb-10">
                            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 text-transparent bg-clip-text bg-gradient-to-r from-blue-900 via-blue-800 to-blue-600" style="filter: drop-shadow(0 2px 8px rgba(255,255,255,0.9));">Solusi Keamanan <br> Terpusat</h1>
                            <p class="text-slate-800 text-sm md:text-base font-semibold" style="text-shadow: 0 1px 4px rgba(255,255,255,1);">
                                Sederhanakan proses pemantauan CCTV dalam satu platform terpusat untuk meningkatkan kewaspadaan dan transparansi keamanan kampus.
                            </p>
                        </div>

                        <!-- Floating Orbiting Icons -->
                        <div class="orbit-system-intro hidden sm:block">
                            <div class="orbit-icon orbit-1 bg-gradient-to-br from-purple-400 to-fuchsia-500 text-white shadow-lg shadow-fuchsia-500/40 rounded-full border border-white/50"><i class="fas fa-video"></i></div>
                            <div class="orbit-icon orbit-2 bg-gradient-to-br from-rose-400 to-red-500 text-white shadow-lg shadow-red-500/40 rounded-full border border-white/50"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="orbit-icon orbit-3 bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-orange-500/40 rounded-full border border-white/50"><i class="fas fa-desktop"></i></div>
                            <div class="orbit-icon orbit-4 bg-gradient-to-br from-emerald-400 to-teal-500 text-white shadow-lg shadow-teal-500/40 rounded-full border border-white/50"><i class="fas fa-users"></i></div>
                        </div>
                    </div>

                    <!-- SLIDE 3: Fitur -->
                    <div class="swiper-slide p-8 md:p-12">
                        <div class="text-center mb-8">
                            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-3 text-transparent bg-clip-text bg-gradient-to-r from-blue-900 via-blue-800 to-blue-600" style="filter: drop-shadow(0 2px 8px rgba(255,255,255,0.9));">Diciptakan untuk semua <br>kebutuhan keamanan</h2>
                            <p class="text-slate-800 text-sm md:text-base max-w-xl mx-auto font-semibold" style="text-shadow: 0 1px 4px rgba(255,255,255,1);">Platform multifungsi yang dapat diandalkan oleh seluruh pimpinan dan operator kampus.</p>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
                            <!-- Feature 1 -->
                            <div class="feature-card flex flex-col items-center text-center">
                                <div class="w-14 h-14 bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-orange-500/40 rounded-[20px] border border-white/50 flex items-center justify-center text-xl mb-4"><i class="fas fa-bolt"></i></div>
                                <h3 class="text-lg font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-600 mb-2">Streaming Cepat</h3>
                                <p class="text-slate-600 text-xs leading-relaxed font-semibold">Nikmati video langsung dengan jeda minimal berkat teknologi WebRTC.</p>
                            </div>
                            <!-- Feature 2 -->
                            <div class="feature-card flex flex-col items-center text-center">
                                <div class="w-14 h-14 bg-gradient-to-br from-cyan-400 to-blue-500 text-white shadow-lg shadow-blue-500/40 rounded-[20px] border border-white/50 flex items-center justify-center text-xl mb-4"><i class="fas fa-heartbeat"></i></div>
                                <h3 class="text-lg font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-600 mb-2">Cek Otomatis</h3>
                                <p class="text-slate-600 text-xs leading-relaxed font-semibold">Sistem rutin memonitor koneksi kamera dan mengirim notifikasi jika offline.</p>
                            </div>
                            <!-- Feature 3 -->
                            <div class="feature-card flex flex-col items-center text-center">
                                <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-fuchsia-500 text-white shadow-lg shadow-fuchsia-500/40 rounded-[20px] border border-white/50 flex items-center justify-center text-xl mb-4"><i class="fas fa-lock"></i></div>
                                <h3 class="text-lg font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-600 mb-2">Akses Fleksibel</h3>
                                <p class="text-slate-600 text-xs leading-relaxed font-semibold">Manajemen peran memastikan hanya personel berwenang yang dapat mengakses.</p>
                            </div>
                        </div>
                    </div>

                    <!-- DUPLICATE SLIDE 1 (Untuk memastikan loop berjalan mulus tanpa bug) -->
                    <div class="swiper-slide p-6">
                        <div class="relative w-full max-w-3xl h-[250px] flex items-center justify-center mb-8 mt-4">
                            <div class="center-app"><i class="fas fa-video text-white text-4xl"></i></div>
                            <div class="floating-node hidden md:flex" style="top: 15%; left: 15%; color: #ea580c; animation-delay: 0s;"><i class="fas fa-lightbulb"></i></div>
                            <div class="floating-node hidden sm:flex" style="top: 40%; left: 5%; color: #1e3a8a; animation-delay: 0.5s;"><i class="fas fa-server"></i></div>
                            <div class="floating-node hidden md:flex" style="top: 65%; left: 20%; color: #ea580c; animation-delay: 1s;"><i class="fas fa-shield-halved"></i></div>
                            <div class="floating-node hidden md:flex" style="top: 15%; right: 20%; color: #1e3a8a; animation-delay: 0.2s;"><i class="fas fa-bolt"></i></div>
                            <div class="floating-node hidden sm:flex" style="top: 40%; right: 5%; color: #ea580c; animation-delay: 0.7s;"><i class="fas fa-eye"></i></div>
                            <div class="floating-node hidden md:flex" style="top: 65%; right: 15%; color: #1e3a8a; animation-delay: 1.2s;"><i class="fas fa-network-wired"></i></div>
                        </div>
                        <div class="text-center px-4 max-w-2xl mx-auto">
                            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 text-transparent bg-clip-text bg-gradient-to-r from-blue-900 via-blue-800 to-blue-600" style="filter: drop-shadow(0 2px 8px rgba(255,255,255,0.9));">Sistem Pemantauan <br> Kampus Pintar</h1>
                            <p class="text-slate-800 text-sm md:text-base mb-8 font-semibold" style="text-shadow: 0 1px 4px rgba(255,255,255,1);">CCTV Unpad adalah platform pemantauan modern dan terpusat yang dirancang khusus untuk memastikan keamanan seluruh lingkungan akademik Anda.</p>
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-8 py-3.5 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold rounded-full shadow-lg shadow-orange-500/30 hover:shadow-xl transition-transform hover:-translate-y-1 inline-block">Buka Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="px-8 py-3.5 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold rounded-full shadow-lg shadow-orange-500/30 hover:shadow-xl transition-transform hover:-translate-y-1 inline-block">Masuk ke Sistem</a>
                            @endauth
                        </div>
                    </div>

                    <!-- DUPLICATE SLIDE 2 -->
                    <div class="swiper-slide p-6">
                        <div class="w-20 h-20 flex items-center justify-center mb-6 relative z-20 mx-auto mt-4 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-[28px] shadow-xl shadow-indigo-500/40 border border-white/50">
                            <i class="fas fa-shield-alt text-4xl text-white"></i>
                        </div>
                        <div class="text-center px-4 relative z-20 max-w-2xl mx-auto mb-10">
                            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 text-transparent bg-clip-text bg-gradient-to-r from-blue-900 via-blue-800 to-blue-600" style="filter: drop-shadow(0 2px 8px rgba(255,255,255,0.9));">Solusi Keamanan <br> Terpusat</h1>
                            <p class="text-slate-800 text-sm md:text-base font-semibold" style="text-shadow: 0 1px 4px rgba(255,255,255,1);">Sederhanakan proses pemantauan CCTV dalam satu platform terpusat untuk meningkatkan kewaspadaan dan transparansi keamanan kampus.</p>
                        </div>
                        <div class="orbit-system-intro hidden sm:block">
                            <div class="orbit-icon orbit-1 text-orange-600"><i class="fas fa-video"></i></div>
                            <div class="orbit-icon orbit-2 text-blue-800"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="orbit-icon orbit-3 text-orange-600"><i class="fas fa-desktop"></i></div>
                            <div class="orbit-icon orbit-4 text-blue-800"><i class="fas fa-users"></i></div>
                        </div>
                    </div>

                    <!-- DUPLICATE SLIDE 3 -->
                    <div class="swiper-slide p-8 md:p-12">
                        <div class="text-center mb-8">
                            <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-3 text-transparent bg-clip-text bg-gradient-to-r from-blue-900 via-blue-800 to-blue-600" style="filter: drop-shadow(0 2px 8px rgba(255,255,255,0.9));">Diciptakan untuk semua <br>kebutuhan keamanan</h2>
                            <p class="text-slate-800 text-sm md:text-base max-w-xl mx-auto font-semibold" style="text-shadow: 0 1px 4px rgba(255,255,255,1);">Platform multifungsi yang dapat diandalkan oleh seluruh pimpinan dan operator kampus.</p>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
                            <div class="feature-card flex flex-col items-center text-center">
                                <div class="w-14 h-14 bg-orange-100 text-orange-600 rounded-[20px] flex items-center justify-center text-xl mb-4"><i class="fas fa-bolt"></i></div>
                                <h3 class="text-lg font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-600 mb-2">Streaming Cepat</h3>
                                <p class="text-slate-600 text-xs leading-relaxed font-semibold">Nikmati video langsung dengan jeda minimal berkat teknologi WebRTC.</p>
                            </div>
                            <div class="feature-card flex flex-col items-center text-center">
                                <div class="w-14 h-14 bg-blue-100 text-blue-800 rounded-[20px] flex items-center justify-center text-xl mb-4"><i class="fas fa-heartbeat"></i></div>
                                <h3 class="text-lg font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-600 mb-2">Cek Otomatis</h3>
                                <p class="text-slate-600 text-xs leading-relaxed font-semibold">Sistem rutin memonitor koneksi kamera dan mengirim notifikasi jika offline.</p>
                            </div>
                            <div class="feature-card flex flex-col items-center text-center">
                                <div class="w-14 h-14 bg-orange-100 text-orange-600 rounded-[20px] flex items-center justify-center text-xl mb-4"><i class="fas fa-lock"></i></div>
                                <h3 class="text-lg font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-900 to-blue-600 mb-2">Akses Fleksibel</h3>
                                <p class="text-slate-600 text-xs leading-relaxed font-semibold">Manajemen peran memastikan hanya personel berwenang yang dapat mengakses.</p>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- Add Pagination -->
                <div class="swiper-pagination"></div>
            </div>
        </section>

        <!-- FOOTER SECTION (Normal Manual Scroll) -->
        <section class="bg-slate-50 text-slate-900 py-20 flex flex-col items-center">
            <div class="max-w-4xl w-full px-6 text-center">
                <div class="flex flex-col items-center mb-6">
                    <span class="text-xs font-extrabold tracking-[0.2em] text-slate-500 mb-1 uppercase">CCTV</span>
                    <div class="bg-white p-4 rounded-2xl inline-block shadow-md border border-gray-100">
                        <img src="{{ asset('logo-unpad-secondary.png') }}" alt="Logo" class="h-10">
                    </div>
                </div>
                <h2 class="text-3xl font-bold mb-4 tracking-tight text-blue-900">Siap memantau area kampus?</h2>
                <p class="text-slate-600 mb-10 text-sm max-w-lg mx-auto leading-relaxed">Geser kembali carousel ke atas untuk melihat fitur, atau langsung akses sistem jika Anda sudah siap.</p>
                <a href="{{ route('login') }}" class="px-8 py-3 bg-orange-600 text-white font-bold rounded-full shadow-lg shadow-orange-600/30 hover:scale-105 transition-transform inline-block">
                    Mulai Sekarang
                </a>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-left border-t border-slate-200 pt-10 mt-16 w-full">
                    <!-- Info Unpad -->
                    <div>
                        <div class="flex items-center space-x-3 mb-4">
                            <img src="{{ asset('logo.png') }}" alt="Logo Universitas Padjadjaran" class="h-12 bg-white rounded p-1 border border-gray-100" onerror="this.src='{{ asset('logo-unpad.png') }}'">
                            <div>
                                <h3 class="font-extrabold text-lg leading-tight tracking-wide text-blue-900">UNIVERSITAS<br>PADJADJARAN</h3>
                            </div>
                        </div>
                        <p class="font-bold text-sm text-slate-700 mb-2">
                            Direktorat Perencanaan, Sistem<br>Informasi, dan Transformasi Digital
                        </p>
                        <p class="text-sm text-slate-600 leading-relaxed max-w-sm">
                            Gedung Rektorat Unpad Kampus Jatinangor<br>
                            Jln. Ir. Soekarno km. 21 Jatinangor, Kab. Sumedang 45363<br>
                            Jawa Barat
                        </p>
                    </div>
                    
                    <!-- Copyright & Links -->
                    <div class="flex flex-col md:items-end md:justify-end space-y-4">
                        <div class="flex space-x-4 text-sm text-slate-600">
                            <a href="#" class="hover:text-blue-700 transition-colors">Bantuan</a>
                            <a href="#" class="hover:text-blue-700 transition-colors">Kebijakan Privasi</a>
                        </div>
                        <span class="text-xs text-slate-500">&copy; {{ date('Y') }} CCTV Universitas Padjadjaran. All rights reserved.</span>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

    <!-- Initialize Swiper -->
    <script>
        var swiper = new Swiper(".mySwiper", {
            initialSlide: 0,
            speed: 1500, // Make the transition slow and buttery smooth (1.5s)
            effect: "coverflow",
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: "auto",
            loop: true,
            autoplay: {
                delay: 4500, // Stay on slide for 4.5s
                disableOnInteraction: false, // JANGAN MATIKAN autoplay saat user menggeser manual
            },
            coverflowEffect: {
                rotate: -45,      // Negatif agar melengkung ke dalam (melihat dari luar)
                stretch: -10,     
                depth: 500,       
                modifier: 1,      
                slideShadows: false, 
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        });
    </script>
</body>
</html>
