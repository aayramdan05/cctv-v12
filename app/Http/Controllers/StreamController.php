<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use Illuminate\Http\Request;

class StreamController extends Controller
{
    public function play(Request $request, Cctv $cctv)
    {
        // 1. Ambil URL RTSP yang sudah aman (Model sudah handle encoding karakter @ jadi %40)
        // Contoh hasil: rtsp://admin:cctv%40J4t1nangor@10.67...
        $rtspUrl = $cctv->stream_url;

        // 2. Encode URL tersebut agar bisa masuk sebagai parameter browser
        // Contoh hasil: rtsp%3A%2F%2Fadmin%3Acctv%2540J4t1nangor...
        $encodedUrl = urlencode($rtspUrl);

        // 3. Tentukan Host
        $serverHost = $request->getHost(); 

        // 4. Redirect langsung ke Player dengan URL Asli
        // Kita pakai mode lengkap (webrtc,mse,hls,mjpeg) agar anti-gagal
        $playerUrl = "http://{$serverHost}:1984/stream.html?src={$encodedUrl}&mode=webrtc,mse,hls,mjpeg";

        return redirect($playerUrl);
    }
}