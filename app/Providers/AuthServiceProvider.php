<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // 1. Superadmin bypasses all checks
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if ($user->role === 'superadmin') {
                return true;
            }
        });

        // 2. Define the 22 granular permissions dynamically
        $permissions = [
            'dashboard_view',
            'live_monitoring',
            'playback_view',
            'playback_export',
            'map_view',
            'map_update_coords',
            'cctv_view',
            'cctv_create',
            'cctv_edit',
            'cctv_delete',
            'cctv_bulk_move',
            'cctv_import',
            'user_view',
            'user_create',
            'user_edit',
            'user_delete',
            'user_approve',
            'server_manage',
            'api_key_manage',
            'report_view',
            'event_view',
            'notification_manage',
            'building_manage',
            'activity_log_view',
        ];

        foreach ($permissions as $permission) {
            \Illuminate\Support\Facades\Gate::define($permission, function ($user) use ($permission) {
                try {
                    $rolePermissions = \Illuminate\Support\Facades\Cache::rememberForever("role_permissions_{$user->role}", function () use ($user) {
                        $record = \Illuminate\Support\Facades\DB::table('role_permissions')->where('role', $user->role)->first();
                        if ($record) {
                            return json_decode($record->permissions, true) ?: [];
                        }
                        return [];
                    });
                } catch (\Exception $e) {
                    $rolePermissions = [];
                }

                return in_array($permission, $rolePermissions);
            });
        }
    }
}
