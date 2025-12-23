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
    
    // 1. Get User Info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // 2. Get List CCTV
    Route::get('/cctvs', function () {
        // Return JSON daftar kamera
        return Cctv::with('building')->get()->map(function($cam) {
            return [
                'id' => $cam->id,
                'name' => $cam->nama_cctv,
                'building' => $cam->building->nama_gedung ?? '-',
                'stream_url' => $cam->live_stream_url, // Hati-hati mengekspos ini
                'status' => 'online'
            ];
        });
    });
  });

