<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Models\Cctv;
use App\Models\Server;

// --- SCHEDULER REKAMAN CCTV ---

try {
    // 1. Ambil IP Server ini dari settingan .env
    $myIp = env('MY_SERVER_IP');

    if ($myIp) {
        // 2. Cari Data Server di Database berdasarkan IP
        $myServer = Server::where('ip_address', $myIp)->first();

        if ($myServer) {
            // 3. Ambil CCTV yang statusnya ONLINE dan milik Server ini
            $cctvs = Cctv::where('server_id', $myServer->id)
                         ->where('status', 'online')
                         ->get();

            // 4. Looping: Buat jadwal untuk setiap kamera
            foreach ($cctvs as $cctv) {
                // Perintah: php artisan cctv:record {id} --duration=900
                // Jalan setiap 15 menit
                Schedule::command("cctv:record {$cctv->id} --duration=900")
                        ->everyFifteenMinutes()
                        ->runInBackground()      // Penting: Agar bisa rekam banyak kamera sekaligus
                        ->withoutOverlapping(15); // Cegah bentrok jika proses sebelumnya belum kelar
            }

            // 5. Jadwal Hapus Rekaman Lama (Setiap jam 1 pagi)
            Schedule::command('cctv:cleanup')->dailyAt('01:00');
        }
    } else {
        // Fallback jika tidak pakai sistem Multi-Server (Single Server)
        // Ambil SEMUA cctv online
        $cctvs = Cctv::where('status', 'online')->get();
        foreach ($cctvs as $cctv) {
             Schedule::command("cctv:record {$cctv->id} --duration=900")
                        ->everyFifteenMinutes()
                        ->runInBackground()
                        ->withoutOverlapping(15);
        }
        Schedule::command('cctv:cleanup')->dailyAt('01:00');
    }

} catch (\Throwable $e) {
    // Catat error jika ada masalah query database (misal saat migrasi belum siap)
    Log::error("Scheduler Error: " . $e->getMessage());
}
