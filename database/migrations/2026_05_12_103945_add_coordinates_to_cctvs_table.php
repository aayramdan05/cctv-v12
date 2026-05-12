<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cctvs', function (Blueprint $table) {
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
        });

        // Set random coordinates around Jatinangor for existing cameras
        $cctvs = \DB::table('cctvs')->get();
        foreach ($cctvs as $cctv) {
            // Unpad Jatinangor center roughly: -6.9261, 107.7743
            $lat = -6.9261 + (rand(-5000, 5000) / 1000000);
            $lng = 107.7743 + (rand(-5000, 5000) / 1000000);
            
            \DB::table('cctvs')->where('id', $cctv->id)->update([
                'lat' => $lat,
                'lng' => $lng
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cctvs', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });
    }
};
