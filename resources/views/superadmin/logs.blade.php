<x-app-layout>
    <main id="main-content" class="pt-20 p-6 md:p-8 min-h-screen bg-slate-50">
        
        <!-- Breadcrumb -->
        <div id="breadcrumb" class="mb-6">
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-cyan-500"></i>
                <span class="text-slate-400">/</span>
                <span class="text-slate-500">Super Admin</span>
                <span class="text-slate-400">/</span>
                <span class="text-slate-800 font-medium">Log Aktivitas</span>
            </div>
        </div>

        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-slate-800 mb-2">Log Aktivitas Pengguna</h2>
            <p class="text-slate-500">Pantau riwayat login dan siaran CCTV yang diakses oleh pengguna secara real-time.</p>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Activities -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:shadow-md transition-all duration-300">
                <div class="space-y-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Aktivitas</span>
                    <h3 class="text-3xl font-bold text-slate-800 font-mono">{{ $logs->total() }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-500 flex items-center justify-center text-lg shadow-inner">
                    <i class="fas fa-history"></i>
                </div>
            </div>

            <!-- Total Logins -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:shadow-md transition-all duration-300">
                <div class="space-y-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Sesi Masuk (Login)</span>
                    <h3 class="text-3xl font-bold text-slate-800 font-mono">{{ $totalLogins }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-lg shadow-inner">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
            </div>

            <!-- Total CCTV Views -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between hover:shadow-md transition-all duration-300">
                <div class="space-y-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">CCTV Dilihat</span>
                    <h3 class="text-3xl font-bold text-slate-800 font-mono">{{ $totalViews }}</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-lg shadow-inner">
                    <i class="fas fa-video"></i>
                </div>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm mb-6">
            <form method="GET" action="{{ route('superadmin.logs') }}" id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <!-- User Search -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Cari Pengguna</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3.5 top-1/2 transform -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="search_user" value="{{ request('search_user') }}" placeholder="Nama atau email..."
                               class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition-all placeholder-slate-400 text-slate-700">
                    </div>
                </div>

                <!-- Activity Type Filter -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tipe Aktivitas</label>
                    <select name="activity_type" class="w-full px-3 py-2 text-xs bg-slate-50 rounded-xl border border-slate-200 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 cursor-pointer text-slate-700">
                        <option value="">Semua Tipe</option>
                        <option value="login" {{ request('activity_type') === 'login' ? 'selected' : '' }}>Login</option>
                        <option value="logout" {{ request('activity_type') === 'logout' ? 'selected' : '' }}>Logout</option>
                        <option value="cctv_view" {{ request('activity_type') === 'cctv_view' ? 'selected' : '' }}>CCTV View</option>
                        <option value="cctv_add" {{ request('activity_type') === 'cctv_add' ? 'selected' : '' }}>Tambah CCTV</option>
                        <option value="cctv_edit" {{ request('activity_type') === 'cctv_edit' ? 'selected' : '' }}>Edit CCTV</option>
                        <option value="cctv_delete" {{ request('activity_type') === 'cctv_delete' ? 'selected' : '' }}>Hapus CCTV</option>
                        <option value="user_add" {{ request('activity_type') === 'user_add' ? 'selected' : '' }}>Tambah User</option>
                        <option value="user_edit" {{ request('activity_type') === 'user_edit' ? 'selected' : '' }}>Edit User</option>
                        <option value="user_delete" {{ request('activity_type') === 'user_delete' ? 'selected' : '' }}>Hapus User</option>
                        <option value="camera_down" {{ request('activity_type') === 'camera_down' ? 'selected' : '' }}>Kamera Down</option>
                        <option value="camera_up" {{ request('activity_type') === 'camera_up' ? 'selected' : '' }}>Kamera Up</option>
                    </select>
                </div>

                <!-- Start Date -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                           class="w-full px-3 py-2 text-xs bg-slate-50 rounded-xl border border-slate-200 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-slate-700">
                </div>

                <!-- End Date & Form buttons -->
                <div class="grid grid-cols-3 gap-2">
                    <div class="col-span-2 flex flex-col gap-1.5">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal Akhir</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}"
                               class="w-full px-3 py-2 text-xs bg-slate-50 rounded-xl border border-slate-200 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 text-slate-700">
                    </div>
                    
                    <!-- Action buttons -->
                    <div class="flex items-center gap-1.5 mt-auto">
                        <button type="submit" class="flex-1 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs shadow-md transition-all active:scale-95 flex items-center justify-center" title="Terapkan Filter">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="{{ route('superadmin.logs') }}" class="flex-1 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-600 font-bold text-xs transition-all active:scale-95 flex items-center justify-center" title="Reset Filter">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Logs Table Content -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 text-[10px] font-bold uppercase tracking-wider">
                            <th class="py-4 px-6">Pengguna</th>
                            <th class="py-4 px-6">Aktivitas</th>
                            <th class="py-4 px-6">Detail Informasi</th>
                            <th class="py-4 px-6">IP Address</th>
                            <th class="py-4 px-6">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <!-- User -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-cyan-600 uppercase text-xs">
                                            {{ substr($log->user->name ?? '?', 0, 2) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">{{ $log->user->name ?? 'Unknown User' }}</p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $log->user->email ?? '-' }} &bull; <span class="capitalize text-cyan-600 font-semibold">{{ $log->user->role ?? '-' }}</span></p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Activity Type -->
                                <td class="py-4 px-6">
                                    @switch($log->activity_type)
                                        @case('login')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 font-bold text-[9px] uppercase border border-emerald-100 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Login
                                            </span>
                                            @break
                                        @case('logout')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-bold text-[9px] uppercase border border-slate-200 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                                Logout
                                            </span>
                                            @break
                                        @case('cctv_view')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 text-blue-600 font-bold text-[9px] uppercase border border-blue-100 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                CCTV View
                                            </span>
                                            @break
                                        @case('cctv_add')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-50 text-green-700 font-bold text-[9px] uppercase border border-green-200 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                CCTV Add
                                            </span>
                                            @break
                                        @case('cctv_edit')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 font-bold text-[9px] uppercase border border-amber-200 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                CCTV Edit
                                            </span>
                                            @break
                                        @case('cctv_delete')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-50 text-red-600 font-bold text-[9px] uppercase border border-red-100 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                                CCTV Delete
                                            </span>
                                            @break
                                        @case('camera_down')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-50 text-rose-600 font-bold text-[9px] uppercase border border-rose-100 shadow-sm animate-pulse">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Camera Down
                                            </span>
                                            @break
                                        @case('camera_up')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-teal-50 text-teal-600 font-bold text-[9px] uppercase border border-teal-100 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                                Camera Up
                                            </span>
                                            @break
                                        @case('user_add')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-teal-50 text-teal-700 font-bold text-[9px] uppercase border border-teal-200 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                                User Add
                                            </span>
                                            @break
                                        @case('user_edit')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 font-bold text-[9px] uppercase border border-indigo-200 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                                User Edit
                                            </span>
                                            @break
                                        @case('user_delete')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 font-bold text-[9px] uppercase border border-rose-200 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                User Delete
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-50 text-slate-600 font-bold text-[9px] uppercase border border-slate-100 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                {{ $log->activity_type }}
                                            </span>
                                    @endswitch
                                </td>

                                <!-- Details -->
                                <td class="py-4 px-6">
                                    @php
                                        $details = json_decode($log->details, true) ?? [];
                                    @endphp
                                    @switch($log->activity_type)
                                        @case('login')
                                        @case('logout')
                                            @php
                                                $userAgent = $details['user_agent'] ?? '-';
                                                $browser = 'Browser';
                                                if (str_contains($userAgent, 'Firefox')) $browser = 'Firefox';
                                                elseif (str_contains($userAgent, 'Chrome')) $browser = 'Chrome';
                                                elseif (str_contains($userAgent, 'Safari')) $browser = 'Safari';
                                                elseif (str_contains($userAgent, 'Edge')) $browser = 'Edge';
                                            @endphp
                                            <div class="text-[10px] text-slate-500 flex items-center gap-1.5">
                                                <i class="fas fa-desktop text-slate-400"></i>
                                                <span class="truncate max-w-xs" title="{{ $userAgent }}">{{ $browser }} &bull; {{ $userAgent }}</span>
                                            </div>
                                            @break

                                        @case('cctv_view')
                                            @if($log->cctv)
                                                <div>
                                                    <p class="font-bold text-slate-800 flex items-center gap-1.5">
                                                        <i class="fas fa-video text-slate-400 text-[10px]"></i>
                                                        {{ $log->cctv->nama_cctv }}
                                                    </p>
                                                    <p class="text-[10px] text-slate-400 mt-0.5">
                                                        {{ $log->cctv->building->nama_gedung ?? '-' }} &bull; {{ $log->cctv->building->fakultas ?? '-' }}
                                                    </p>
                                                </div>
                                            @else
                                                <span class="text-slate-400 italic">Kamera Telah Dihapus</span>
                                            @endif
                                            @break

                                        @case('cctv_add')
                                            <div>
                                                <p class="font-bold text-slate-800 flex items-center gap-1.5">
                                                    <i class="fas fa-plus-circle text-green-500 text-[10px]"></i>
                                                    {{ $details['nama_cctv'] ?? 'Kamera Baru' }}
                                                </p>
                                                <p class="text-[10px] text-slate-400 mt-0.5">
                                                    Kode: {{ $details['kode_cctv'] ?? '-' }} &bull; IP: {{ $details['ip'] ?? '-' }}
                                                </p>
                                            </div>
                                            @break

                                        @case('cctv_edit')
                                            <div>
                                                <p class="font-bold text-slate-800 flex items-center gap-1.5">
                                                    <i class="fas fa-edit text-amber-500 text-[10px]"></i>
                                                    {{ $details['nama_cctv'] ?? 'Kamera' }}
                                                </p>
                                                @if(!empty($details['changes']))
                                                    <div class="text-[9px] text-slate-500 bg-slate-50 p-1.5 rounded border border-slate-100 mt-1 font-mono">
                                                        <span class="font-bold text-slate-400 font-sans">Perubahan:</span>
                                                        <ul class="list-disc pl-3.5 space-y-0.5 mt-0.5">
                                                            @foreach($details['changes'] as $key => $val)
                                                                <li>
                                                                    <span class="text-slate-600 font-semibold">{{ $key }}:</span> 
                                                                    <span class="text-slate-800">"{{ is_array($val) ? json_encode($val) : $val }}"</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                            @break

                                        @case('cctv_delete')
                                            <div>
                                                <p class="font-bold text-red-600 flex items-center gap-1.5">
                                                    <i class="fas fa-trash-alt text-red-500 text-[10px]"></i>
                                                    {{ $details['nama_cctv'] ?? 'Kamera' }}
                                                </p>
                                                <p class="text-[10px] text-slate-400 mt-0.5">
                                                    Kode: {{ $details['kode_cctv'] ?? '-' }}
                                                </p>
                                            </div>
                                            @break

                                        @case('camera_down')
                                            <div>
                                                <p class="font-bold text-rose-600 flex items-center gap-1.5">
                                                    <i class="fas fa-chevron-circle-down text-rose-500 text-[10px]"></i>
                                                    {{ $details['nama_cctv'] ?? 'Kamera' }}
                                                </p>
                                                <p class="text-[10px] text-slate-400 mt-0.5">
                                                    IP: {{ $details['ip'] ?? '-' }} &bull; <span class="text-red-500 font-bold">Koneksi Putus (Offline)</span>
                                                </p>
                                            </div>
                                            @break

                                        @case('camera_up')
                                            <div>
                                                <p class="font-bold text-teal-600 flex items-center gap-1.5">
                                                    <i class="fas fa-chevron-circle-up text-teal-500 text-[10px]"></i>
                                                    {{ $details['nama_cctv'] ?? 'Kamera' }}
                                                </p>
                                                <p class="text-[10px] text-slate-400 mt-0.5">
                                                    IP: {{ $details['ip'] ?? '-' }} &bull; <span class="text-green-600 font-bold">Terhubung Kembali (Online)</span>
                                                </p>
                                            </div>
                                            @break
                                        @case('user_add')
                                            <div>
                                                <p class="font-bold text-slate-800 flex items-center gap-1.5">
                                                    <i class="fas fa-user-plus text-teal-500 text-[10px]"></i>
                                                    {{ $details['name'] ?? 'User Baru' }}
                                                </p>
                                                <p class="text-[10px] text-slate-400 mt-0.5">
                                                    Email: {{ $details['email'] ?? '-' }} &bull; Role: <span class="uppercase font-bold">{{ $details['role'] ?? '-' }}</span>
                                                </p>
                                            </div>
                                            @break
                                        @case('user_edit')
                                            <div>
                                                <p class="font-bold text-slate-800 flex items-center gap-1.5">
                                                    <i class="fas fa-user-cog text-indigo-500 text-[10px]"></i>
                                                    {{ $details['name'] ?? 'User' }}
                                                </p>
                                                <p class="text-[10px] text-slate-400 mt-0.5">
                                                    Email: {{ $details['email'] ?? '-' }}
                                                </p>
                                                @if(!empty($details['changes']))
                                                    <div class="text-[9px] text-slate-500 bg-slate-50 p-1.5 rounded border border-slate-100 mt-1 font-mono">
                                                        <span class="font-bold text-slate-400 font-sans">Perubahan:</span>
                                                        <ul class="list-disc pl-3.5 space-y-0.5 mt-0.5">
                                                            @foreach($details['changes'] as $key => $val)
                                                                <li>
                                                                    <span class="text-slate-600 font-semibold">{{ $key }}:</span> 
                                                                    <span class="text-slate-800">"{{ is_array($val) ? json_encode($val) : $val }}"</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                            @break
                                        @case('user_delete')
                                            <div>
                                                <p class="font-bold text-red-600 flex items-center gap-1.5">
                                                    <i class="fas fa-user-minus text-red-500 text-[10px]"></i>
                                                    {{ $details['name'] ?? 'User' }}
                                                </p>
                                                <p class="text-[10px] text-slate-400 mt-0.5">
                                                    Email: {{ $details['email'] ?? '-' }}
                                                </p>
                                            </div>
                                            @break

                                        @default
                                            <span class="text-slate-400 italic">No details available</span>
                                    @endswitch
                                </td>

                                <!-- IP Address -->
                                <td class="py-4 px-6 font-mono text-[10px] text-slate-500">
                                    {{ $log->ip_address ?? '-' }}
                                </td>

                                <!-- Waktu -->
                                <td class="py-4 px-6">
                                    <p class="text-slate-800 font-semibold">{{ $log->created_at->diffForHumans() }}</p>
                                    <p class="text-[9px] text-slate-400 mt-0.5">{{ $log->created_at->format('d M Y H:i:s') }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 italic bg-white">
                                    Belum ada log aktivitas yang tercatat untuk pencarian ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination block -->
            @if($logs->hasPages())
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
        
    </main>
</x-app-layout>
