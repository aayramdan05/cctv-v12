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
            $table->integer('onvif_port')->default(80)->after('rtsp_password');
            $table->string('onvif_user')->nullable()->after('onvif_port');
            $table->text('onvif_password')->nullable()->after('onvif_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cctvs', function (Blueprint $table) {
            $table->dropColumn(['onvif_port', 'onvif_user', 'onvif_password']);
        });
    }
};
