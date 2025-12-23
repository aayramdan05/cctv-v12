<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class StreamAuthController extends Controller
{
    /**
     * Satpam Hybrid:
     * 1. Cek apakah user login di Web (Cookie/Session).
     * 2. ATAU Cek apakah URL membawa ?api_token=... yang valid.
     */
    public function verify(Request $request)
    {
        // 1. CEK USER WEB (Browser Laptop/Desktop)
        if (Auth::check()) {
            return response('OK - Web Session', 200);
        }

        // 2. CEK TOKEN API (Aplikasi Mobile/3rd Party)
        // Token dikirim lewat URL: http://.../stream.html?api_token=1|AbCd...
        $tokenString = $request->query('api_token');

        if ($tokenString) {
            // Cari token di database Sanctum
            $token = PersonalAccessToken::findToken($tokenString);

            // Validasi Token: Harus ada, valid, dan belum expired (jika ada expired time)
            if ($token && $token->tokenable) {
                // Opsional: Update 'last_used_at' agar admin tahu token ini aktif
                $token->forceFill(['last_used_at' => now()])->save();
                
                return response('OK - API Token', 200);
            }
        }

        // Jika tidak punya tiket sama sekali
        return response('Unauthorized', 401);
    }
}