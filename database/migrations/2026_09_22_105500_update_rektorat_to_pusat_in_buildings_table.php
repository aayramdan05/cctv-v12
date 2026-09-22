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
        // Update all buildings where fakultas is 'Rektorat' or 'rektorat' to 'Pusat'
        DB::table('buildings')
            ->whereRaw('LOWER(fakultas) = ?', ['rektorat'])
            ->update(['fakultas' => 'Pusat']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We generally can't reliably revert this if there were already 'Pusat' buildings, 
        // but we can assume anything we just updated was Rektorat if needed, though usually data migrations like this are irreversible.
    }
};
