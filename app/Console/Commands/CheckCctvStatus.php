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

            // CEK PING (ICMP)
            // Menggunakan perintah ping sistem (Windows/Linux support)
            $wait = (PHP_OS_FAMILY === 'Windows') ? '-n 1 -w 1000' : '-c 1 -W 1';
            $cmd = "ping {$wait} " . escapeshellarg($ip);
            
            exec($cmd, $output, $resultCode);

            if ($resultCode === 0) {
                // Jika ping berhasil (exit code 0)
                $this->updateStatus($cctv, 'online');
            } else {
                // Jika ping gagal
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