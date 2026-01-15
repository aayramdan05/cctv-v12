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
            // Asumsi relasi: User punya kolom 'faculty', CCTV punya relasi 'building' -> 'fakultas'
            return $user->faculty === $cctv->building->fakultas;
        }

        // 3. USER BIASA / USER API: Cek assignment spesifik (Jika ada tabel pivot)
        // Contoh: return $user->allowedCctvs->contains($cctv->id);
        
        // Default: Tolak
        return false;
    }
}