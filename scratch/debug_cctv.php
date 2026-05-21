<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require __DIR__ . "/../bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

$cctv = \App\Models\Cctv::where("server_id", 1)->first();
if ($cctv) {
    echo "ID: " . $cctv->id . "\n";
    echo "Server ID: " . $cctv->server_id . "\n";
    echo "Live URL: " . $cctv->live_stream_url . "\n";
} else {
    echo "No CCTV found for server_id 1\n";
}
