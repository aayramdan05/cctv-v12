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
        // 1. ADMIN: Bebas akses semua (Super User)
        if ($user->role === 'admin') {
            return true;
        }

        // 2. OPERATOR FAKULTAS: Akses berdasarkan kesamaan Nama Fakultas
        if ($user->role === 'faculty_operator') {
            // Gunakan strtolower agar tidak sensitif huruf besar/kecil
            $userFaculty = strtolower($user->faculty ?? '');
            $cctvFaculty = strtolower($cctv->building->fakultas ?? '');
            
            return $userFaculty === $cctvFaculty;
        }

        // 3. USER BIASA (Perbaikan Utama)
        // Cek apakah ID CCTV ini ada di dalam daftar yang di-assign ke user tersebut
        if ($user->role === 'user') {
            // Query ke tabel pivot (cctv_user) via relasi di Model User
            return $user->cctvs()->where('cctv_id', $cctv->id)->exists();
        }

        // 4. USER API (Pihak Ketiga)
        if ($user->role === 'api_viewer' || $user->role === 'viewer') {
             // Jika API user juga punya assignment spesifik, gunakan logika yang sama dengan 'user'
             return $user->cctvs()->where('cctv_id', $cctv->id)->exists();
        }
        
        // Default: Tolak semua yang tidak memenuhi syarat di atas
        return false;
    }
}