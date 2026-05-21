<?php

use App\Models\Server;
use App\Models\Cctv;
use Illuminate\Support\Facades\DB;

// Jika dijalankan via Artisan Tinker, Laravel sudah ter-bootstrap.
// Jika tidak, kita bootstrap manual.
if (!isset($app)) {
    require __DIR__ . "/../vendor/autoload.php";
    $app = require __DIR__ . "/../bootstrap/app.php";
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

DB::beginTransaction();

try {
    echo "--- Memulai Perbaikan ID Server ---\n";
    $node = Server::where("ip_address", "10.69.69.41")->first();
    
    if (!$node) {
        throw new Exception("Server dengan IP 10.69.69.41 tidak ditemukan.");
    }

    $oldId = $node->id;
    $newId = 1;

    if ($oldId !== $newId) {
        echo "Mengubah Server ID dari {$oldId} menjadi {$newId}...\n";
        
        // Hapus master lama jika ada
        DB::table("servers")->where("id", $newId)->delete();
        
        DB::statement("SET FOREIGN_KEY_CHECKS=0;");
        DB::table("servers")->where("id", $oldId)->update(["id" => $newId]);
        echo "Update tabel servers berhasil.\n";
    } else {
        echo "ID Server sudah 1. Melanjutkan...\n";
    }

    echo "Sinkronisasi tabel cctvs...\n";
    $updatedCctvs = DB::table("cctvs")->where("server_id", $oldId)->update(["server_id" => $newId]);
    echo "{$updatedCctvs} kamera telah diperbarui.\n";

    DB::statement("SET FOREIGN_KEY_CHECKS=1;");
    DB::commit();
    echo "--- BERHASIL ---\n";
} catch (\Exception $e) {
    if (DB::transactionLevel() > 0) DB::rollBack();
    DB::statement("SET FOREIGN_KEY_CHECKS=1;");
    echo "ERROR: " . $e->getMessage() . "\n";
}
