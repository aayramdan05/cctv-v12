<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Cctv;
use App\Models\Server;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ---------------------------------------------------------
        // 1. TUGAS UMUM (Jalan di semua server)
        // ---------------------------------------------------------
        
        // Cek Status Koneksi (Ping RTSP) - Setiap 5 menit
        // Di arsitektur cluster, sebaiknya ini hanya dijalankan oleh Master Server 
        // atau server yang punya akses jaringan ke semua kamera.
        // Jika ingin aman, biarkan jalan di semua node (redundansi).
        $schedule->command('cctv:check-status')->everyFiveMinutes();


        // ---------------------------------------------------------
        // 2. TUGAS PEREKAMAN (Logic Multi-Server)
        // ---------------------------------------------------------
        try {
            // Ambil Identitas IP Server ini dari .env
            $myIp = env('MY_SERVER_IP');

            if ($myIp) {
                // SKENARIO: SAYA ADALAH SERVER RECORDER (NODE)
                // Cari ID Server saya di database berdasarkan IP
                $myServer = Server::where('ip_address', $myIp)->first();

                if ($myServer) {
                    // Ambil kamera yang ditugaskan KHUSUS ke server ini
                    $cctvs = Cctv::where('server_id', $myServer->id)
                                 ->where('status', 'online')
                                 ->get();

                    foreach ($cctvs as $cctv) {
                        $schedule->command("cctv:record {$cctv->id} --duration=900")
                                 ->everyFifteenMinutes()
                                 ->runInBackground()
                                 ->withoutOverlapping(15); // Expire lock after 15 mins
                    }
                    
                    // Tugas Kebersihan Disk (Hanya untuk server yang merekam)
                    $schedule->command('cctv:cleanup')->dailyAt('01:00');
                    
                    // Sync Config Go2RTC Lokal (Agar streaming lancar di node ini)
                    // Opsional: Jalankan setiap jam untuk memastikan config up-to-date
                    // $schedule->command('cctv:sync-config')->hourly();
                }
            } 
            else {
                // SKENARIO: SAYA ADALAH MASTER (WEB PORTAL) ATAU SINGLE SERVER
                // Jika tidak ada MY_RECORDER_IP, kita asumsikan mode Single Server (Legacy)
                // Atau Master Server yang tidak merekam apa-apa (kosongkan blok ini jika Master murni)
                
                // UNTUK SINGLE SERVER (Mode saat ini di laptop/dev):
                $cctvs = Cctv::where('status', 'online')->whereNull('server_id')->get(); // Atau ambil semua ->get()
                
                foreach ($cctvs as $cctv) {
                    $schedule->command("cctv:record {$cctv->id} --duration=900")
                             ->everyFifteenMinutes()
                             ->runInBackground()
                             ->withoutOverlapping(15);
                }
                
                // Cleanup lokal
                $schedule->command('cctv:cleanup')->dailyAt('01:00');
            }

        } catch (\Throwable $e) {
            // Tangkap error agar artisan tidak crash jika tabel belum migrasi
            // \Log::error("Scheduler Error: " . $e->getMessage());
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}