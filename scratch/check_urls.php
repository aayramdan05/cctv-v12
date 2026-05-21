<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require __DIR__ . "/../bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

$c = \App\Models\Cctv::where("server_id", 1)->first();
if ($c) {
    echo "ID: " . $c->id . "\n";
    echo "Server ID: " . $c->server_id . "\n";
    echo "Live URL: " . $c->live_stream_url . "\n";
    echo "HLS URL: " . $c->hls_stream_url . "\n";
    echo "Recording URL: " . $c->getRecordingUrl("2026-05-21", "test.mp4") . "\n";
} else {
    echo "No CCTV found for Server ID 1\n";
}
