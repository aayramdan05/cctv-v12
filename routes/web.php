<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; // <--- WAJIB ADA
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use App\Models\User;

// Controller Imports
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\CctvController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\ServerController;     
use App\Http\Controllers\PlaybackController;   
use App\Http\Controllers\FfmpegStatusController;
use App\Http\Controllers\Api\TestCameraController;
use App\Models\Cctv; 
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\StreamAuthController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\Auth\PAuSIDController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// SSO Unpad (PAUS) Authentication
Route::get('/auth/paus', function () {
    return Socialite::driver('paus')->redirect();
})->name('auth.paus');

Route::get('/auth/paus/callback', [PAuSIDController::class, 'handleProviderCallback'])->name('auth.paus.callback');
// Route::get('/auth/paus/callback', function () {
//     try {
//         $pausUser = Socialite::driver('paus')->user();
//         $pausId = $pausUser->getId();
//         $email = $pausUser->getEmail();

//         // 1. Cari user berdasarkan paus_id terlebih dahulu
//         $user = null;
//         if ($pausId) {
//             $user = User::where('paus_id', $pausId)->first();
//         }

//         // Jika tidak ditemukan berdasarkan paus_id, cari berdasarkan email (jika ada)
//         if (!$user && $email) {
//             $user = User::where('email', $email)->first();
//         }

//         if ($user) {
//             // Update data PAUS jika sudah ada
//             $user->update([
//                 'paus_id' => $pausId,
//                 'paus_username' => $pausUser->getNickname(),
//                 'name' => $pausUser->getName(),
//             ]);
//         } else {
//             // 2. Buat user baru jika belum terdaftar
//             $user = User::create([
//                 'name' => $pausUser->getName(),
//                 'email' => $email,
//                 'paus_id' => $pausId,
//                 'paus_username' => $pausUser->getNickname(),
//                 'password' => bcrypt(Str::random(24)),
//                 'role' => 'user', // Default: View Only (User Biasa)
//             ]);
//         }

//         // 3. Login-kan user
//         Auth::login($user);

//         return redirect()->intended('/dashboard');

//     } catch (\Exception $e) {
//         return redirect('/login')->with('error', 'Gagal login menggunakan SSO Unpad: ' . $e->getMessage());
//     }
// })->name('auth.paus.callback');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pending-approval', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    if (auth()->user()->status !== 'pending') {
        return redirect()->route('monitoring.index');
    }
    return view('auth.pending-approval');
})->name('pending-approval')->middleware(['auth']);

Route::get('/auth-stream-verify', [StreamAuthController::class, 'verify'])->name('stream.verify');

// --- GROUP 1: USER, OPERATOR, ADMIN (Akses Umum) ---
Route::middleware(['auth', 'verified', 'dashboard.access'])->group(function () {
    
    // Dashboard & Monitoring
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/presets', [MonitoringController::class, 'getPresets'])->name('monitoring.presets');
    Route::post('/monitoring/presets', [MonitoringController::class, 'savePreset'])->name('monitoring.presets.store');
    Route::delete('/monitoring/presets/{preset}', [MonitoringController::class, 'deletePreset'])->name('monitoring.presets.destroy');
    
    // Data Timeline & Playback
    Route::get('/playback', [PlaybackController::class, 'index'])->name('playback.index');
    Route::get('/playback/data', [PlaybackController::class, 'getRecordings'])->name('playback.data');
    Route::post('/playback/export', [PlaybackController::class, 'exportRecordings'])->name('playback.export');
    Route::get('/playback/download/{filename}', [PlaybackController::class, 'downloadExport'])->name('playback.download');
    Route::get('/monitoring/timeline/{cctv}', [MonitoringController::class, 'getTimelineJson'])->name('monitoring.timeline');
    Route::post('/log/cctv-view/{cctv}', [MonitoringController::class, 'logCctvView'])->name('monitoring.logView');
    // Tools Streaming
    Route::get('/stream/{cctv}', [StreamController::class, 'play'])->name('stream.play');
    Route::post('/cctv/test-connection', [TestCameraController::class, 'test'])->name('cctv.test');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Map Monitoring
    Route::get('/map-monitoring', [MapController::class, 'index'])->name('map.index');
    Route::get('/api/map/cctvs', [MapController::class, 'getCctvData'])->name('api.map.cctvs');
    Route::post('/api/map/update-coords', [MapController::class, 'updateCoordinates'])->name('api.map.update-coords');
    Route::get('/api/map/proxy-stream', [MapController::class, 'streamProxy'])->name('api.map.proxy');
});

