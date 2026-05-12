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
        Schema::table('cctvs', function (Blueprint $blueprint) {
            $blueprint->enum('penempatan', ['Indoor', 'Outdoor'])->default('Indoor')->after('building_id');
        });

        // Auto-assign logic: 
        // Jika nama gedung (via building_id) tidak diawali 'WM', set ke Outdoor.
        // Sebenarnya lebih akurat pakai subquery atau loop.
        // Di sini saya pakai query update mentah untuk performa.
        
        DB::statement("
            UPDATE cctvs 
            SET penempatan = 'Outdoor' 
            WHERE building_id IN (
                SELECT id FROM buildings WHERE kode_gedung NOT LIKE 'WM%'
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cctvs', function (Blueprint $blueprint) {
            $blueprint->dropColumn('penempatan');
        });
    }
};
