<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use App\Services\RoleService;

class SuperAdminController extends Controller
{
    /**
     * Display a listing of user login and CCTV viewing logs.
     */
    public function userLogs(Request $request)
    {
        $query = ActivityLog::with(['user', 'cctv.building'])->latest();

        // Filter by User Name / Email
        if ($request->filled('search_user')) {
            $search = $request->search_user;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Activity Type
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        // Filter by Date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Paginate logs
        $logs = $query->paginate(30)->withQueryString();

        // Get aggregate stats
        $totalLogins = ActivityLog::where('activity_type', 'login')->count();
        $totalViews = ActivityLog::where('activity_type', 'cctv_view')->count();

        return view('superadmin.logs', compact('logs', 'totalLogins', 'totalViews'));
    }

    /**
     * Display dynamic RBAC page.
     */
    public function rbacIndex(RoleService $roleService)
    {
        $rolePermissions = [];
        try {
            $rolePermissions = DB::table('role_permissions')->get()->keyBy('role')->map(function($item) {
                return json_decode($item->permissions, true) ?: [];
            })->toArray();
        } catch (\Exception $e) {}

        $rolesList = $roleService->getAllRoles();

        return view('superadmin.rbac', compact('rolePermissions', 'rolesList'));
    }

    /**
     * Update dynamic RBAC permissions.
     */
    public function updateRbac(Request $request, RoleService $roleService)
    {
        $request->validate([
            'permissions' => 'required|array',
        ]);

        $roles = array_keys($roleService->getAllRoles());

        try {
            DB::transaction(function() use ($request, $roles) {
                foreach ($roles as $role) {
                    $perms = $request->input("permissions.{$role}", []);
                    
                    DB::table('role_permissions')
                        ->updateOrInsert(
                            ['role' => $role],
                            [
                                'permissions' => json_encode(array_values($perms)),
                                'updated_at' => now()
                            ]
                        );

                    // Clear the cache for this role
                    \Illuminate\Support\Facades\Cache::forget("role_permissions_{$role}");
                }
            });

            return redirect()->route('superadmin.rbac.index')->with('success', 'Konfigurasi hak akses role (RBAC) berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui hak akses: ' . $e->getMessage());
        }
    }

    /**
     * Display Role Management page.
     */
    public function rolesIndex(RoleService $roleService)
    {
        $roles = $roleService->getAllRoles();
        return view('superadmin.roles', compact('roles'));
    }

    /**
     * Store a new custom role.
     */
    public function roleStore(Request $request, RoleService $roleService)
    {
        $request->validate([
            'slug' => 'required|string|regex:/^[a-z0-9_]+$/|max:50',
            'title' => 'required|string|max:100',
            'desc' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
        ]);

        try {
            $icon = $request->icon ?: 'fa-user';
            $color = $request->color ?: 'from-slate-500 to-slate-700';

            $roleService->createRole($request->slug, $request->title, $request->desc, $icon, $color);

            return redirect()->route('superadmin.roles.index')->with('success', 'Role berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambah role: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Delete a custom role.
     */
    public function roleDestroy($slug, RoleService $roleService)
    {
        try {
            // Cek apakah ada user yg menggunakan role ini
            $usersCount = \App\Models\User::where('role', $slug)->count();
            if ($usersCount > 0) {
                return back()->with('error', "Role tidak bisa dihapus karena masih digunakan oleh {$usersCount} pengguna.");
            }

            $roleService->deleteRole($slug);

            return redirect()->route('superadmin.roles.index')->with('success', 'Role berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus role: ' . $e->getMessage());
        }
    }
}
