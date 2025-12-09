<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleCheck
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Belum Login -> Lempar ke Login
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // 2. Admin -> Boleh lewat kemana saja (Super User)
        if ($user->role === 'admin') {
            return $next($request);
        }

        // 3. Cek Role sesuai parameter route
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // 4. PENTING: Jika role salah, JANGAN REDIRECT.
        // Matikan proses dengan error 403 (Forbidden).
        // Ini memutus rantai looping.
        abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk halaman ini.');
    }
}