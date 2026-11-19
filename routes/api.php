<?php

use Illuminate\Http\Request;
use App\Models\Cctv;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IntelligentController;

// Webhook Catcher untuk UNV Camera (LAPI Push)
// Kita tangkap semua method POST ke /api/webhook/unv-push atau URL apapun yang diakhiri wildcard
Route::post('/webhook/unv-push', [IntelligentController::class, 'receiveWebhook']);
Route::post('/LAPI/{any}', [IntelligentController::class, 'receiveWebhook'])->where('any', '.*');
// Endpoint penerimaan event deteksi AI dari script Python (ai_analyzer.py)
Route::post('/ai-events', [IntelligentController::class, 'storeEvent']);

// Endpoint untuk Node mengambil config
Route::get('/node-config', function (Request $request) {
    $secret = env('SYNC_TOKEN', 'secret_unpad_cctv_2026');
    $token = $request->bearerToken() ?: $request->query('token');

    // 1. Validasi Token
    if (!$token || $token !== $secret) {
        return response()->json(['error' => 'Unauthorized: Invalid Token'], 401);
    }

    $ip = $request->query('ip');
    if (!$ip) {
        return response()->json(['error' => 'Missing node IP'], 400);
    }
    
    // 2. Cari Server ID berdasarkan IP
    $server = \App\Models\Server::where('ip_address', $ip)->first();
    if (!$server) {
        return response()->json(['error' => 'Server not found'], 404);
    }

    // 3. Ambil kamera khusus untuk server tersebut
    $cameras = Cctv::where('server_id', $server->id)
                 ->where('status', 'online')
                 ->get(); 

    // 4. Susun format untuk Go2RTC & Log Node
    $streams = [];
    $camerasList = [];

    foreach ($cameras as $cam) {
        $fullUrl = $cam->stream_url;
        $urlWithSuffix = "{$fullUrl}#rtsp_transport=tcp";
        $streams["camera_{$cam->id}"] = [$urlWithSuffix];

        $camerasList[] = [
            'id' => $cam->id,
            'kode_cctv' => $cam->kode_cctv ?? "CAM-{$cam->id}",
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

    return response()->json([
        'streams' => $streams,
        'cameras_list' => $camerasList
    ]);
});

// Endpoint untuk Node melaporkan event (Motion Detection, ONVIF status, dll)
Route::get('/report-event', function (Request $request) {
    $secret = env('SYNC_TOKEN', 'secret_unpad_cctv_2026');
    $token = $request->bearerToken() ?: $request->query('token');

    if (!$token || $token !== $secret) {
        return response()->json(['error' => 'Unauthorized: Invalid Token'], 401);
    }

    $cctvId = $request->query('cctv_id');
    $type = $request->query('type', 'motion');

    if (!$cctvId) {
        return response()->json(['error' => 'Missing CCTV ID'], 400);
    }

    $cctv = Cctv::find($cctvId);
    if ($cctv && $type === 'onvif_status') {
        $status = $request->query('status');
        $error = $request->query('error');

        $statusChanged = $cctv->onvif_status !== $status || $cctv->onvif_error !== $error;
        if ($statusChanged) {
            try {
                $cctv->update([
                    'onvif_status' => $status,
                    'onvif_error' => $error
                ]);
            } catch (\Exception $e) {
                // Ignore if column missing
            }

            \App\Models\CameraEvent::create([
                'cctv_id' => $cctvId,
                'event_type' => 'onvif',
                'event_time' => now(),
                'metadata' => ['status' => $status, 'error' => $error]
            ]);
        }

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

// Endpoint Data CCTV (Protected by Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/cctvs', function (Request $request) {
        
        // 1. Ambil data User yang memiliki token ini
        $user = $request->user();

        // 2. Ambil token mentah yang sedang dipakai
        $currentToken = $request->bearerToken(); 

        // 3. PERUBAHAN UTAMA: Gunakan $user->cctvs() BUKAN Cctv::all()
        return $user->cctvs()->with('building')->get()->map(function($cam) use ($currentToken) {
            
            // Logika untuk menempelkan token ke URL
            $separatorLive = parse_url($cam->live_stream_url, PHP_URL_QUERY) ? '&' : '?';
            $separatorHls = parse_url($cam->hls_stream_url, PHP_URL_QUERY) ? '&' : '?';
            
            // Tempelkan token di ujung URL
            $signedLiveUrl = $cam->live_stream_url . $separatorLive . "api_token=" . $currentToken;
            $signedHlsUrl = $cam->hls_stream_url . $separatorHls . "api_token=" . $currentToken;

            return [
                'id' => $cam->id,
                'name' => $cam->nama_cctv,
                'building' => $cam->building->nama_gedung ?? '-',
                'live_url' => $signedLiveUrl, // Untuk Web View
                'hls_url' => $signedHlsUrl,   // <--- UNTUK MOBILE (M3U8)
                'status' => 'online'
            ];
        });
    });

});