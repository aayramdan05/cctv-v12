<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require __DIR__ . "/../bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();
use Illuminate\Support\Facades\DB;

$srv2 = DB::table("servers")->where("id", 2)->first();
$srv3 = DB::table("servers")->where("id", 3)->first();

echo "ID 2: " . ($srv2 ? $srv2->ip_address : "EMPTY") . "\n";
echo "ID 3: " . ($srv3 ? $srv3->ip_address : "EMPTY") . "\n";
