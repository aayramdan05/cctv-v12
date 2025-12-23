<?php

use Illuminate\Http\Request;
use App\Models\Cctv;
use Illuminate\Support\Facades\Route;

// Endpoint untuk Node mengambil config
// Contoh akses: http://ip-master/api/node-config?server_id=1&secret=RAHASIA
Route::get('/node-config', function (Request $request) {
    // Validasi token sederhana biar gak sembarang orang ambil
    if ($request->query('secret') !== 'apicctvD@pnu1957') {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $serverId = $request->query('server_id');
    
    // Ambil kamera khusus untuk server tersebut
    $cctvs = Cctv::where('server_id', $serverId)
                 ->where('status', 'online')
                 ->get(['id', 'stream_url']); // Cukup ID dan URL

    return response()->json($cctvs);
});

// Endpoint Data CCTV (Protected by Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/cctvs', function (Request $request) {
        
        // Ambil token mentah yang sedang dipakai request ini
        // (Token yang dikirim mobile app di header Authorization)
        $currentToken = $request->bearerToken(); 

        return Cctv::with('building')->get()->map(function($cam) use ($currentToken) {
            
            // Logika untuk menempelkan token ke URL
            // Cek apakah URL asli sudah punya tanda tanya '?'
            $separator = parse_url($cam->live_stream_url, PHP_URL_QUERY) ? '&' : '?';
            
            // Tempelkan token di ujung URL
            // Contoh hasil: /node1/stream.html?src=camera_1&api_token=1|Xyz...
            $signedUrl = $cam->live_stream_url . $separator . "api_token=" . $currentToken;

            return [
                'id' => $cam->id,
                'name' => $cam->nama_cctv,
                'building' => $cam->building->nama_gedung ?? '-',
                'stream_url' => $signedUrl, // <--- URL SAKTI
                'status' => 'online'
            ];
        });
    });

});