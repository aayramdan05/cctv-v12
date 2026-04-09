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
        
        // 1. Ambil data User yang memiliki token ini
        $user = $request->user();

        // 2. Ambil token mentah yang sedang dipakai
        $currentToken = $request->bearerToken(); 

        // 3. PERUBAHAN UTAMA: Gunakan $user->cctvs() BUKAN Cctv::all()
        return $user->cctvs()->with('building')->get()->map(function($cam) use ($currentToken) {
            
            // Logika untuk menempelkan token ke URL
            $separator = parse_url($cam->live_stream_url, PHP_URL_QUERY) ? '&' : '?';
            
            // Tempelkan token di ujung URL
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