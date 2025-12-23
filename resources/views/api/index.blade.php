<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8 h-screen overflow-y-auto">
        
        <div class="max-w-4xl mx-auto">
            <h2 class="text-2xl font-bold text-slate-800 mb-6">API Management</h2>

            <!-- Form Generate Token -->
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm mb-8">
                <h3 class="font-bold text-lg mb-4">Buat Token Baru</h3>
                
                @if(session('success'))
                    <div class="mb-4 bg-emerald-100 border border-emerald-400 text-emerald-800 px-4 py-3 rounded break-all">
                        <p class="font-bold">Token Berhasil Dibuat!</p>
                        <p class="text-sm mt-1">Silakan copy token ini sekarang. Anda tidak akan bisa melihatnya lagi.</p>
                        <code class="block mt-2 bg-black/10 p-2 rounded font-mono text-sm select-all">
                            {{ session('success') }}
                        </code>
                        <!-- Hapus bagian teks 'API Token berhasil dibuat...' dari session string di controller agar bersih -->
                    </div>
                @endif

                <form action="{{ route('api.store') }}" method="POST" class="flex gap-4">
                    @csrf
                    <input type="text" name="token_name" placeholder="Nama Aplikasi (Misal: Mobile App Android)" required
                           class="flex-1 rounded-lg border-slate-300 focus:ring-cyan-500 focus:border-cyan-500">
                    <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white px-6 py-2 rounded-lg font-bold transition">
                        Generate Token
                    </button>
                </form>
            </div>

            <!-- List Token Aktif -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-700">Token Aktif</h3>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($tokens as $token)
                        <div class="px-6 py-4 flex justify-between items-center">
                            <div>
                                <p class="font-bold text-slate-800">{{ $token->name }}</p>
                                <p class="text-xs text-slate-500">
                                    Last Used: {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}
                                </p>
                            </div>
                            <form action="{{ route('api.destroy', $token->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus akses aplikasi ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-bold border border-red-200 hover:bg-red-50 px-3 py-1 rounded transition">
                                    Revoke
                                </button>
                            </form>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400 text-sm">Belum ada token API aktif.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </main>
</x-app-layout>