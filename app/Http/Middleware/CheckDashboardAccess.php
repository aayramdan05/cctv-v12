<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckDashboardAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Blokir jika user adalah 'api_viewer'
        if ($user && $user->role === 'api_viewer') {
            
            // Logout paksa
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Kembalikan ke login dengan pesan error
            return redirect()->route('login')->withErrors([
                'email' => 'Akun ini khusus API Client dan tidak diizinkan mengakses Dashboard Web.',
            ]);
        }

        return $next($request);
    }
}