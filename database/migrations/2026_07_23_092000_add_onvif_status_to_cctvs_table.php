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
            $table->string('onvif_status')->nullable()->after('onvif_password')->comment('online, failed, unconfigured');
            $table->text('onvif_error')->nullable()->after('onvif_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cctvs', function (Blueprint $table) {
            $table->dropColumn(['onvif_status', 'onvif_error']);
        });
    }
};
