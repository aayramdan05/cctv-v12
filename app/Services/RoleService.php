<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RoleService
{
    private const FILE_PATH = 'roles_metadata.json';
    
    private const DEFAULT_ROLES = [
        'superadmin' => [
            'slug' => 'superadmin',
            'title' => 'Super Administrator',
            'desc' => 'Akses penuh ke seluruh sistem',
            'icon' => 'fa-crown',
            'color' => 'from-red-500 to-red-700',
            'is_system' => true
        ],
        'admin' => [
            'slug' => 'admin',
            'title' => 'Administrator',
            'desc' => 'Mengelola CRUD cctv, peta, & data master',
            'icon' => 'fa-user-tie',
            'color' => 'from-cyan-500 to-blue-500',
            'is_system' => true
        ],
        'operator' => [
            'slug' => 'operator',
            'title' => 'Operator Pusat',
            'desc' => 'Memantau live stream, playback, dan koordinat peta',
            'icon' => 'fa-users-cog',
            'color' => 'from-emerald-500 to-teal-500',
            'is_system' => true
        ],
        'upt_lingkungan' => [
            'slug' => 'upt_lingkungan',
            'title' => 'UPT Lingkungan',
            'desc' => 'Memantau live stream, playback (Default mirip Operator)',
            'icon' => 'fa-leaf',
            'color' => 'from-teal-500 to-cyan-500',
            'is_system' => true
        ],
        'faculty_operator' => [
            'slug' => 'faculty_operator',
            'title' => 'Operator Fakultas',
            'desc' => 'Memantau & mengelola cctv terbatas pada fakultasnya',
            'icon' => 'fa-university',
            'color' => 'from-purple-500 to-indigo-500',
            'is_system' => true
        ],
        'user' => [
            'slug' => 'user',
            'title' => 'User Biasa',
            'desc' => 'Hak akses pemantauan standar (view only)',
            'icon' => 'fa-user',
            'color' => 'from-amber-500 to-orange-500',
            'is_system' => true
        ],
        'api_viewer' => [
            'slug' => 'api_viewer',
            'title' => 'API Viewer / Client',
            'desc' => 'Koneksi integrasi data pihak ketiga',
            'icon' => 'fa-robot',
            'color' => 'from-slate-600 to-slate-800',
            'is_system' => true
        ]
    ];

    /**
     * Get all roles metadata (merging default and custom).
     */
    public function getAllRoles()
    {
        $customRoles = [];
        
        if (Storage::exists(self::FILE_PATH)) {
            $content = Storage::get(self::FILE_PATH);
            $customRoles = json_decode($content, true) ?: [];
        }

        return array_merge(self::DEFAULT_ROLES, $customRoles);
    }

    /**
     * Create a new custom role.
     */
    public function createRole($slug, $title, $desc, $icon, $color)
    {
        $roles = $this->getAllRoles();
        
        if (isset($roles[$slug])) {
            throw new \Exception('Role dengan ID (slug) tersebut sudah ada.');
        }

        // Simpan ke DB role_permissions dengan permission kosong agar tidak error RBAC
        try {
            DB::table('role_permissions')->insert([
                'role' => $slug,
                'permissions' => '[]',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Abaikan jika duplikat di db
        }

        // Ambil custom roles saat ini
        $customRoles = [];
        if (Storage::exists(self::FILE_PATH)) {
            $customRoles = json_decode(Storage::get(self::FILE_PATH), true) ?: [];
        }

        $customRoles[$slug] = [
            'slug' => $slug,
            'title' => $title,
            'desc' => $desc,
            'icon' => $icon,
            'color' => $color,
            'is_system' => false
        ];

        Storage::put(self::FILE_PATH, json_encode($customRoles, JSON_PRETTY_PRINT));
    }

    /**
     * Delete a custom role.
     */
    public function deleteRole($slug)
    {
        $roles = $this->getAllRoles();
        
        if (!isset($roles[$slug])) {
            throw new \Exception('Role tidak ditemukan.');
        }

        if ($roles[$slug]['is_system']) {
            throw new \Exception('Role bawaan sistem tidak boleh dihapus.');
        }

        // Hapus dari DB
        DB::table('role_permissions')->where('role', $slug)->delete();
        
        // Hapus custom role dari file JSON
        $customRoles = [];
        if (Storage::exists(self::FILE_PATH)) {
            $customRoles = json_decode(Storage::get(self::FILE_PATH), true) ?: [];
        }
        
        if (isset($customRoles[$slug])) {
            unset($customRoles[$slug]);
            Storage::put(self::FILE_PATH, json_encode($customRoles, JSON_PRETTY_PRINT));
        }
    }
}
