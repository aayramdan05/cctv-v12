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
        Schema::create('recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cctv_id')->constrained('cctvs')->onDelete('cascade');
            $table->date('date');
            $table->string('filename');
            $table->integer('start_time')->comment('Seconds from midnight');
            $table->integer('duration')->default(900);
            $table->float('size_mb', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recordings');
    }
};
