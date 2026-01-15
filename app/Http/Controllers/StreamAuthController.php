<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Cctv;

class StreamAuthController extends Controller
{
    public function verify(Request $request)
    {
        $user = null;
        $authSource = '';

        // =========================================================
        // TAHAP 1: IDENTIFIKASI USER (AUTHENTICATION)
        // Logika ekstraksi token TIDAK DIUBAH, hanya outputnya disimpan ke $user
        // =========================================================

        // 1. CEK USER WEB (Browser Session)
        if (Auth::check()) {
            $user = Auth::user();
            $authSource = 'Web Session';
        }

        // 2. CEK TOKEN API (Jika belum ketemu di session)
        if (!$user) {
            // Plan A: Ambil dari Query String standar
            $tokenString = $request->query('api_token');
            $originalUri = $request->header('X-Original-URI');
            $queryParams = [];

            // Plan B: Parsing Header X-Original-URI (Logika Asli Anda)
            if (empty($tokenString) && $originalUri) {
                $parsedUrl = parse_url($originalUri);
                if (isset($parsedUrl['query'])) {
                    parse_str($parsedUrl['query'], $queryParams);
                    $tokenString = $queryParams['api_token'] ?? null;
                }
            }

            if ($tokenString) {
                $token = PersonalAccessToken::findToken($tokenString);

                if ($token && $token->tokenable) {
                    $token->forceFill(['last_used_at' => now()])->save();
                    $user = $token->tokenable; // Simpan User dari Token
                    $authSource = 'API Token';
                }
            }
        }

        // Jika User Tidak Dikenal -> TOLAK
        if (!$user) {
            return response('Unauthorized', 401);
        }

        // =========================================================
        // TAHAP 2: CEK HAK AKSES KAMERA (AUTHORIZATION / POLICY)
        // User sudah ketemu, sekarang cek apakah dia boleh lihat kamera ini?
        // =========================================================

        // 1. Ambil Parameter 'src' (ID Kamera)
        // Kita gunakan logika Plan A/Plan B yang sama untuk mengambil 'src'
        // karena Nginx mungkin membuang query string utama.
        $src = $request->query('src');
        
        if (empty($src) && isset($queryParams['src'])) {
            $src = $queryParams['src'];
        }

        // Jika request tidak menyertakan 'src' (misal request root /), 
        // kita anggap lolos authentication saja (200 OK)
        if (!$src) {
            return response("OK - $authSource (No Target)", 200);
        }

        // 2. Parsing ID Kamera (Format: "camera_19" -> "19")
        // Sesuaikan dengan format Go2RTC Anda
        $cctvId = str_replace('camera_', '', $src);

        // Jika hasil replace bukan angka, tolak (Security check)
        if (!is_numeric($cctvId)) {
            return response('Bad Request - Invalid Source ID', 400);
        }

        // 3. Cari Data CCTV
        $cctv = Cctv::with('building')->find($cctvId);

        if (!$cctv) {
            return response('Not Found - Camera does not exist', 404);
        }

        // 4. CEK POLICY (RBAC)
        // Ini akan memanggil method view() di CctvPolicy.php
        if (Gate::forUser($user)->allows('view', $cctv)) {
            return response("OK - $authSource (Authorized)", 200);
        }

        // Jika User Login tapi tidak punya hak akses ke kamera ini
        return response('Forbidden - Access Denied', 403);
    }
}