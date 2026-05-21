<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require __DIR__ . "/../bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use Illuminate\Support\Facades\DB;

\$server = DB::table("servers")->where("id", 1)->first();
if (\$server) {
    echo "SERVER ID 1 FOUND: IP " . \$server->ip_address . "\n";
} else {
    echo "SERVER ID 1 NOT FOUND!\n";
}

\$cctvCount = DB::table("cctvs")->where("server_id", 1)->count();
echo "CCTV count for server_id 1: " . \$cctvCount . "\n";

\$cctvCountOld = DB::table("cctvs")->where("server_id", 2)->count();
echo "CCTV count for server_id 2: " . \$cctvCountOld . "\n";

// Check if there are any cameras with server_id NULL
\$cctvCountNull = DB::table("cctvs")->whereNull("server_id")->count();
echo "CCTV count for server_id NULL: " . \$cctvCountNull . "\n";

