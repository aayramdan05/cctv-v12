<?php

use App\Models\Server;
use App\Models\Cctv;
use Illuminate\Support\Facades\DB;

if (!isset($app)) {
    require __DIR__ . "/../vendor/autoload.php";
    $app = require __DIR__ . "/../bootstrap/app.php";
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

DB::beginTransaction();

try {
    echo "--- Memulai Perbaikan ID Server (PostgreSQL Safe Mode) ---\n";
    
    $oldId = 2;
    $newId = 1;
    $ipTarget = "10.69.69.41";

    $node = DB::table("servers")->where("ip_address", $ipTarget)->where("id", $oldId)->first();
    
    if (!$node) {
        $checkAny = DB::table("servers")->where("ip_address", $ipTarget)->first();
        if ($checkAny && $checkAny->id == $newId) {
            echo "Server ID sudah 1. Melanjutkan sinkronisasi CCTV...\n";
            $oldId = $newId; // Tetap jalankan update CCTV just in case
        } else {
            throw new Exception("Server dengan IP {$ipTarget} dan ID {$oldId} tidak ditemukan.");
        }
    } else {
        echo "Memindahkan Server ID {$oldId} ke {$newId}...\n";

        // 1. Hapus master lama dengan ID 1 jika ada
        DB::table("servers")->where("id", $newId)->delete();

        // 2. Insert row baru dengan ID 1 (Copy data dari ID 2)
        $nodeArray = (array)$node;
        $nodeArray["id"] = $newId;
        DB::table("servers")->insert($nodeArray);
        
        echo "Berhasil membuat Server dengan ID {$newId}.\n";
    }

    // 3. Update CCTV agar merujuk ke ID 1
    echo "Sinkronisasi tabel cctvs...\n";
    $updatedCctvs = DB::table("cctvs")->where("server_id", $oldId)->update(["server_id" => $newId]);
    echo "{$updatedCctvs} kamera telah diperbarui ke Server ID {$newId}.\n";

    // 4. Hapus server lama (ID 2) jika tadi kita melakukan pemindahan
    if ($oldId !== $newId) {
        DB::table("servers")->where("id", $oldId)->delete();
        echo "Server ID {$oldId} telah dihapus.\n";
    }

    // 5. Update Sequence (Penting untuk PostgreSQL agar auto-increment tidak error)
    try {
        DB::statement("SELECT setval(pg_get_serial_sequence(\"servers\", \"id\"), coalesce(max(id), 1)) FROM servers;");
        echo "Sequence database telah diperbarui.\n";
    } catch (\Exception $eSequence) {
        echo "Warning: Gagal mengupdate sequence (bisa diabaikan jika tidak pakai auto-increment): " . $eSequence->getMessage() . "\n";
    }

    DB::commit();
    echo "--- SEMUA BERHASIL DIPERBAIKI ---\n";
} catch (\Exception $e) {
    if (DB::transactionLevel() > 0) DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
