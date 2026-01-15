<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log; // Tambahkan Log untuk debugging

class StreamAuthController extends Controller
{
    public function verify(Request $request)
    {
        // 1. CEK USER WEB (Browser Session)
        if (Auth::check()) {
            return response('OK - Web Session', 200);
        }

        // 2. CEK TOKEN API
        // Plan A: Ambil dari Query String standar ($request->query)
        $tokenString = $request->query('api_token');

        // Plan B: Jika kosong, ambil dari Header X-Original-URI (Fix untuk Nginx auth_request)
        if (empty($tokenString)) {
            $originalUri = $request->header('X-Original-URI');
            if ($originalUri) {
                // Parse URL manual untuk mengambil parameter api_token
                $parsedUrl = parse_url($originalUri);
                if (isset($parsedUrl['query'])) {
                    parse_str($parsedUrl['query'], $queryParams);
                    $tokenString = $queryParams['api_token'] ?? null;
                }
            }
        }

        // Debugging (Cek di storage/logs/laravel.log jika masih error)
        // Log::info('StreamAuth Check:', ['token' => $tokenString]);

        if ($tokenString) {
            $token = PersonalAccessToken::findToken($tokenString);

            if ($token && $token->tokenable) {
                $token->forceFill(['last_used_at' => now()])->save();
                return response('OK - API Token', 200);
            }
        }

        return response('Unauthorized', 401);
    }
}