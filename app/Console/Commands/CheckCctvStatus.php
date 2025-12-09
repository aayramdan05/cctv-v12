<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cctv;

class CheckCctvStatus extends Command
{
    /**
     * Nama perintah yang akan diketik di terminal.
     */
    protected $signature = 'cctv:check-status';

    /**
     * Deskripsi perintah.
     */
    protected $description = 'Mengecek status koneksi (Online/Offline) seluruh kamera CCTV';

    /**
     * Eksekusi perintah.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan status CCTV...');

        // Ambil semua CCTV
        $cctvs = Cctv::all();
        $timeout = 2; // Detik (Jangan terlalu lama agar cepat selesai)

        foreach ($cctvs as $cctv) {
            $ip = $cctv->ip;
            
            // Jika tidak ada IP, skip (anggap offline)
            if (!$ip) {
                $this->updateStatus($cctv, 'offline');
                continue;
            }

            // CEK PORT 554 (RTSP)
            // Kita gunakan fsockopen untuk mencoba koneksi TCP
            try {
                $connection = @fsockopen($ip, 554, $errno, $errstr, $timeout);

                if ($connection) {
                    // Jika berhasil connect, berarti ONLINE
                    $this->updateStatus($cctv, 'online');
                    fclose($connection);
                } else {
                    // Jika gagal connect, berarti OFFLINE
                    $this->updateStatus($cctv, 'offline');
                }
            } catch (\Exception $e) {
                $this->updateStatus($cctv, 'offline');
            }
        }

        $this->info('Pengecekan selesai.');
    }

    /**
     * Helper untuk update database hanya jika status berubah
     */
    private function updateStatus($cctv, $newStatus)
    {
        // Hanya update ke DB jika statusnya berubah (Hemat resource DB)
        if ($cctv->status !== $newStatus) {
            $cctv->update(['status' => $newStatus]);
            $this->line("{$cctv->nama_cctv} ({$cctv->ip}) berubah menjadi: {$newStatus}");
        }
    }
}