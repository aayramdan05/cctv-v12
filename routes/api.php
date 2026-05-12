<?php

use Illuminate\Http\Request;
use App\Models\Cctv;
use Illuminate\Support\Facades\Route;

// Endpoint untuk Node mengambil config
// Contoh akses: http://ip-master/api/node-config?ip=192.168.1.1&token=secret_unpad_cctv_2026
Route::get('/node-config', function (Request $request) {
    // 1. Validasi Token (Harus sama dengan SYNC_TOKEN di Node)
    if ($request->query('token') !== 'secret_unpad_cctv_2026') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $ip = $request->query('ip');
    
    // 2. Cari Server ID berdasarkan IP
    $server = \App\Models\Server::where('ip_address', $ip)->first();
    if (!$server) {
        return response()->json(['error' => 'Server not found'], 404);
    }

    // 3. Ambil kamera khusus untuk server tersebut
    $cctvs = Cctv::where('server_id', $server->id)
                 ->where('status', 'online')
                 ->get(['id', 'kode_cctv', 'nama_cctv', 'stream_url']); 

    // 4. Susun format untuk Go2RTC & Log Node
    $streams = [];
    foreach ($cctvs as $cam) {
        $streams["camera_{$cam->id}"] = [
            "ffmpeg:{$cam->stream_url}#video=copy#audio=aac#rtsp_transport=tcp"
        ];
    }

    return response()->json([
        'streams' => $streams,
        'cameras_list' => $cctvs
    ]);
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