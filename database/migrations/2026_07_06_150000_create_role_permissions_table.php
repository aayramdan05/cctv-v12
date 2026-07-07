<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->string('role')->primary();
            $table->json('permissions');
            $table->timestamps();
        });

        // Seed default permissions for each role
        $defaults = [
            'admin' => [
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
                'server_manage',
                'api_key_manage',
                'report_view',
                'event_view',
                'notification_manage',
                'building_manage',
                'activity_log_view',
            ],
            'operator' => [
                'dashboard_view',
                'live_monitoring',
                'playback_view',
                'playback_export',
                'map_view',
                'cctv_view',
                'cctv_create',
                'cctv_edit',
                'cctv_bulk_move',
                'cctv_import',
                'user_view',
                'user_create',
                'user_edit',
            ],
            'faculty_operator' => [
                'dashboard_view',
                'live_monitoring',
                'playback_view',
                'playback_export',
                'map_view',
                'cctv_view',
                'cctv_create',
                'cctv_edit',
                'cctv_bulk_move',
                'cctv_import',
                'user_view',
                'user_create',
                'user_edit',
            ],
            'user' => [
                'live_monitoring',
                'map_view',
            ],
            'api_viewer' => []
        ];

        foreach ($defaults as $role => $perms) {
            DB::table('role_permissions')->insert([
                'role' => $role,
                'permissions' => json_encode($perms),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
