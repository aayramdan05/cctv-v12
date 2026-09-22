<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IntelligentController extends Controller
{
    /**
     * Display the Intelligent Testing Dashboard.
     */
    public function index()
    {
        return view('intelligent.index');
    }

    /**
     * Poll data from Cache (populated by webhook)
     */
    public function getRealtimeData(Request $request)
    {
        // Baca data terbaru dari cache (disimpan oleh Webhook)
        $latestData = cache()->get('unv_intelligent_data');
        
        if ($latestData) {
            return response()->json([
                'status' => 'success',
                'raw_data' => $latestData,
                'message' => 'Data from webhook cache'
            ]);
        }

        return response()->json([
            'status' => 'waiting',
            'error' => 'Menunggu pengiriman data (Push) dari kamera UNV...',
        ], 202);
    }

    /**
     * Endpoint untuk menerima Push Event (Webhook) dari kamera UNV
     */
    public function receiveWebhook(Request $request)
    {
        // 1. Ambil semua data (Headers & Body)
        $headers = $request->headers->all();
        $body = $request->all();
        $rawBody = $request->getContent(); // In case it's XML or raw string

        $dataToCache = [
            'timestamp' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'path' => $request->path(),
            'headers' => $headers,
            'body_json' => $body,
            'body_raw' => $rawBody
        ];

        // 2. Simpan di Cache selama 5 menit
        cache()->put('unv_intelligent_data', $dataToCache, 300);

        // 3. Catat di Log (agar bisa dianalisis jika ada masalah)
        Log::info('UNV Webhook Received:', $dataToCache);

        // 4. Balas dengan Response Code 200 (Success) agar kamera tahu data berhasil dikirim
        return response()->json([
            'Response' => [
                'ResponseCode' => 0,
                'ResponseString' => 'Succeed'
            ]
        ]);
    }

    public function checkOnvif(Request $request)
    {
        $ip = $request->input('ip');
        $username = $request->input('username', 'admin');
        $password = $request->input('password');

        if (!$ip || !$username || !$password) {
            return response()->json(['error' => 'IP, Username, dan Password kamera harus diisi.'], 400);
        }

        try {
            // LAPI Discovery
            $url = "http://{$ip}/LAPI/V1.0/System/Capabilities";

            $response = Http::timeout(5)
                ->withDigestAuth($username, $password)
                ->get($url);

            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'xml_response' => $response->body()
                ]);
            }

            return response()->json([
                'error' => 'Gagal mengambil data Capabilities. Status Code: ' . $response->status(),
                'xml_response' => $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Connection Failed',
                'xml_response' => $e->getMessage()
            ], 500);
        }
    }
}
