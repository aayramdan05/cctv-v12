<!DOCTYPE html>
<html lang="en">
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
            overflow: hidden; /* Hide scrollbars, handle scrolling via animation */
        }

        /* Container that moves up to simulate scrolling down */
        .scroll-container {
            height: 100vh;
            width: 100vw;
            animation: autoScroll 15s infinite;
        }

        /* 3 slides: Frame 1 (0-33%), Frame 2 (33-66%), Footer (66-100%) */
        @keyframes autoScroll {
            0%, 25% { transform: translateY(0); }
            33%, 58% { transform: translateY(-100vh); }
            66%, 90% { transform: translateY(-200vh); }
            95%, 100% { transform: translateY(0); }
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
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #a855f7, #6366f1);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);
            z-index: 10;
        }

        .floating-node {
            position: absolute;
            width: 70px;
            height: 70px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            background: white;
            z-index: 10;
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        /* Slide 2 Elements - Orbiting spinner */
        .orbit-container {
            position: absolute;
            width: 140px;
            height: 140px;
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
            top: -6px;
            left: 50%;
            transform: translateX(-50%);
            width: 12px;
            height: 12px;
            background-color: #3b82f6;
            border-radius: 50%;
            box-shadow: 0 0 10px #3b82f6;
        }

        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        .slide-2-icon {
            width: 90px;
            height: 90px;
            background: white;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: #475569;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            z-index: 5;
        }
    </style>
</head>
<body>

    <!-- Fixed Header with Login Button -->
    <header class="fixed top-0 left-0 w-full z-50 p-6 flex justify-between items-center bg-white/80 backdrop-blur-md border-b border-gray-100">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('unpad-cctv.png') }}" alt="Logo" class="w-8 h-8 rounded-lg">
            <span class="font-bold text-xl tracking-tight">CCTV UNPAD</span>
        </div>
        <div class="flex space-x-6 items-center">
            <nav class="hidden md:flex space-x-6 text-sm font-medium text-gray-600">
                <a href="#" class="hover:text-black">Product</a>
                <a href="#" class="hover:text-black">Features</a>
                <a href="#" class="hover:text-black">Monitoring</a>
            </nav>
            <a href="{{ route('login') }}" class="px-6 py-2.5 bg-black text-white rounded-full text-sm font-semibold hover:bg-gray-800 transition-colors shadow-lg shadow-black/20">
                Log In
            </a>
        </div>
    </header>

    <div class="scroll-container">
        
        <!-- SLIDE 1: Image 1 concept (Connecting nodes) -->
        <div class="slide">
            <div class="relative w-full max-w-4xl h-[400px] flex items-center justify-center mb-10 mt-20">
                
                <!-- Center Main Icon -->
                <div class="center-app">
                    <i class="fas fa-video text-white text-5xl"></i>
                </div>

                <!-- Lines (Horizontal & Diagonal) -->
                <div class="line" style="width: 250px; height: 2px; top: 50%; left: 15%;"></div>
                <div class="line" style="width: 250px; height: 2px; top: 50%; right: 15%;"></div>
                
                <!-- Left Nodes -->
                <div class="floating-node" style="top: 15%; left: 20%; background: #fef08a; color: #a16207; animation-delay: 0s;">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div class="floating-node" style="top: 40%; left: 8%; color: #3b82f6; animation-delay: 0.5s;">
                    <i class="fas fa-server"></i>
                </div>
                <div class="floating-node" style="top: 65%; left: 23%; background: #38bdf8; color: white; animation-delay: 1s;">
                    <i class="fas fa-shield-halved"></i>
                </div>

                <!-- Right Nodes -->
                <div class="floating-node" style="top: 15%; right: 23%; background: #ef4444; color: white; animation-delay: 0.2s;">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="floating-node" style="top: 40%; right: 8%; color: #0f172a; animation-delay: 0.7s;">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="floating-node" style="top: 65%; right: 20%; color: #10b981; animation-delay: 1.2s;">
                    <i class="fas fa-network-wired"></i>
                </div>
            </div>

            <div class="text-center px-4">
                <h1 class="text-6xl md:text-7xl font-extrabold tracking-tight mb-6">
                    Smart Campus <br> Monitoring System
                </h1>
                <p class="text-gray-500 max-w-2xl mx-auto text-lg mb-8">
                    CCTV Unpad is a modern, all-in-one monitoring platform designed to perfectly secure your academic environment.
                </p>
                <a href="{{ route('login') }}" class="px-8 py-4 bg-gradient-to-r from-red-500 to-orange-500 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-all inline-block hover:-translate-y-1">
                    Buka Dashboard
                </a>
            </div>
        </div>

        <!-- SLIDE 2: Image 2 concept (Floating cards with orbiting spinners) -->
        <div class="slide">
            
            <!-- Center Icon -->
            <div class="w-20 h-20 bg-white rounded-3xl shadow-xl flex items-center justify-center mb-8 relative z-20">
                <i class="fas fa-shield-alt text-4xl text-purple-600"></i>
            </div>
            
            <div class="text-center px-4 relative z-20">
                <h1 class="text-6xl md:text-7xl font-extrabold tracking-tight mb-6">
                    Core Security <br> solutions
                </h1>
                <p class="text-gray-500 max-w-2xl mx-auto text-lg mb-8">
                    Streamline CCTV processes in one centralized platform, enhancing campus security and transparency.
                </p>
                <a href="{{ route('login') }}" class="px-8 py-4 bg-purple-500 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-all inline-block hover:-translate-y-1">
                    Masuk ke Sistem
                </a>
            </div>

            <!-- Floating Orbiting Icons (Left Side) -->
            <div class="absolute top-[20%] left-[10%]">
                <div class="orbit-container">
                    <div class="orbit-ring"><div class="orbit-dot"></div></div>
                    <div class="slide-2-icon"><i class="fas fa-video text-blue-500"></i></div>
                </div>
            </div>
            <div class="absolute top-[50%] left-[5%]">
                <div class="orbit-container" style="animation-delay: -2s;">
                    <div class="orbit-ring" style="animation-duration: 10s;"><div class="orbit-dot bg-red-500 box-shadow-red"></div></div>
                    <div class="slide-2-icon"><i class="fas fa-exclamation-triangle text-red-500"></i></div>
                </div>
            </div>
            <div class="absolute top-[75%] left-[15%]">
                <div class="orbit-container" style="animation-delay: -4s;">
                    <div class="orbit-ring" style="animation-duration: 12s; animation-direction: reverse;"><div class="orbit-dot bg-green-500"></div></div>
                    <div class="slide-2-icon"><i class="fas fa-server text-green-600"></i></div>
                </div>
            </div>

            <!-- Floating Orbiting Icons (Right Side) -->
            <div class="absolute top-[25%] right-[10%]">
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
            <div class="absolute top-[80%] right-[20%]">
                <div class="orbit-container" style="animation-delay: -5s;">
                    <div class="orbit-ring" style="animation-duration: 7s;"><div class="orbit-dot bg-teal-500"></div></div>
                    <div class="slide-2-icon"><i class="fas fa-hdd text-teal-600"></i></div>
                </div>
            </div>
            
        </div>

        <!-- SLIDE 3: Footer -->
        <div class="slide bg-slate-900 text-white justify-end pb-12">
            <div class="max-w-4xl mx-auto text-center">
                <div class="mb-10">
                    <img src="{{ asset('unpad-cctv.png') }}" alt="Logo" class="w-16 h-16 mx-auto rounded-2xl opacity-80 mb-6">
                    <h2 class="text-3xl font-bold mb-4">Siap untuk memantau keamanan?</h2>
                    <p class="text-slate-400 mb-8">Akses semua fitur keamanan dalam satu klik.</p>
                    <a href="{{ route('login') }}" class="px-8 py-3 bg-white text-black font-bold rounded-full shadow hover:scale-105 transition-transform inline-block">
                        Login Sekarang
                    </a>
                </div>
                <div class="border-t border-slate-800 pt-8 mt-16 text-slate-500 text-sm">
                    &copy; {{ date('Y') }} Universitas Padjadjaran. Network Infrastructure.
                </div>
            </div>
        </div>

    </div>

</body>
</html>
