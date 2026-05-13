<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$id = 140;
$c = \App\Models\Cctv::find($id);

if ($c) {
    echo "--- DATA KAMERA $id ---\n";
    echo "Nama: " . $c->nama_cctv . "\n";
    echo "Server ID: " . ($c->server_id ?: 'NULL (BELUM DI-SET)') . "\n";
    echo "URL: " . ($c->stream_url ?: 'KOSONG') . "\n";
    
    if ($c->server_id) {
        $srv = \App\Models\Server::find($c->server_id);
        echo "Server Name: " . ($srv ? $srv->name : 'Server Tidak Ditemukan') . " (" . ($srv ? $srv->ip_address : '-') . ")\n";
    }
} else {
    echo "❌ Kamera dengan ID $id tidak ditemukan di database.\n";
}
