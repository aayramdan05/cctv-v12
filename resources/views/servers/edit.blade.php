<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8">
        
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <a href="{{ route('servers.index') }}" class="text-slate-500 hover:text-cyan-600">Master Server</a>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Edit Node</span>
            </div>
        </div>

        <div class="max-w-3xl mx-auto">
            <div class="glass-effect rounded-2xl p-8 border border-cyan-100 relative overflow-hidden">
                
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-gradient-to-br from-cyan-400/10 to-blue-400/10 rounded-full blur-3xl"></div>

                <h2 class="text-2xl font-bold text-slate-800 mb-6 relative z-10">Edit Server: {{ $server->name }}</h2>
                
                <form action="{{ route('servers.update', $server->id) }}" method="POST" class="space-y-6 relative z-10">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Server (Node)</label>
                            <input type="text" name="name" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 focus:border-cyan-400 transition-all" 
                                   required value="{{ old('name', $server->name) }}">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">IP Address</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3 text-slate-400"><i class="fas fa-network-wired"></i></span>
                                <input type="text" name="ip_address" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 focus:border-cyan-400 transition-all font-mono" 
                                       required value="{{ old('ip_address', $server->ip_address) }}">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Lokasi Fisik</label>
                        <input type="text" name="location" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 focus:border-cyan-400 transition-all" 
                               value="{{ old('location', $server->location) }}">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi (Opsional)</label>
                        <textarea name="description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-cyan-200 focus:border-cyan-400 transition-all">{{ old('description', $server->description) }}</textarea>
                    </div>

                    <!-- Status Toggle -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-slate-700 text-sm">Status Aktif</h4>
                            <p class="text-xs text-slate-500">Server aktif dapat dipilih saat input CCTV.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ $server->is_active ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-cyan-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-500"></div>
                        </label>
                    </div>

                    <div class="flex justify-end gap-4 pt-4 border-t border-slate-100">
                        <a href="{{ route('servers.index') }}" class="px-6 py-2.5 text-slate-500 hover:bg-slate-100 rounded-xl font-medium transition-colors">Batal</a>
                        <button type="submit" class="px-8 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-500 text-white rounded-xl font-bold shadow-lg shadow-cyan-500/30 hover:shadow-cyan-500/50 hover:-translate-y-0.5 transition-all">
                            Update Server
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</x-app-layout>