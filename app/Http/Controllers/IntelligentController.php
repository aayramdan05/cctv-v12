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
        $customEndpoint = $request->input('endpoint', '/LAPI/V1.0/Intelligent/PeopleCounting/Report');

        if (!$ip || !$username || !$password) {
            return response()->json([
                'error' => 'IP, Username, dan Password kamera harus diisi.'
            ], 400);
        }

        try {
            // Menggunakan Endpoint Dinamis
            $url = "http://{$ip}" . $customEndpoint;

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
