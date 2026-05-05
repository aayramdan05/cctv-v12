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
        Schema::create('camera_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cctv_id')->constrained()->onDelete('cascade');
            $table->string('event_type')->default('motion'); // motion, line_crossing, etc.
            $table->timestamp('event_time');
            $table->string('snapshot_path')->nullable(); // Path ke gambar jika ada
            $table->json('metadata')->nullable(); // Data detail dari ONVIF
            $table->boolean('is_read')->default(false); // Untuk notifikasi di dashboard
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camera_events');
    }
};
