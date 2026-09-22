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
     * Poll data from UNV Camera API (LAPI)
     */
    public function getRealtimeData(Request $request)
    {
        $ip = $request->input('ip');
        $username = $request->input('username', 'admin');
        $password = $request->input('password');

        if (!$ip || !$username || !$password) {
            return response()->json([
                'error' => 'IP, Username, dan Password kamera harus diisi.'
            ], 400);
        }

        try {
            // UNV LAPI Endpoint for People Counting Report
            $url = "http://{$ip}/LAPI/V1.0/Intelligent/PeopleCounting/Report";

            // Uniview cameras usually require Digest Authentication
            $response = Http::timeout(3)
                ->withDigestAuth($username, $password)
                ->get($url);

            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'raw_data' => $response->json() // Sending raw data back to frontend to inspect
                ]);
            }

            return response()->json([
                'error' => 'Gagal mengambil data. Status Code: ' . $response->status(),
                'raw_response' => $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error("UNV LAPI Error: " . $e->getMessage());
            return response()->json([
                'error' => 'Koneksi gagal atau kamera tidak merespons.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
