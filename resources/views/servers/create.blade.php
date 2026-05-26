<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <a href="{{ route('servers.index') }}" class="text-slate-500 hover:text-cyan-600">Master Server</a>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Tambah Baru</span>
            </div>
        </div>

        <div class="max-w-2xl mx-auto">
            <div class="glass-effect rounded-2xl p-8 border border-cyan-100">
                <h2 class="text-2xl font-bold text-slate-800 mb-6">Register Node Baru</h2>
                
                <form action="{{ route('servers.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Server (Node)</label>
                            <input type="text" name="name" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200" placeholder="Contoh: Node 1 (Rektorat)" required value="{{ old('name') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">ID Node / Server ID (Opsional)</label>
                            <input type="number" name="id" min="1" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200" placeholder="Otomatis (Auto Increment)" value="{{ old('id') }}">
                            <p class="text-[10px] text-slate-400 mt-1">Kosongkan untuk auto-increment, atau tentukan ID kustom (misal: 3).</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">IP Address</label>
                            <input type="text" name="ip_address" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 font-mono" placeholder="10.69.69.x" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Lokasi Fisik</label>
                            <input type="text" name="location" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200" placeholder="Ruang Server Lt.1">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Retention (Hari)</label>
                            <input type="number" name="retention_days" min="1" max="365" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200" value="30" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi (Opsional)</label>
                        <textarea name="description" rows="3" class="w-full px-4 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200"></textarea>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded text-cyan-500 focus:ring-cyan-200 w-5 h-5">
                        <label for="is_active" class="text-sm font-bold text-slate-700 cursor-pointer">Server Aktif & Siap Merekam</label>
                    </div>

                    <div class="flex justify-end gap-4 pt-4">
                        <a href="{{ route('servers.index') }}" class="px-6 py-2 text-slate-500 hover:bg-slate-100 rounded-xl font-medium">Batal</a>
                        <button type="submit" class="px-6 py-2 bg-cyan-500 text-white rounded-xl hover:bg-cyan-600 font-medium shadow-lg">Simpan Server</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</x-app-layout>