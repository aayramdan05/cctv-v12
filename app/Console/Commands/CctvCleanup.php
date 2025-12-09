<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CctvCleanup extends Command
{
    protected $signature = 'cctv:cleanup';
    protected $description = 'Hapus rekaman lawas (> 7 hari)';

    public function handle()
    {
        $path = storage_path('app/public/recordings');
        $limitDate = now()->subDays(2)->format('Y-m-d'); // Hapus folder < H-7

        $dirs = File::directories($path);

        foreach ($dirs as $dir) {
            $folderName = basename($dir); // Contoh: "2025-11-20"

            // Bandingkan String Tanggal
            if ($folderName < $limitDate) {
                // Hapus Folder + Isinya (Cepat)
                File::deleteDirectory($dir);
                $this->info("Deleted archive: $folderName");
            }
        }
    }
}