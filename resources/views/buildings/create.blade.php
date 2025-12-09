<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <a href="{{ route('building.index') }}" class="text-slate-500 hover:text-cyan-600">Master Gedung</a>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Tambah Baru</span>
            </div>
        </div>

        <div class="max-w-2xl mx-auto">
            <div class="glass-effect rounded-2xl p-8 border border-cyan-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-32 h-32 bg-gradient-to-br from-cyan-400/20 to-blue-400/20 rounded-full blur-2xl"></div>

                <h2 class="text-2xl font-bold text-slate-800 mb-6 relative z-10">Input Data Gedung</h2>

                <form action="{{ route('building.store') }}" method="POST" class="space-y-6 relative z-10">
                    @csrf

                    <div>
                        <label for="kode_gedung" class="block text-sm font-semibold text-slate-700 mb-2">Kode Gedung</label>
                        <input type="text" name="kode_gedung" id="kode_gedung" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 outline-none transition-all text-slate-700"
                               placeholder="Contoh: G-RECT" value="{{ old('kode_gedung') }}" required>
                        @error('kode_gedung')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nama_gedung" class="block text-sm font-semibold text-slate-700 mb-2">Nama Gedung</label>
                        <input type="text" name="nama_gedung" id="nama_gedung" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 outline-none transition-all text-slate-700"
                               placeholder="Contoh: Gedung Rektorat" value="{{ old('nama_gedung') }}" required>
                        @error('nama_gedung')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="fakultas" class="block text-sm font-semibold text-slate-700 mb-2">Fakultas / Unit</label>
                        <select name="fakultas" id="fakultas" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 outline-none transition-all text-slate-700">
                            <option value="" disabled selected>Pilih Fakultas/Unit</option>
                            <option value="Fakultas Hukum">Fakultas Hukum</option>
                            <option value="Fakultas Ekonomi & Bisnis">Fakultas Ekonomi & Bisnis</option>
                            <option value="Fakultas Kedokteran">Fakultas Kedokteran</option>
                            <option value="Fakultas MIPA">Fakultas MIPA</option>
                            <option value="Fakultas Pertanian">Fakultas Pertanian</option>
                            <option value="Fakultas Kedokteran Gigi">Fakultas Kedokteran Gigi</option>
                            <option value="Fakultas Ilmu Sosial & Politik">Fakultas Ilmu Sosial & Politik</option>
                            <option value="Fakultas Ilmu Budaya">Fakultas Ilmu Budaya</option>
                            <option value="Fakultas Psikologi">Fakultas Psikologi</option>
                            <option value="Fakultas Peternakan">Fakultas Peternakan</option>
                            <option value="Fakultas Ilmu Komunikasi">Fakultas Ilmu Komunikasi</option>
                            <option value="Fakultas Keperawatan">Fakultas Keperawatan</option>
                            <option value="Fakultas Perikanan & Ilmu Kelautan">Fakultas Perikanan & Ilmu Kelautan</option>
                            <option value="Fakultas Teknologi Industri Pertanian">Fakultas Teknologi Industri Pertanian</option>
                            <option value="Fakultas Farmasi">Fakultas Farmasi</option>
                            <option value="Fakultas Teknik Geologi">Fakultas Teknik Geologi</option>
                            <option value="Pascasarjana">Pascasarjana</option>
                            <option value="Rektorat">Rektorat</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        @error('fakultas')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-4">
                        <a href="{{ route('building.index') }}" class="px-5 py-2.5 rounded-xl text-slate-500 hover:bg-slate-100 transition-colors font-medium">Batal</a>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all duration-300">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</x-app-layout>