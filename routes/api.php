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
