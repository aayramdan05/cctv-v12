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

        // 2. OPERATOR FAKULTAS
        if ($user->role === 'faculty_operator') {
            // Hati-hati: === itu Case Sensitive. Pastikan "Hukum" == "Hukum"
            // Gunakan strcasecmp atau str to lower untuk lebih aman
            return strtolower($user->faculty) === strtolower($cctv->building->fakultas);
        }

        // 3. USER BIASA (Perbaikan)
        // Jika user biasa belum punya mapping khusus, mau diizinkan apa?
        // Opsi A: Izinkan semua kamera 'public' (jika ada kolom is_public)
        // return $cctv->is_public; 
        
        // Opsi B: Sementara izinkan semua user login (Development Mode)
        // return true; 

        // Opsi C (Ideal): Cek tabel relasi (perlu buat tabel cctv_user dulu)
        // return $user->allowedCctvs()->where('cctv_id', $cctv->id)->exists();

        return false; // Default tolak
    }
}