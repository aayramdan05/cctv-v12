<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;

class StreamAuthController extends Controller
{
    public function verify(Request $request)
    {
        // 1. CEK USER WEB (Browser Session)
        // Jika request datang dari dashboard web yang sudah login
        if (Auth::check()) {
            return response('OK - Web Session', 200);
        }

        // 2. CEK TOKEN API (Untuk Aplikasi Pihak Ketiga)
        // Token dikirim lewat URL: ?api_token=...
        $tokenString = $request->query('api_token');

        if ($tokenString) {
            // Cari token di database Sanctum
            $token = PersonalAccessToken::findToken($tokenString);

            if ($token && $token->tokenable) {
                // Update last_used_at (opsional)
                $token->forceFill(['last_used_at' => now()])->save();
                return response('OK - API Token', 200);
            }
        }

        // Jika tidak punya session web DAN tidak punya token valid
        return response('Unauthorized', 401);
    }
}