<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya tambahkan header keamanan ke response HTTP standar
        if (method_exists($response, 'header')) {
            // HSTS: Wajibkan HTTPS selama 1 tahun (F9)
            if ($request->isSecure() || $request->header('X-Forwarded-Proto') === 'https') {
                $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            }

            // Anti-MIME sniffing
            $response->header('X-Content-Type-Options', 'nosniff');

            // Kontrol Referrer
            $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

            // Batasi API browser sensitif yang tidak dipakai
            $response->header('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        }

        return $response;
    }
}

