<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menunggu Persetujuan - CCTV Unpad</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-effect { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-slate-50 h-screen w-full flex items-center justify-center p-6 relative">
    
    <!-- Background Gradients -->
    <div class="absolute inset-0 bg-gradient-to-br from-cyan-900/10 to-blue-900/10 -z-10"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-cyan-500 rounded-full blur-[120px] opacity-10 -z-10"></div>
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-500 rounded-full blur-[120px] opacity-10 -z-10"></div>

    <div class="w-full max-w-lg glass-effect rounded-2xl border border-cyan-100 shadow-2xl p-8 md:p-10 text-center relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-amber-400 to-orange-500"></div>

        <!-- Animated Clock / Shield Icon -->
        <div class="mb-6 inline-flex items-center justify-center w-20 h-20 rounded-full bg-amber-50 border border-amber-100 text-amber-500 relative">
            <i class="fas fa-user-clock text-3xl animate-pulse"></i>
            <span class="absolute top-0 right-0 w-4 h-4 bg-amber-500 rounded-full border-2 border-white animate-ping"></span>
        </div>

        <h2 class="text-2xl md:text-3xl font-bold text-slate-800 mb-3">Akun Menunggu Persetujuan</h2>
        
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase bg-amber-100 text-amber-700 border border-amber-200 mb-6">
            <i class="fas fa-lock"></i> Status: Butuh Approval
        </div>

        <div class="space-y-4 text-slate-600 text-sm md:text-base leading-relaxed mb-8 max-w-md mx-auto">
            <p>
                Halo, <span class="font-bold text-slate-800">{{ auth()->user()->name }}</span> ({{ auth()->user()->email }}).
            </p>
            <p>
                Akun Anda berhasil terdaftar melalui <strong>SSO Unpad (PAUS ID)</strong>. Namun, demi alasan keamanan, akun baru memerlukan persetujuan dari Administrator sebelum dapat mengakses dashboard monitoring.
            </p>
            <p class="text-xs text-slate-400 italic">
                Silakan hubungi administrator sistem atau tunggu hingga admin menetapkan hak akses (role) untuk akun Anda.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
            <!-- Refresh Button -->
            <a href="{{ route('pending-approval') }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-bold text-sm tracking-wide shadow-lg shadow-cyan-500/20 hover:shadow-cyan-500/40 hover:scale-[1.02] transition-all flex items-center justify-center gap-2 group">
                <i class="fas fa-sync-alt group-hover:rotate-180 transition-transform duration-500"></i>
                Cek Status
            </a>

            <!-- Logout Button -->
            <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-800 font-bold text-sm transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout / Keluar
                </button>
            </form>
        </div>

        <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-center space-x-2 text-xs text-slate-400">
            <img src="{{ asset('unpad-cctv.png') }}" alt="Logo" class="w-5 h-5 rounded opacity-60">
            <span>&copy; {{ date('Y') }} CCTV Unpad • Infrastructure Team</span>
        </div>
    </div>
</body>
</html>
