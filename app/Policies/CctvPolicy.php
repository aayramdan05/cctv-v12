<?php

namespace App\Policies;

use App\Models\Cctv;
use App\Models\User;

class CctvPolicy
{
    /**
     * Tentukan siapa yang boleh melihat CCTV ini.
     */
    public function view(User $user, Cctv $cctv): bool
    {
        // 1. ADMIN: Bebas akses semua
        if ($user->role === 'admin') {
            return true;
        }

        // 2. OPERATOR FAKULTAS: Hanya kamera di fakultasnya
        if ($user->role === 'faculty_operator') {
            return strtolower($user->faculty) === strtolower($cctv->building->fakultas);
        }

        // 3. USER BIASA (Perbaikan untuk role 'user')
        if ($user->role === 'user') {
            // Izinkan jika fakultas user sama dengan fakultas kamera
            // Pastikan menggunakan strtolower untuk menghindari masalah huruf besar/kecil
            return strtolower($user->faculty) === strtolower($cctv->building->fakultas);
        }

        // 4. USER API (Contoh)
        if ($user->role === 'api_viewer') {
            return true; // Atau logika lain
        }
        
        // Default: Tolak
        return false;
    }
}