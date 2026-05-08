<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Cctv;
use Illuminate\Support\Facades\Log; // Added for debugging

class StreamAuthController extends Controller
{
    public function verify(Request $request)
    {
        // =========================================================
        // TAHAP 0: PARSING PARAMETER URL (ROBUST)
        // Kita parsing X-Original-URI di awal agar parameter 'src' & 'api_token' 
        // selalu tersedia, baik untuk User Web maupun User API.
        // =========================================================
        
        $params = $request->query(); // Mulai dengan query string bawaan request
        
        // Timpa/Gabungkan dengan parameter dari Header X-Original-URI (jika ada)
        // Ini adalah sumber paling akurat saat request lewat Nginx auth_request
        $originalUri = $request->header('X-Original-URI');
        if ($originalUri) {
            $parsedUrl = parse_url($originalUri);
            if (isset($parsedUrl['query'])) {
                $headerParams = [];
                parse_str($parsedUrl['query'], $headerParams);
                $params = array_merge($params, $headerParams);
            }
        }

        // =========================================================
        // TAHAP 1: IDENTIFIKASI USER (AUTHENTICATION)
        // =========================================================

        $user = null;
        $authSource = '';

        // 1. CEK USER WEB (Browser Session)
        if (Auth::check()) {
            $user = Auth::user();
            $authSource = 'Web Session';
        }

        // 2. CEK TOKEN API (Jika belum ketemu di session)
        if (!$user) {
            // Ambil token dari params yang sudah diparsing
            $tokenString = $params['api_token'] ?? null;

            if ($tokenString) {
                $token = PersonalAccessToken::findToken($tokenString);

                if ($token && $token->tokenable) {
                    $token->forceFill(['last_used_at' => now()])->save();
                    $user = $token->tokenable;
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
        // =========================================================

        // 1. Ambil Parameter 'src' dari params yang sudah diparsing
        $src = $params['src'] ?? null;

        // Jika tidak ada 'src', coba cari pola 'camera_ID' langsung di path URI (Berguna untuk file MP4)
        if (!$src && $originalUri) {
            if (preg_match('/camera_(\d+)/', $originalUri, $matches)) {
                $src = 'camera_' . $matches[1];
            }
        }

        if (!$src) {
            // Cek apakah URI asli mengarah ke stream video atau storage rekaman
            if ($originalUri && (str_contains($originalUri, 'stream.html') || str_contains($originalUri, '/storage/recordings/'))) {
                return response('Bad Request - Target camera not specified in URL', 400);
            }
            // Jika bukan stream atau storage (misal akses root api), loloskan auth user saja
            return response("OK - $authSource (No Target)", 200);
        }

        // 2. Parsing ID Kamera
        // Mendukung format "?src=camera_19" atau path "/storage/recordings/camera_19/..."
        if (preg_match('/camera_(\d+)/', $src, $matches)) {
            $cctvId = $matches[1];
        } else {
            return response('Bad Request - Invalid Source ID format', 400);
        }

        // 3. Cari Data CCTV
        $cctv = Cctv::with('building')->find($cctvId);

        if (!$cctv) {
            return response('Not Found - Camera does not exist', 404);
        }

        // 4. CEK POLICY (RBAC)
        // Pastikan User A hanya bisa melihat Kamera yang diizinkan untuknya
        if (Gate::forUser($user)->allows('view', $cctv)) {
            return response("OK - $authSource (Authorized)", 200);
        }

        // --- DEBUGGING LOG (Cek storage/logs/laravel.log jika Forbidden) ---
        Log::warning("Stream Access Denied", [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_faculty' => $user->faculty ?? 'N/A',
            'cctv_id' => $cctvId,
            'cctv_faculty' => $cctv->building->fakultas ?? 'N/A',
            'auth_source' => $authSource
        ]);

        return response('Forbidden - Access Denied', 403);
    }
}