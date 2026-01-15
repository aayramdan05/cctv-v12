<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8 h-screen overflow-y-auto">
        
        <div class="max-w-5xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">API Management</h2>
                    <p class="text-sm text-slate-500">Kelola akses token untuk aplikasi pihak ketiga.</p>
                </div>
                <a href="{{ route('users.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                    <i class="fas fa-plus mr-2"></i> Tambah API Client Baru
                </a>
            </div>

            <!-- Pesan Sukses -->
            @if(session('success'))
                <div class="mb-6 bg-emerald-100 border border-emerald-400 text-emerald-800 px-4 py-4 rounded-xl shadow-sm">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-check-circle text-xl"></i>
                        <h3 class="font-bold text-lg">Token Berhasil Dibuat!</h3>
                    </div>
                    <p class="text-sm">Salin token ini sekarang. Token tidak akan ditampilkan lagi.</p>
                    <div class="mt-3 bg-white p-3 rounded border border-emerald-200 font-mono text-sm break-all select-all text-slate-700">
                        {{ str_replace('Token berhasil dibuat untuk ', '', session('success')) }}
                        {{-- (Parsing sederhana string session, atau sesuaikan di controller agar kirim array) --}}
                        {{-- Sebaiknya di controller: with('new_token', $token->plainTextToken) agar lebih rapi --}}
                    </div>
                </div>
            @endif

            <!-- List User API -->
            <div class="space-y-6">
                @forelse($apiUsers as $user)
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <!-- Header User -->
                        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-800">{{ $user->name }}</h3>
                                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                </div>
                            </div>
                            
                            <!-- Form Tambah Token -->
                            <form action="{{ route('api.store') }}" method="POST" class="flex gap-2">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <input type="text" name="token_name" placeholder="Nama Token (e.g. Web Prod)" required
                                       class="text-xs px-3 py-1.5 rounded border border-slate-300 focus:ring-indigo-500 w-48">
                                <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded hover:bg-indigo-700 transition">
                                    <i class="fas fa-key mr-1"></i> Generate
                                </button>
                            </form>
                        </div>

                        <!-- List Token User Ini -->
                        <div class="divide-y divide-slate-100">
                            @forelse($user->tokens as $token)
                                <div class="px-6 py-3 flex justify-between items-center hover:bg-slate-50 transition">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-key text-slate-300"></i>
                                        <div>
                                            <p class="text-sm font-bold text-slate-700">{{ $token->name }}</p>
                                            <div class="flex gap-2 text-[10px] text-slate-400">
                                                <span>Created: {{ $token->created_at->format('d M Y') }}</span>
                                                <span>•</span>
                                                <span>Last Used: {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <form action="{{ route('api.destroy', $token->id) }}" method="POST" onsubmit="return confirm('Yakin cabut akses token ini? Aplikasi yang menggunakannya akan putus koneksi.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-bold border border-red-200 hover:bg-red-50 px-2 py-1 rounded transition">
                                            Revoke
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="px-6 py-4 text-center text-slate-400 text-xs italic">
                                    Belum ada token aktif untuk user ini.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                            <i class="fas fa-users-cog text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700">Belum Ada API Client</h3>
                        <p class="text-slate-500 text-sm mb-4">Buat user dengan role 'API Viewer' di menu Manajemen User terlebih dahulu.</p>
                        <a href="{{ route('users.create') }}" class="text-indigo-600 font-bold hover:underline">Ke Manajemen User &rarr;</a>
                    </div>
                @endforelse
            </div>
        </div>
    </main>
</x-app-layout>