// --- GROUP 2: INFRASTRUCTURE & REPORTS (Dinamis Berbasis Permission) ---
Route::middleware(['auth', 'permission:server_manage'])->group(function () {
    Route::resource('servers', ServerController::class);
    Route::get('/ffmpeg-monitor', [FfmpegStatusController::class, 'index'])->name('ffmpeg.monitor');
    Route::get('/ffmpeg-monitor/nginx-config', [FfmpegStatusController::class, 'getNginxConfig'])->name('ffmpeg.nginx');
    Route::get('/ffmpeg-monitor/backup-db', [FfmpegStatusController::class, 'backupDatabase'])->name('ffmpeg.backup');
});

Route::middleware(['auth', 'permission:api_key_manage'])->group(function () {
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api.index');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api.store');
    Route::delete('/api-keys/{id}', [ApiKeyController::class, 'destroy'])->name('api.destroy');
});

Route::middleware(['auth', 'permission:report_view'])->group(function () {
    Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/csv', [App\Http\Controllers\ReportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('/reports/export/pdf', [App\Http\Controllers\ReportController::class, 'exportPdf'])->name('reports.export.pdf');
});

Route::middleware(['auth', 'permission:notification_manage'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
});

// --- GROUP 3: MASTER DATA (Dinamis Berbasis Permission) ---
Route::middleware(['auth', 'permission:building_manage'])->group(function () {
    Route::resource('building', BuildingController::class);
    Route::resource('faculties', \App\Http\Controllers\FacultyController::class)->except(['show']);
});

// --- GROUP 4: MANAJEMEN CCTV & USER (Dinamis Berbasis Permission) ---
Route::middleware(['auth', 'permission:cctv_import'])->group(function () {
    Route::get('cctv/migration', [\App\Http\Controllers\CctvMigrationController::class, 'index'])->name('cctv.migration');
    Route::get('cctv/migration/template', [\App\Http\Controllers\CctvMigrationController::class, 'downloadTemplate'])->name('cctv.migration.template');
    Route::post('cctv/migration/import', [\App\Http\Controllers\CctvMigrationController::class, 'import'])->name('cctv.migration.import');
});

Route::middleware(['auth', 'permission:cctv_bulk_move'])->group(function () {
    Route::post('cctv/bulk-move', [CctvController::class, 'bulkMove'])->name('cctv.bulkMove');
});

Route::middleware(['auth', 'permission:cctv_view'])->group(function () {
    Route::resource('cctv', CctvController::class);
});

Route::middleware(['auth', 'permission:user_view'])->group(function () {
    Route::resource('users', UserController::class);
});


Route::get('/cek-status-user', function (Request $request) {
    // Cek apakah user login
    if (auth()->check()) {
        // Jika OK, return 200. Body tidak penting.
        return response('OK', 200);
    }

    // Jika Gagal, JANGAN REDIRECT. Return 401.
    return response('Unauthorized', 401);
})->middleware(['web']);


// ==========================================================================
// ROUTE SATPAM NGINX (AUTH REQUEST)
// ==========================================================================
// Route ini dipanggil internal oleh Nginx sebelum mengizinkan akses video
Route::get('/auth-video', function (Request $request) {
    
    // 1. CEK LOGIN SESSION (Wajib Login Web)
    if (!Auth::check()) {
        return response('Unauthorized', 401);
    }
    
    // 2. CEK HAK AKSES KAMERA (RBAC)
    // Ambil sumber dari parameter 'src' (untuk Live) atau dari URI asli (untuk Recording)
    $src = $request->query('src') ?: $request->header('X-Original-Uri'); 
    
    if ($src) {
        // Mendukung format 'camera_12' (Live) ATAU 'cam_12' (Recording)
        if (preg_match('/(?:camera_|cam_)(\d+)/', $src, $matches)) {
            $id = $matches[1];
            
            // Validasi ID dengan RBAC
            $exists = Cctv::accessibleByAuth()->where('id', $id)->exists();
            
            if (!$exists) {
                return response('Forbidden: Access Denied to Camera #' . $id, 403);
            }
        } else {
            return response('Forbidden: Invalid camera ID format in ' . $src, 403);
        }
    } else {
        return response('Forbidden: No source/URI provided', 403);
    }

    // Jika Lolos Semua Cek (RBAC valid)
    return response('OK', 200);
});

