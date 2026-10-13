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
        Schema::create('cctv_ai_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cctv_id')->constrained('cctvs')->cascadeOnDelete();
            $table->foreignId('recording_id')->nullable()->constrained('recordings')->nullOnDelete();
            
            // Tipe event: 'missing_item', 'presence_detected', 'vehicle_flow', 'motion_anomaly'
            $table->string('event_type', 50)->index();
            
            // Objek: 'laptop', 'cell phone', 'backpack', 'handbag', 'car', 'motorcycle', 'person'
            $table->string('object_type', 50)->index();
            
            // Visual metadata
            $table->string('color', 50)->nullable();
            $table->float('confidence')->default(0.0);
            
            // Snapshot URLs (disimpan di storage/ai_events/)
            $table->string('snapshot_url')->nullable();
            $table->string('snapshot_before_url')->nullable();
            $table->string('snapshot_after_url')->nullable();
            
            // Posisi bounding box [x1, y1, x2, y2]
            $table->json('bounding_box')->nullable();
            
            // Waktu kejadian
            $table->timestamp('event_time')->index();
            $table->timestamp('last_seen_time')->nullable(); // Terutama untuk kasus barang hilang
            
            // Narasi deskripsi manusia (dari Ollama / AI Engine)
            $table->text('narrative')->nullable();
            
            // Data tambahan (tracker_id, person_correlated, raw info)
            $table->json('metadata')->nullable();
            
            $table->timestamps();

            $table->index(['cctv_id', 'event_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cctv_ai_events');
    }
};
