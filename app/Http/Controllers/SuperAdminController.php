<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

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

        // Fetch role permissions for RBAC form
        $rolePermissions = [];
        try {
            $rolePermissions = DB::table('role_permissions')->get()->keyBy('role')->map(function($item) {
                return json_decode($item->permissions, true) ?: [];
            })->toArray();
        } catch (\Exception $e) {}

        return view('superadmin.logs', compact('logs', 'totalLogins', 'totalViews', 'rolePermissions'));
    }

    /**
     * Update dynamic RBAC permissions.
     */
    public function updateRbac(Request $request)
    {
        $request->validate([
            'permissions' => 'required|array',
        ]);

        $roles = ['admin', 'operator', 'faculty_operator', 'user', 'api_viewer'];

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

            return redirect()->route('superadmin.logs')->with('success', 'Konfigurasi hak akses role (RBAC) berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui hak akses: ' . $e->getMessage());
        }
    }
}