// API untuk Node menarik konfigurasi (DIPROTEKSI)
Route::get('/api/node-config', function (Request $request) {
    $nodeIp = $request->query('ip');
    $token = $request->query('token');
    
    // 1. Validasi Token (Gunakan APP_KEY sebagai secret sederhana atau string custom)
    $secret = env('SYNC_TOKEN', 'secret_unpad_cctv_2026'); 
    if ($token !== $secret) {
        return response()->json(['error' => 'Unauthorized: Invalid Token'], 401);
    }

    // 2. Validasi IP Pemanggil (Hanya boleh dari IP Node itu sendiri)
    if ($request->ip() !== $nodeIp && $request->ip() !== '127.0.0.1') {
         // return response()->json(['error' => 'Forbidden: IP Mismatch'], 403);
         // Catatan: Jika lewat proxy, mungkin perlu check X-Forwarded-For
    }

    $server = \App\Models\Server::where('ip_address', $nodeIp)->first();
    if (!$server) {
        return response()->json(['error' => 'Server Node tidak terdaftar'], 404);
    }

    $cameras = \App\Models\Cctv::where('server_id', $server->id)->get();
    
    $config = [
        'streams' => [],
        'cameras_list' => []
    ];

    foreach ($cameras as $cam) {
        $fullUrl = $cam->stream_url; 
        
        // Gunakan RTSP asli (Native) tanpa ffmpeg wrapper untuk meringankan go2rtc
        $urlWithSuffix = "{$fullUrl}#rtsp_transport=tcp";
        
        $config['streams']["camera_{$cam->id}"] = [$urlWithSuffix];
        
        $config['cameras_list'][] = [
            'id' => $cam->id,
            'kode_cctv' => $cam->kode_cctv ?? "CAM-{$cam->id}", // Gunakan fallback jika null
            'nama_cctv' => $cam->nama_cctv ?? "Camera {$cam->id}",
            'url' => "rtsp://127.0.0.1:8554/camera_{$cam->id}",
            'ip' => $cam->ip,
            'onvif' => [
                'port' => $cam->onvif_port ?? 80,
                'user' => $cam->onvif_user ?? $cam->rtsp_user,
                'password' => $cam->onvif_password ?: $cam->rtsp_password
            ]
        ];
    }

    return response()->json($config);
});

// API untuk Node melaporkan kejadian (Motion Detection, dll)
Route::get('/api/report-event', function (Request $request) {
    $token = $request->query('token');
    $secret = env('SYNC_TOKEN', 'secret_unpad_cctv_2026'); 
    
    if ($token !== $secret) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $cctvId = $request->query('cctv_id');
    $type = $request->query('type', 'motion');
    
    if (!$cctvId) return response()->json(['error' => 'Missing CCTV ID'], 400);
    
    $cctv = \App\Models\Cctv::find($cctvId);
    if ($cctv && $type === 'onvif_status') {
        $status = $request->query('status');
        $error = $request->query('error');
        
        try {
            $cctv->update([
                'onvif_status' => $status,
                'onvif_error' => $error
            ]);
        } catch (\Exception $e) {
            // Ignore if migration not run
        }
        
        \App\Models\CameraEvent::create([
            'cctv_id' => $cctvId,
            'event_type' => 'onvif',
            'event_time' => now(),
            'metadata' => ['status' => $status, 'error' => $error]
        ]);
        
        return response()->json(['status' => 'Status Updated']);
    }

    \App\Models\CameraEvent::create([
        'cctv_id' => $cctvId,
        'event_type' => $type,
        'event_time' => now(),
        'metadata' => $request->all()
    ]);

    return response()->json(['status' => 'Event Recorded']);
});

require __DIR__.'/auth.php';

// Halaman Riwayat Kejadian (Events) - Dinamis Berbasis Permission
Route::middleware(['auth', 'permission:event_view'])->group(function () {
    Route::get('/events', [App\Http\Controllers\EventController::class, 'index'])->name('events.index');
    Route::post('/events/mark-all-read', [App\Http\Controllers\EventController::class, 'markAllRead'])->name('events.markAllRead');
    Route::post('/events/{id}/read', [App\Http\Controllers\EventController::class, 'markAsRead'])->name('events.read');
    Route::get('/events/export-csv', [App\Http\Controllers\EventController::class, 'exportCsv'])->name('events.exportCsv');
});

// Konfigurasi RBAC - Only Super Admin
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/superadmin/rbac', [App\Http\Controllers\SuperAdminController::class, 'rbacIndex'])->name('superadmin.rbac.index');
    Route::post('/superadmin/rbac', [App\Http\Controllers\SuperAdminController::class, 'updateRbac'])->name('superadmin.rbac.update');
});

// Halaman Aktivitas Log - Dinamis Berbasis Permission
Route::middleware(['auth', 'permission:activity_log_view'])->group(function () {
    Route::get('/superadmin/logs', [App\Http\Controllers\SuperAdminController::class, 'userLogs'])->name('superadmin.logs');
});
