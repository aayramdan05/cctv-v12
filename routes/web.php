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
    Route::get('/api-keys', [ApiKeyController::class, 'index'])->name('api.index');
    Route::post('/api-keys', [ApiKeyController::class, 'store'])->name('api.store');
    Route::delete('/api-keys/{id}', [ApiKeyController::class, 'destroy'])->name('api.destroy');
    // Tools Streaming
    Route::get('/stream/{cctv}', [StreamController::class, 'play'])->name('stream.play');
    Route::post('/cctv/test-connection', [TestCameraController::class, 'test'])->name('cctv.test');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- GROUP 2: SUPER ADMIN (Infrastruktur) ---
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('servers', ServerController::class);
    Route::get('/ffmpeg-monitor', [FfmpegStatusController::class, 'index'])->name('ffmpeg.monitor');
});

// --- GROUP 3: ADMIN & OPERATOR (Master Data) ---
Route::middleware(['auth', 'role:admin,operator'])->group(function () {
    Route::resource('building', BuildingController::class);
});

// --- GROUP 4: ADMIN & ALL OPERATORS (Manajemen CCTV & User) ---
Route::middleware(['auth', 'role:admin,operator,faculty_operator'])->group(function () {
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
    // Nginx mengirim parameter asli: /auth-video?src=camera_7
    $src = $request->query('src'); 
    
    if ($src) {
        // Extract ID dari string "camera_7" -> "7"
        // Jika format lain (misal nama file), sesuaikan parsingnya
        $id = str_replace('camera_', '', $src);
        
        // Validasi ID harus angka (untuk keamanan)
        if (is_numeric($id)) {
            // Cek apakah user yang sedang login BOLEH melihat kamera ini?
            // Menggunakan scopeAccessibleByAuth yang ada di Model Cctv
            $exists = Cctv::accessibleByAuth()->where('id', $id)->exists();
            
            if (!$exists) {
                // User login, tapi mencoba akses kamera fakultas lain -> TOLAK
                return response('Forbidden: Access Denied to this Camera', 403);
            }
        }
    }

    // Jika Lolos Semua Cek
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
        $fullUrl = $cam->stream_url; // Menggunakan accessor getStreamUrlAttribute (Otomatis Didekripsi)
        
        // Tambahkan suffix wajib untuk Go2RTC
        $urlWithSuffix = "{$fullUrl}#video=copy#audio=aac#rtsp_transport=tcp";
        
        // Format sebagai LIST (Array) agar sesuai permintaan go2rtc
        $config['streams']["camera_{$cam->id}"] = [$urlWithSuffix];
        
        $config['cameras_list'][] = [
            'id' => $cam->id,
            'url' => "rtsp://127.0.0.1:8554/camera_{$cam->id}"
        ];
    }

    return response()->json($config);
});

require __DIR__.'/auth.php';