<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Cctv;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $cctvs = Cctv::all();
        foreach ($cctvs as $cctv) {
            $isAxis = str_contains(strtolower($cctv->rtsp_url ?? ''), 'axis') || 
                      str_contains(strtolower($cctv->rtsp_url_sub ?? ''), 'axis');
            
            $cctv->onvif_user = $isAxis ? 'root' : 'admin';
            $cctv->onvif_password = 'D1p4t1nangor#';
            $cctv->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migrasi data satu arah, tidak perlu revert
    }
};
