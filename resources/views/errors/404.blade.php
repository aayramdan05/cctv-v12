<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan (404) - CCTV Unpad</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        
        /* Glassmorphism Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.10);
        }
        
        /* Efek Layar Rusak (Static Noise Animation) */
        .monitor-screen {
            background-color: #1e293b; /* slate-800 */
            position: relative;
            overflow: hidden;
        }
        .monitor-screen::before {
            content: "";
            position: absolute;
            top: -100%;
            left: -100%;
            width: 300%;
            height: 300%;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.15'/%3E%3C/svg%3E");
            animation: static-noise 0.5s steps(5) infinite both;
            pointer-events: none;
        }
        @keyframes static-noise {
            0% { transform: translate(0,0) }
            10% { transform: translate(-5%,-5%) }
            20% { transform: translate(-10%,5%) }
            30% { transform: translate(5%,-10%) }
            40% { transform: translate(-5%,15%) }
            50% { transform: translate(-10%,5%) }
            60% { transform: translate(15%,0) }
            70% { transform: translate(0,10%) }
            80% { transform: translate(-15%,0) }
            90% { transform: translate(10%,5%) }
            100% { transform: translate(5%,0) }
        }

        /* Efek Scanline Monitor Jadul */
        .scanline {
            width: 100%;
            height: 100px;
            z-index: 8;
            background: linear-gradient(0deg, rgba(0,0,0,0) 0%, rgba(6, 182, 212, 0.2) 10%, rgba(0,0,0,0.1) 100%);
            opacity: 0.5;
            position: absolute;
            bottom: 100%;
            animation: scanline 6s linear infinite;
        }
        @keyframes scanline {
            0% { bottom: 100%; }
            80% { bottom: -100px; }
            100% { bottom: -100px; }
        }
    </style>
</head>
<body class="bg-slate-200 h-screen w-full flex items-center justify-center overflow-hidden relative">

    <div class="absolute top-10 left-10 w-72 h-72 bg-cyan-300 rounded-full mix-blend-multiply filter blur-[80px] opacity-40 animate-blob"></div>
    <div class="absolute bottom-10 right-10 w-72 h-72 bg-blue-400 rounded-full mix-blend-multiply filter blur-[80px] opacity-40 animate-blob animation-delay-2000"></div>
    <div class="absolute bottom-1/3 left-1/2 w-72 h-72 bg-slate-300 rounded-full mix-blend-multiply filter blur-[80px] opacity-40 animate-blob animation-delay-4000"></div>

    <div class="glass-card rounded-3xl overflow-hidden max-w-3xl w-[90%] mx-auto relative z-10 flex flex-col md:flex-row shadow-2xl transform transition-all hover:scale-[1.01] duration-500">
        
        <div class="monitor-screen w-full md:w-1/2 h-64 md:h-auto relative flex items-center justify-center border-b md:border-b-0 md:border-r border-slate-700/50 p-8">
            <div class="scanline"></div>
            
            <div class="text-center relative z-10">
                <div class="flex items-center justify-center gap-4 mb-6 text-slate-300/80">
                    <i class="fas fa-video text-4xl animate-pulse"></i>
                    <i class="fas fa-unlink text-6xl text-red-500/80"></i>
                    <i class="fas fa-desktop text-4xl opacity-50"></i>
                </div>

                <h2 class="text-5xl font-mono font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-slate-200">
                    404
                </h2>
                <div class="mt-2 px-4 py-1 bg-red-500/20 border border-red-500/50 rounded inline-block">
                    <p class="text-red-400 font-mono text-xs font-bold tracking-[0.2em] animate-pulse">NO SIGNAL / LOST</p>
                </div>
                
                <div class="absolute bottom-2 right-2 font-mono text-[10px] text-slate-500 opacity-70">
                    REC: <span id="current-time"></span>
                </div>
            </div>

            <div class="absolute top-2 left-2 w-4 h-4 border-t-2 border-l-2 border-cyan-500/50"></div>
            <div class="absolute top-2 right-2 w-4 h-4 border-t-2 border-r-2 border-cyan-500/50"></div>
            <div class="absolute bottom-2 left-2 w-4 h-4 border-b-2 border-l-2 border-cyan-500/50"></div>
            <div class="absolute bottom-2 right-2 w-4 h-4 border-b-2 border-r-2 border-cyan-500/50"></div>
        </div>

        <div class="w-full md:w-1/2 p-10 md:p-14 flex flex-col justify-center bg-white/40">
            
            <h1 class="text-3xl font-bold text-slate-800 mb-3">Tidak ditemukan</h1>
            <p class="text-lg text-slate-600 mb-6 leading-relaxed">
                Halaman yang Anda cari tidak dapat ditemukan atau berada di luar jangkauan area pengawasan kami.
            </p>

            <div class="space-y-4">
                <p class="text-sm text-slate-500">
                    <i class="fas fa-info-circle mr-2 text-cyan-500"></i>
                    Mungkin URL salah ketik atau halaman telah dipindahkan.
                </p>

                <div class="flex flex-col sm:flex-row gap-3 pt-4">
                    <a href="{{ url('/') }}" 
                       class="px-6 py-3 rounded-xl bg-slate-800 text-white font-bold shadow-md hover:bg-slate-900 hover:-translate-y-0.5 transition-all duration-300 text-center flex items-center justify-center gap-2 group">
                        <i class="fas fa-home group-hover:animate-bounce"></i> Dashboard
                    </a>
                    
                    <button onclick="history.back()" 
                       class="px-6 py-3 rounded-xl bg-white border-2 border-slate-200 text-slate-600 font-bold hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 transition-all duration-300 text-center">
                        Kembali
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-GB', { hour12: false });
            const dateString = now.toLocaleDateString('en-GB').split('/').reverse().join('-');
            document.getElementById('current-time').innerText = `${dateString} ${timeString}`;
        }
        setInterval(updateTime, 1000);
        updateTime();
    </script>

    <style type="text/tailwindcss">
        @layer utilities {
            .animation-delay-2000 { animation-delay: 2s; }
            .animation-delay-4000 { animation-delay: 4s; }
        }
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
    </style>
</body>
</html>