<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menunggu Persetujuan - CCTV Unpad</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 20px 40px -10px rgba(245, 158, 11, 0.1); /* Amber shadow tint */
        }

        /* --- ANIMASI CCTV SCANNER (PENDING VERSION) --- */
        .security-screen {
            background-color: #0f172a;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
            position: relative;
            overflow: hidden;
        }

        /* Garis Laser Amber */
        .laser-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: #f59e0b; /* Amber-500 */
            box-shadow: 0 0 15px #f59e0b, 0 0 30px #f59e0b;
            animation: scan-vertical 3s ease-in-out infinite;
            opacity: 0.8;
            z-index: 10;
        }

        /* Efek bayangan laser */
        .laser-line::after {
            content: "";
            position: absolute;
            top: -50px;
            left: 0;
            width: 100%;
            height: 50px;
            background: linear-gradient(to bottom, transparent, rgba(245, 158, 11, 0.2));
        }

        @keyframes scan-vertical {
            0%, 100% { top: 5%; }
            50% { top: 95%; }
        }

        /* Kedipan Lampu Amber */
        .blink-amber {
            animation: blink 1.2s steps(2) infinite;
        }
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        /* Icon Kamera Bergetar secara periodik */
        .cctv-shaker {
            animation: shake 3s ease-in-out infinite;
        }
        @keyframes shake {
            0%, 45%, 55%, 100% { transform: scale(1); }
            48% { transform: scale(1.05) rotate(-3deg); }
            50% { transform: scale(1.05) rotate(3deg); }
            52% { transform: scale(1.05) rotate(-3deg); }
        }

        /* Putaran lambat ikon jam */
        .spin-slow {
            animation: spin 10s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-slate-100 h-screen w-full flex items-center justify-center overflow-hidden relative">

    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
        <div class="absolute top-10 right-10 w-80 h-80 bg-amber-200 rounded-full mix-blend-multiply filter blur-[100px] opacity-25 animate-blob"></div>
        <div class="absolute bottom-10 left-10 w-80 h-80 bg-cyan-200 rounded-full mix-blend-multiply filter blur-[100px] opacity-20 animate-blob animation-delay-2000"></div>
    </div>

    <div class="glass-card rounded-3xl overflow-hidden max-w-4xl w-[90%] mx-auto relative z-10 flex flex-col md:flex-row shadow-2xl">
        
        <!-- Sisi Kiri: Animasi Monitor CCTV & Status Pending -->
        <div class="security-screen w-full md:w-5/12 min-h-[300px] md:h-auto relative flex flex-col items-center justify-center p-8 border-r border-slate-200/20">
            
            <div class="laser-line"></div>

            <div class="absolute top-4 left-4 flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-amber-500 blink-amber"></div>
                <span class="text-amber-500 font-mono text-[10px] tracking-widest">PENDING ●</span>
            </div>
            <div class="absolute top-4 right-4">
                <span class="text-slate-500 font-mono text-[10px]">CAM_APPROVAL</span>
            </div>

            <div class="relative z-20 text-center">
                <!-- Stacked Icon: Kamera CCTV + Jam Berputar -->
                <div class="mb-4 inline-flex p-6 rounded-full border-2 border-amber-500/30 bg-amber-500/10 cctv-shaker relative group cursor-pointer">
                    <i class="fas fa-video text-6xl text-amber-500 drop-shadow-[0_0_15px_rgba(245,158,11,0.5)]"></i>
                    <!-- Jam Pending di pojok atas ikon kamera -->
                    <div class="absolute -top-1 -right-1 w-8 h-8 rounded-full bg-slate-900 border border-amber-500/50 flex items-center justify-center">
                        <i class="fas fa-clock text-sm text-amber-400 spin-slow group-hover:text-amber-300"></i>
                    </div>
                </div>
                
                <div class="space-y-1">
                    <h2 class="text-2xl font-mono font-bold text-white tracking-wider">WAITING</h2>
                    <div class="px-3 py-1 bg-amber-500 text-black font-bold text-[10px] inline-block rounded uppercase tracking-[0.2em]">
                        Awaiting Review
                    </div>
                </div>
            </div>

            <div class="absolute bottom-4 w-full px-8 flex justify-between text-[9px] text-slate-500 font-mono">
                <span>USER: {{ substr(auth()->user()->name ?? '?', 0, 10) }}...</span>
                <span id="current-time">00:00:00</span>
            </div>
        </div>

        <!-- Sisi Kanan: Detail Informasi dan Aksi -->
        <div class="w-full md:w-7/12 p-10 md:p-14 flex flex-col justify-center bg-white/60">
            
            <div class="mb-6">
                <span class="text-xs font-bold text-amber-600 uppercase tracking-wider border-b-2 border-amber-500 pb-1">Verifikasi Akun</span>
            </div>

            <h1 class="text-3xl font-bold text-slate-800 mb-4">Menunggu Persetujuan.</h1>
            
            <div class="space-y-4 text-slate-600 mb-6 leading-relaxed">
                <p>
                    Halo, <span class="font-bold text-slate-800 bg-slate-200/60 px-2 py-0.5 rounded text-sm">{{ auth()->user()->name }}</span> ({{ auth()->user()->email }}).
                </p>
                <p>
                    Akun Anda berhasil terdaftar menggunakan **SSO Unpad (PAUS ID)**. Demi alasan keamanan sistem, akun baru memerlukan persetujuan serta pengaturan role (*plotting*) oleh Administrator sebelum dapat mengakses dashboard monitoring.
                </p>
            </div>

            <div class="p-4 bg-amber-50 rounded-xl border border-amber-100/70 mb-8 flex items-start gap-3">
                <i class="fas fa-info-circle text-amber-500 mt-0.5"></i>
                <div class="text-xs text-amber-700 leading-5">
                    <strong>Pemberitahuan:</strong> Silakan hubungi tim infrastruktur jaringan atau Administrator Sistem untuk aktivasi akun Anda.
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('pending-approval') }}" 
                   class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-900 font-bold shadow-lg shadow-amber-500/20 hover:shadow-amber-500/30 transition-all duration-300 flex items-center gap-2 group">
                    <i class="fas fa-sync-alt group-hover:rotate-180 transition-transform duration-500"></i> Cek Status Akun
                </a>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-6 py-3 rounded-xl bg-white border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 hover:text-red-600 transition-all duration-300 flex items-center gap-2">
                        <i class="fas fa-sign-out-alt"></i> Logout / Ganti Akun
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Jam Realtime untuk Monitor CCTV
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').innerText = now.toISOString().replace('T', ' ').split('.')[0];
        }
        setInterval(updateTime, 1000);
        updateTime();
    </script>
    
    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob { animation: blob 10s infinite; }
        .animation-delay-2000 { animation-delay: 2s; }
    </style>
</body>
</html>
