<x-app-layout>
    <div x-data="intelligentApp()" class="pt-20 p-6 md:p-8 min-h-screen bg-slate-50 relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute -top-20 -right-20 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl mix-blend-multiply pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl mix-blend-multiply pointer-events-none"></div>

        <div class="max-w-6xl mx-auto relative z-10">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center gap-3">
                    <i class="fas fa-brain text-purple-600 bg-purple-100 p-2 rounded-xl"></i>
                    Intelligent Testing
                </h1>
                <p class="text-slate-500 mt-2 font-medium">Real-time People Flow Counting LAPI Viewer (UNV)</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Config Panel -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-satellite-dish text-slate-400"></i> Server Receiver Status
                        </h2>
                        
                        <div class="space-y-4">
                            <div class="bg-slate-50 border border-slate-200 p-4 rounded-xl text-center">
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">Status Polling</p>
                                <p class="text-lg font-black text-purple-600 mt-1" x-text="isPolling ? 'Aktif Menunggu Data' : 'Berhenti'"></p>
                            </div>
                            
                            <button type="button" @click="toggleConnection()" 
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white transition-all duration-200"
                                :class="isPolling ? 'bg-red-500 hover:bg-red-600' : 'bg-purple-600 hover:bg-purple-700'">
                                <i class="fas mr-2 mt-0.5" :class="isPolling ? 'fa-stop-circle' : 'fa-play-circle'"></i>
                                <span x-text="isPolling ? 'Stop Menunggu' : 'Mulai Menunggu Data'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-indigo-500 mt-0.5"></i>
                            <div class="text-sm text-indigo-800">
                                <p class="font-bold mb-1">Cara Kerja (Push Webhook)</p>
                                <p class="text-xs leading-relaxed">Kamera UNV yang sudah dikonfigurasi akan secara otomatis mengirimkan data JSON/LAPI ke server kita setiap kali mendeteksi orang. Server akan menampung datanya dan halaman ini akan menampilkannya.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Panel -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Alert/Status -->
                    <template x-if="errorMsg">
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm animate-pulse">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700 font-medium" x-text="errorMsg"></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="isPolling && !errorMsg">
                        <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-xl shadow-sm flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-circle-notch fa-spin text-emerald-500 mr-3"></i>
                                <p class="text-sm text-emerald-700 font-medium">Terhubung & Membaca Data API...</p>
                            </div>
                            <span class="text-xs font-mono text-emerald-600 bg-emerald-100 px-2 py-1 rounded-md" x-text="'Last Check: ' + lastUpdate"></span>
                        </div>
                    </template>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4" x-show="hasData" x-transition>
                        <!-- Entered -->
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 relative overflow-hidden group">
                            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition duration-300">
                                <i class="fas fa-sign-in-alt text-8xl text-emerald-600"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-500 uppercase tracking-wide">Orang Masuk</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-5xl font-black text-slate-800" x-text="stats.entered">0</span>
                                <span class="text-sm text-emerald-500 font-bold"><i class="fas fa-arrow-up"></i> In</span>
                            </div>
                        </div>

                        <!-- Left -->
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 relative overflow-hidden group">
                            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition duration-300">
                                <i class="fas fa-sign-out-alt text-8xl text-rose-600"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-500 uppercase tracking-wide">Orang Keluar</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-5xl font-black text-slate-800" x-text="stats.left">0</span>
                                <span class="text-sm text-rose-500 font-bold"><i class="fas fa-arrow-down"></i> Out</span>
                            </div>
                        </div>

                        <!-- Total (Entered - Left) -->
                        <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-2xl p-6 shadow-md relative overflow-hidden group">
                            <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:opacity-20 transition duration-300">
                                <i class="fas fa-users text-8xl text-white"></i>
                            </div>
                            <p class="text-sm font-bold text-purple-100 uppercase tracking-wide">Estimasi Di Dalam</p>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span class="text-5xl font-black text-white" x-text="stats.total">0</span>
                                <span class="text-sm text-purple-200 font-medium">Orang</span>
                            </div>
                        </div>
                    </div>

                    <!-- RAW JSON Dump (For Debugging) -->
                    <div class="bg-slate-900 rounded-2xl shadow-xl border border-slate-700 overflow-hidden" x-show="hasData" x-transition>
                        <div class="bg-slate-800 px-4 py-3 flex items-center justify-between border-b border-slate-700">
                            <h3 class="text-xs font-bold text-slate-300 flex items-center gap-2 uppercase tracking-wider">
                                <i class="fas fa-code text-cyan-400"></i> Raw API Response (UNV LAPI)
                            </h3>
                            <div class="flex items-center gap-3">
                                <button type="button" @click="copyRawData()" class="text-xs text-slate-400 hover:text-cyan-400 transition-colors flex items-center gap-1 bg-slate-700 hover:bg-slate-600 px-2 py-1 rounded-md">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                                <div class="flex gap-1.5 border-l border-slate-600 pl-3">
                                    <div class="w-2.5 h-2.5 rounded-full bg-rose-500"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 overflow-y-auto overflow-x-hidden max-h-96 custom-scrollbar">
                            <pre class="text-xs font-mono text-cyan-300 leading-relaxed whitespace-pre-wrap break-all" x-text="typeof rawData === 'string' ? rawData : JSON.stringify(rawData, null, 2)"></pre>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div x-show="!hasData && !isPolling" class="flex flex-col items-center justify-center py-20 text-slate-400">
                        <i class="fas fa-satellite-dish text-6xl mb-4 opacity-20"></i>
                        <p class="text-lg font-medium">Belum ada data masuk</p>
                        <p class="text-sm mt-1">Masukkan konfigurasi dan mulai polling.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function intelligentApp() {
            return {
                isPolling: false,
                pollingTimer: null,
                errorMsg: null,
                lastUpdate: '--:--:--',
                
                hasData: false,
                rawData: null,
                stats: {
                    entered: 0,
                    left: 0,
                    total: 0
                },

                toggleConnection() {
                    if (this.isPolling) {
                        this.stopPolling();
                    } else {
                        this.startPolling();
                    }
                },

                startPolling() {
                    this.isPolling = true;
                    this.errorMsg = null;
                    this.fetchData(); // Fetch immediately
                    
                    // Then interval every 3 seconds
                    this.pollingTimer = setInterval(() => {
                        this.fetchData();
                    }, 3000);
                },

                stopPolling() {
                    this.isPolling = false;
                    clearInterval(this.pollingTimer);
                },

                copyRawData() {
                    const textToCopy = typeof this.rawData === 'string' ? this.rawData : JSON.stringify(this.rawData, null, 2);
                    navigator.clipboard.writeText(textToCopy).then(() => {
                        alert('Raw Response disalin ke clipboard!');
                    }).catch(err => {
                        alert('Gagal menyalin text: ' + err);
                    });
                },

                async fetchData() {
                    try {
                        const response = await fetch(`{{ route('intelligent.data') }}`);
                        const data = await response.json();

                        if (response.status === 202) {
                            // Menunggu data dari kamera (masih kosong)
                            return;
                        }

                        if (!response.ok) {
                            throw new Error(data.error || 'Terjadi kesalahan saat mengecek cache');
                        }

                        this.errorMsg = null;
                        this.hasData = true;
                        this.rawData = data.raw_data;
                        
                        this.parseUnvData(data.raw_data);
                        
                        const now = new Date();
                        this.lastUpdate = now.getHours().toString().padStart(2, '0') + ':' + 
                                          now.getMinutes().toString().padStart(2, '0') + ':' + 
                                          now.getSeconds().toString().padStart(2, '0');

                    } catch (error) {
                        this.errorMsg = error.message;
                        this.hasData = false;
                        // Kita jangan stop intervalnya siapa tahu hanya RTO 1x
                    }
                },
                
                parseUnvData(raw) {
                    // Coba tebak struktur standar LAPI People Counting Report
                    // Jika tidak cocok, angka akan tetap 0 dan user bisa cek Raw JSON.
                    try {
                        if (raw?.Response?.Data?.PeopleCounting) {
                            const info = raw.Response.Data.PeopleCounting;
                            this.stats.entered = info.EnterNum || info.InNum || 0;
                            this.stats.left = info.LeaveNum || info.OutNum || 0;
                            
                            // Bisa jadi kamera mengirimkan Total, atau kita hitung manual (In - Out)
                            this.stats.total = (this.stats.entered - this.stats.left);
                            if(this.stats.total < 0) this.stats.total = 0;
                        }
                    } catch(e) {
                        console.log("Failed to parse UNV structure", e);
                    }
                }
            }
        }
    </script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
    </style>
</x-app-layout>
