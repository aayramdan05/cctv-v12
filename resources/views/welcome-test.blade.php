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
            font-size: 32px;
            filter: drop-shadow(0 15px 15px rgba(0,0,0,0.15));
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
            font-size: 40px;
            filter: drop-shadow(0 15px 15px rgba(0,0,0,0.3));
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
        .feature-card {
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(8px);
            border-radius: 30px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

    <!-- Fixed Header with Center Logo -->
    <header class="fixed top-0 left-0 w-full z-50 px-6 py-4 flex justify-between items-center bg-transparent">
        <!-- Spacer Kiri agar logo bisa persis di tengah -->
        <div class="flex-1"></div>
        
        <!-- Logo di Tengah -->
        <div class="flex flex-col items-center flex-1">
            <span class="text-[10px] font-extrabold tracking-[0.2em] text-gray-300 mb-1 uppercase leading-none" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);">CCTV</span>
            <div class="bg-white p-2 rounded-xl shadow-lg">
                <img src="{{ asset('logo-unpad-secondary.png') }}" alt="CCTV UNPAD" class="h-6">
            </div>
        </div>
        
        <!-- Tombol Login Kanan -->
        <div class="flex-1 flex justify-end">
            <a href="{{ route('login') }}" class="px-6 py-2 bg-blue-600 text-white rounded-full text-sm font-bold hover:bg-blue-500 transition-colors shadow-lg shadow-blue-900/50">
                Log In
            </a>
        </div>
    </header>

    <main>
        <!-- 3D Carousel Section -->
        <section class="h-screen w-full relative bg-gray-900" style="background-image: linear-gradient(to bottom, rgba(15,23,42,0.8), rgba(15,23,42,0.9)), url('{{ asset('bg.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
            
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    
                    <!-- SLIDE 1: Sistem Pemantauan -->
                    <div class="swiper-slide p-6">
                        <div class="relative w-full max-w-3xl h-[250px] flex items-center justify-center mb-8 mt-4">
                            <!-- Center Main Icon -->
                            <div class="center-app"><i class="fas fa-video text-white text-4xl"></i></div>
                            <!-- Lines (Horizontal) -->
                            <div class="line hidden md:block" style="width: 220px; height: 2px; top: 50%; left: 10%;"></div>
                            <div class="line hidden md:block" style="width: 220px; height: 2px; top: 50%; right: 10%;"></div>
                            <!-- Left Nodes -->
                            <div class="floating-node hidden md:flex" style="top: 15%; left: 15%; color: #eab308; animation-delay: 0s;"><i class="fas fa-lightbulb"></i></div>
                            <div class="floating-node hidden sm:flex" style="top: 40%; left: 5%; color: #3b82f6; animation-delay: 0.5s;"><i class="fas fa-server"></i></div>
                            <div class="floating-node hidden md:flex" style="top: 65%; left: 20%; color: #0ea5e9; animation-delay: 1s;"><i class="fas fa-shield-halved"></i></div>
                            <!-- Right Nodes -->
                            <div class="floating-node hidden md:flex" style="top: 15%; right: 20%; color: #ef4444; animation-delay: 0.2s;"><i class="fas fa-bolt"></i></div>
                            <div class="floating-node hidden sm:flex" style="top: 40%; right: 5%; color: #334155; animation-delay: 0.7s;"><i class="fas fa-eye"></i></div>
                            <div class="floating-node hidden md:flex" style="top: 65%; right: 15%; color: #10b981; animation-delay: 1.2s;"><i class="fas fa-network-wired"></i></div>
                        </div>

                        <div class="text-center px-4 max-w-2xl mx-auto">
                            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 text-white">
                                Sistem Pemantauan <br> Kampus Pintar
                            </h1>
                            <p class="text-gray-300 text-sm md:text-base mb-8">
                                CCTV Unpad adalah platform pemantauan modern dan terpusat yang dirancang khusus untuk memastikan keamanan seluruh lingkungan akademik Anda.
                            </p>
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-8 py-3.5 bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-transform hover:-translate-y-1 inline-block">Buka Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="px-8 py-3.5 bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-transform hover:-translate-y-1 inline-block">Masuk ke Sistem</a>
                            @endauth
                        </div>
                    </div>

                    <!-- SLIDE 2: Solusi Keamanan -->
                    <div class="swiper-slide p-6">
                        <div class="w-20 h-20 flex items-center justify-center mb-6 relative z-20 mx-auto mt-4" style="filter: drop-shadow(0 15px 15px rgba(0,0,0,0.15));">
                            <i class="fas fa-shield-alt text-6xl text-purple-600"></i>
                        </div>
                        
                        <div class="text-center px-4 relative z-20 max-w-2xl mx-auto mb-10">
                            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 text-white">Solusi Keamanan <br> Terpusat</h1>
                            <p class="text-gray-300 text-sm md:text-base">
                                Sederhanakan proses pemantauan CCTV dalam satu platform terpusat untuk meningkatkan kewaspadaan dan transparansi keamanan kampus.
                            </p>
                        </div>

                        <!-- Floating Orbiting Icons -->
                        <div class="orbit-system-intro hidden sm:block">
                            <div class="orbit-icon orbit-1 text-blue-500"><i class="fas fa-video"></i></div>
                            <div class="orbit-icon orbit-2 text-red-500"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="orbit-icon orbit-3 text-purple-500"><i class="fas fa-desktop"></i></div>
                            <div class="orbit-icon orbit-4 text-yellow-500"><i class="fas fa-users"></i></div>
                        </div>
                    </div>

                    <!-- SLIDE 3: Fitur -->
                    <div class="swiper-slide p-8 md:p-12">
                        <div class="text-center mb-8">
                            <h2 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">Diciptakan untuk semua <br>kebutuhan keamanan</h2>
                            <p class="mt-3 text-gray-300 text-sm md:text-base max-w-xl mx-auto">Platform multifungsi yang dapat diandalkan oleh seluruh pimpinan dan operator kampus.</p>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
                            <!-- Feature 1 -->
                            <div class="feature-card flex flex-col items-center text-center">
                                <div class="w-14 h-14 bg-blue-500/20 text-blue-400 rounded-[20px] flex items-center justify-center text-xl mb-4"><i class="fas fa-bolt"></i></div>
                                <h3 class="text-lg font-bold text-white mb-2">Streaming Cepat</h3>
                                <p class="text-gray-300 text-xs leading-relaxed">Nikmati video langsung dengan jeda minimal berkat teknologi WebRTC.</p>
                            </div>
                            <!-- Feature 2 -->
                            <div class="feature-card flex flex-col items-center text-center">
                                <div class="w-14 h-14 bg-cyan-500/20 text-cyan-400 rounded-[20px] flex items-center justify-center text-xl mb-4"><i class="fas fa-heartbeat"></i></div>
                                <h3 class="text-lg font-bold text-white mb-2">Cek Otomatis</h3>
                                <p class="text-gray-300 text-xs leading-relaxed">Sistem rutin memonitor koneksi kamera dan mengirim notifikasi jika offline.</p>
                            </div>
                            <!-- Feature 3 -->
                            <div class="feature-card flex flex-col items-center text-center">
                                <div class="w-14 h-14 bg-purple-500/20 text-purple-400 rounded-[20px] flex items-center justify-center text-xl mb-4"><i class="fas fa-lock"></i></div>
                                <h3 class="text-lg font-bold text-white mb-2">Akses Fleksibel</h3>
                                <p class="text-gray-300 text-xs leading-relaxed">Manajemen peran memastikan hanya personel berwenang yang dapat mengakses.</p>
                            </div>
                        </div>
                    </div>

                    <!-- DUPLICATE SLIDE 1 (Untuk memastikan loop berjalan mulus tanpa bug) -->
                    <div class="swiper-slide p-6">
                        <div class="relative w-full max-w-3xl h-[250px] flex items-center justify-center mb-8 mt-4">
                            <div class="center-app"><i class="fas fa-video text-white text-4xl"></i></div>
                            <div class="line hidden md:block" style="width: 220px; height: 2px; top: 50%; left: 10%;"></div>
                            <div class="line hidden md:block" style="width: 220px; height: 2px; top: 50%; right: 10%;"></div>
                            <div class="floating-node hidden md:flex" style="top: 15%; left: 15%; color: #eab308; animation-delay: 0s;"><i class="fas fa-lightbulb"></i></div>
                            <div class="floating-node hidden sm:flex" style="top: 40%; left: 5%; color: #3b82f6; animation-delay: 0.5s;"><i class="fas fa-server"></i></div>
                            <div class="floating-node hidden md:flex" style="top: 65%; left: 20%; color: #0ea5e9; animation-delay: 1s;"><i class="fas fa-shield-halved"></i></div>
                            <div class="floating-node hidden md:flex" style="top: 15%; right: 20%; color: #ef4444; animation-delay: 0.2s;"><i class="fas fa-bolt"></i></div>
                            <div class="floating-node hidden sm:flex" style="top: 40%; right: 5%; color: #334155; animation-delay: 0.7s;"><i class="fas fa-eye"></i></div>
                            <div class="floating-node hidden md:flex" style="top: 65%; right: 15%; color: #10b981; animation-delay: 1.2s;"><i class="fas fa-network-wired"></i></div>
                        </div>
                        <div class="text-center px-4 max-w-2xl mx-auto">
                            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 text-white">Sistem Pemantauan <br> Kampus Pintar</h1>
                            <p class="text-gray-300 text-sm md:text-base mb-8">CCTV Unpad adalah platform pemantauan modern dan terpusat yang dirancang khusus untuk memastikan keamanan seluruh lingkungan akademik Anda.</p>
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-8 py-3.5 bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-transform hover:-translate-y-1 inline-block">Buka Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="px-8 py-3.5 bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-transform hover:-translate-y-1 inline-block">Masuk ke Sistem</a>
                            @endauth
                        </div>
                    </div>

                    <!-- DUPLICATE SLIDE 2 -->
                    <div class="swiper-slide p-6">
                        <div class="w-20 h-20 flex items-center justify-center mb-6 relative z-20 mx-auto mt-4" style="filter: drop-shadow(0 15px 15px rgba(0,0,0,0.15));">
                            <i class="fas fa-shield-alt text-6xl text-purple-600"></i>
                        </div>
                        <div class="text-center px-4 relative z-20 max-w-2xl mx-auto mb-10">
                            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 text-white">Solusi Keamanan <br> Terpusat</h1>
                            <p class="text-gray-300 text-sm md:text-base">Sederhanakan proses pemantauan CCTV dalam satu platform terpusat untuk meningkatkan kewaspadaan dan transparansi keamanan kampus.</p>
                        </div>
                        <div class="orbit-system-intro hidden sm:block">
                            <div class="orbit-icon orbit-1 text-blue-500"><i class="fas fa-video"></i></div>
                            <div class="orbit-icon orbit-2 text-red-500"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="orbit-icon orbit-3 text-purple-500"><i class="fas fa-desktop"></i></div>
                            <div class="orbit-icon orbit-4 text-yellow-500"><i class="fas fa-users"></i></div>
                        </div>
                    </div>

                    <!-- DUPLICATE SLIDE 3 -->
                    <div class="swiper-slide p-8 md:p-12">
                        <div class="text-center mb-8">
                            <h2 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">Diciptakan untuk semua <br>kebutuhan keamanan</h2>
                            <p class="mt-3 text-gray-300 text-sm md:text-base max-w-xl mx-auto">Platform multifungsi yang dapat diandalkan oleh seluruh pimpinan dan operator kampus.</p>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
                            <div class="feature-card flex flex-col items-center text-center">
                                <div class="w-14 h-14 bg-blue-500/20 text-blue-400 rounded-[20px] flex items-center justify-center text-xl mb-4"><i class="fas fa-bolt"></i></div>
                                <h3 class="text-lg font-bold text-white mb-2">Streaming Cepat</h3>
                                <p class="text-gray-300 text-xs leading-relaxed">Nikmati video langsung dengan jeda minimal berkat teknologi WebRTC.</p>
                            </div>
                            <div class="feature-card flex flex-col items-center text-center">
                                <div class="w-14 h-14 bg-cyan-500/20 text-cyan-400 rounded-[20px] flex items-center justify-center text-xl mb-4"><i class="fas fa-heartbeat"></i></div>
                                <h3 class="text-lg font-bold text-white mb-2">Cek Otomatis</h3>
                                <p class="text-gray-300 text-xs leading-relaxed">Sistem rutin memonitor koneksi kamera dan mengirim notifikasi jika offline.</p>
                            </div>
                            <div class="feature-card flex flex-col items-center text-center">
                                <div class="w-14 h-14 bg-purple-500/20 text-purple-400 rounded-[20px] flex items-center justify-center text-xl mb-4"><i class="fas fa-lock"></i></div>
                                <h3 class="text-lg font-bold text-white mb-2">Akses Fleksibel</h3>
                                <p class="text-gray-300 text-xs leading-relaxed">Manajemen peran memastikan hanya personel berwenang yang dapat mengakses.</p>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- Add Pagination -->
                <div class="swiper-pagination"></div>
            </div>
        </section>

        <!-- FOOTER SECTION (Normal Manual Scroll) -->
        <section class="bg-slate-800 text-white py-20 flex flex-col items-center">
            <div class="max-w-4xl w-full px-6 text-center">
                <div class="flex flex-col items-center mb-6">
                    <span class="text-xs font-extrabold tracking-[0.2em] text-slate-400 mb-1 uppercase">CCTV</span>
                    <div class="bg-white p-4 rounded-2xl inline-block shadow-lg">
                        <img src="{{ asset('logo-unpad-secondary.png') }}" alt="Logo" class="h-10">
                    </div>
                </div>
                <h2 class="text-3xl font-bold mb-4 tracking-tight">Siap memantau area kampus?</h2>
                <p class="text-slate-400 mb-10 text-sm max-w-lg mx-auto leading-relaxed">Geser kembali carousel ke atas untuk melihat fitur, atau langsung akses sistem jika Anda sudah siap.</p>
                <a href="{{ route('login') }}" class="px-8 py-3 bg-white text-slate-900 font-bold rounded-full shadow-lg hover:scale-105 transition-transform inline-block">
                    Mulai Sekarang
                </a>
                
                <div class="border-t border-slate-700 pt-8 mt-16 text-slate-500 text-xs flex justify-between items-center flex-col sm:flex-row gap-4 w-full">
                    <span>&copy; {{ date('Y') }} Universitas Padjadjaran.</span>
                    <span class="flex space-x-4">
                        <a href="#" class="hover:text-white transition-colors">Bantuan</a>
                        <a href="#" class="hover:text-white transition-colors">Privasi</a>
                    </span>
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
