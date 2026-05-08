<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-500">Master Data</span>
                <span class="text-slate-400">/</span>
                <a href="{{ route('faculties.index') }}" class="text-slate-500 hover:text-cyan-500 transition-colors">Fakultas</a>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Edit Fakultas</span>
            </div>
        </div>

        <div class="mb-8">
            <h2 class="text-3xl font-bold text-slate-800 mb-2">Edit Fakultas</h2>
            <p class="text-slate-500">Ubah data Fakultas atau Unit Kerja.</p>
        </div>

        <div class="max-w-2xl">
            <div class="glass-effect rounded-2xl p-8 border border-cyan-100">
                <form action="{{ route('faculties.update', $faculty->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Nama Fakultas / Unit Kerja <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $faculty->name) }}" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-200 outline-none transition-all text-slate-700"
                               placeholder="Contoh: Fakultas Kedokteran" required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
                        <a href="{{ route('faculties.index') }}" 
                           class="px-6 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-medium hover:bg-slate-200 transition-colors">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-medium shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 transition-all duration-300">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </main>
</x-app-layout>
