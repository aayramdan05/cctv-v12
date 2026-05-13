<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CCTV Unpad</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-effect { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
        .hover-glow:hover { box-shadow: 0 0 20px rgba(6, 182, 212, 0.4); transition: all 0.3s ease; }
    </style>
</head>
<body class="bg-slate-50 h-screen w-full overflow-hidden">

    <div class="flex h-full w-full">
        
        <div class="hidden lg:flex w-1/2 relative items-center justify-center bg-slate-900 overflow-hidden">
            <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=2026&auto=format&fit=crop" 
                 class="absolute inset-0 w-full h-full object-cover opacity-60" 
                 alt="Background">
            
            <div class="absolute inset-0 bg-gradient-to-br from-cyan-900/80 to-blue-900/80 mix-blend-multiply"></div>
            
            <div class="relative z-10 p-12 text-white max-w-xl">
                <div class="mb-6 inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-cyan-400 to-blue-500 shadow-lg shadow-cyan-500/30">
                    <i class="fas fa-university text-3xl"></i>
                </div>
                <h1 class="text-5xl font-bold mb-6 leading-tight">Smart Campus Monitoring System</h1>
                <p class="text-lg text-cyan-100 font-light leading-relaxed">
                    Pantau keamanan lingkungan kampus Universitas Padjadjaran secara real-time, aman, dan terintegrasi dalam satu dashboard modern.
                </p>
                
                <div class="mt-12 flex items-center space-x-4 text-sm text-cyan-200/60">
                    <span>&copy; {{ date('Y') }} Unpad</span>
                    <span>•</span>
                    <span>Network Infrastructure</span>
                </div>
            </div>
            
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-cyan-500 rounded-full blur-[100px] opacity-20"></div>
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-blue-500 rounded-full blur-[100px] opacity-20"></div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 relative bg-white">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-cyan-400 to-blue-500 lg:hidden"></div>

            <div class="w-full max-w-md">
                <div class="lg:hidden mb-8 text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-500 mb-4">
                        <i class="fas fa-university text-white text-lg"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800">CCTV UNPAD</h2>
                </div>

                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-slate-800 mb-2">Welcome Back!</h2>
                    <p class="text-slate-500">Silakan login untuk mengakses dashboard monitoring.</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 flex items-start gap-3 animate-shake">
                        <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                        <div class="text-sm text-red-700 font-medium">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-slate-400">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input id="email" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 outline-none transition-all text-slate-700" 
                                   type="email" name="email" :value="old('email')" required autofocus autocomplete="username" 
                                   placeholder="nama@unpad.ac.id" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-slate-400">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input id="password" class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-100 outline-none transition-all text-slate-700" 
                                   type="password" name="password" required autocomplete="current-password" 
                                   placeholder="••••••••" />
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-cyan-500 shadow-sm focus:ring-cyan-200" name="remember">
                            <span class="ml-2 text-sm text-slate-500 font-medium">Ingat saya</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-cyan-600 hover:text-cyan-700 font-medium hover:underline" href="{{ route('password.request') }}">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-bold text-sm uppercase tracking-wide shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 hover:scale-[1.02] transition-all duration-300">
                        Sign In
                    </button>
                </form>

                <div class="mt-6">
                    <div class="relative mb-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-slate-400">Atau login dengan</span>
                        </div>
                    </div>

                    <a href="{{ route('auth.paus') }}" class="w-full flex items-center justify-center gap-3 py-3.5 rounded-xl border border-slate-200 bg-white text-slate-700 font-bold text-sm shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all duration-300 group">
                        <div class="w-6 h-6 rounded bg-gradient-to-br from-cyan-400 to-blue-500 flex items-center justify-center text-white text-[10px] group-hover:scale-110 transition-transform">
                            <i class="fas fa-university"></i>
                        </div>
                        PAUS ID (SSO Unpad)
                    </a>
                </div>
                
                <div class="mt-8 text-center">
                    <p class="text-sm text-slate-400">
                        Belum punya akun? Hubungi Administrator Sistem.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>