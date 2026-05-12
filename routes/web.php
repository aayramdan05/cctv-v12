<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; // <--- WAJIB ADA
use Illuminate\Support\Facades\Auth;

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
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth-stream-verify', [StreamAuthController::class, 'verify'])->name('stream.verify');

// --- GROUP 1: USER, OPERATOR, ADMIN (Akses Umum) ---
Route::middleware(['auth', 'verified', 'dashboard.access'])->group(function () {
    
    // Dashboard & Monitoring
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    
    // Data Timeline & Playback
    Route::get('/playback', [PlaybackController::class, 'index'])->name('playback.index');
    Route::get('/playback/data', [PlaybackController::class, 'getRecordings'])->name('playback.data');
    Route::post('/playback/export', [PlaybackController::class, 'exportRecordings'])->name('playback.export');
    Route::get('/playback/download/{filename}', [PlaybackController::class, 'downloadExport'])->name('playback.download');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::get('/monitoring/timeline/{cctv}', [MonitoringController::class, 'getTimelineJson'])->name('monitoring.timeline');
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

// --- GROUP 2: SUPER ADMIN (Infrastruktur) ---
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('servers', ServerController::class);
    Route::get('/ffmpeg-monitor', [FfmpegStatusController::class, 'index'])->name('ffmpeg.monitor');
    
    // API Keys Management (Only Admin)
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api.index');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api.store');
    Route::delete('/api-keys/{id}', [ApiKeyController::class, 'destroy'])->name('api.destroy');
});

// --- GROUP 3: ADMIN ONLY (Master Data) ---
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('building', BuildingController::class);
    Route::resource('faculties', \App\Http\Controllers\FacultyController::class)->except(['show']);
});

// --- GROUP 4: ADMIN & ALL OPERATORS (Manajemen CCTV & User) ---
Route::middleware(['auth', 'role:admin,operator,faculty_operator'])->group(function () {
    Route::get('cctv/migration', [\App\Http\Controllers\CctvMigrationController::class, 'index'])->name('cctv.migration');
    Route::get('cctv/migration/template', [\App\Http\Controllers\CctvMigrationController::class, 'downloadTemplate'])->name('cctv.migration.template');
    Route::post('cctv/migration/import', [\App\Http\Controllers\CctvMigrationController::class, 'import'])->name('cctv.migration.import');
    Route::post('cctv/bulk-move', [CctvController::class, 'bulkMove'])->name('cctv.bulkMove');
    Route::resource('cctv', CctvController::class);
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
        
        $urlWithSuffix = "ffmpeg:{$fullUrl}#video=copy#audio=aac#rtsp_transport=tcp";
        
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

    \App\Models\CameraEvent::create([
        'cctv_id' => $cctvId,
        'event_type' => $type,
        'event_time' => now(),
        'metadata' => $request->all()
    ]);

    return response()->json(['status' => 'Event Recorded']);
});

require __DIR__.'/auth.php';

// Halaman Riwayat Kejadian (Events)
Route::middleware(['auth'])->group(function () {
    Route::get('/events', [App\Http\Controllers\EventController::class, 'index'])->name('events.index');
    Route::post('/events/mark-all-read', [App\Http\Controllers\EventController::class, 'markAllRead'])->name('events.markAllRead');
    Route::post('/events/{id}/read', [App\Http\Controllers\EventController::class, 'markAsRead'])->name('events.read');
});