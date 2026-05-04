<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cctv;
use App\Models\Recording;
use Illuminate\Support\Facades\File;

class SyncRecordingsToDb extends Command
{
    protected $signature = 'cctv:sync-db';
    protected $description = 'Sinkronisasi ulang file .mp4 lama di harddisk ke tabel recordings database';

    public function handle()
    {
        $this->info("Memulai Sinkronisasi File Lama ke Database...");
        
        $basePath = storage_path("app/public/recordings");
        
        if (!File::exists($basePath)) {
            $this->error("Folder recordings tidak ditemukan.");
            return;
        }

        // Ambil semua folder tanggal (contoh: 2026-04-27)
        $dateFolders = File::directories($basePath);
        $totalInserted = 0;

        foreach ($dateFolders as $folder) {
            $date = basename($folder); // contoh: 2026-04-27
            
            // Cari semua file mp4 di dalam folder tanggal ini
            $files = glob("{$folder}/cam_*.mp4");
            
            foreach ($files as $file) {
                $filename = basename($file);
                
                // Parse format: cam_1_2025-12-03_08-15-00.mp4
                if (preg_match('/^cam_(\d+)_(\d{4}-\d{2}-\d{2})_(\d{2})-(\d{2})-(\d{2})\.mp4$/', $filename, $matches)) {
                    $cctvId = $matches[1];
                    $fileDate = $matches[2];
                    $h = (int)$matches[3];
                    $m = (int)$matches[4];
                    $s = (int)$matches[5];
                    
                    $startSeconds = ($h * 3600) + ($m * 60) + $s;
                    $fileSizeMb = round(File::size($file) / 1024 / 1024, 2);
                    
                    // Cek apakah data ini sudah ada di DB (mencegah duplikat)
                    $exists = Recording::where('cctv_id', $cctvId)
                                       ->where('date', $date)
                                       ->where('filename', $filename)
                                       ->exists();
                                       
                    if (!$exists) {
                        Recording::create([
                            'cctv_id' => $cctvId,
                            'date' => $date,
                            'filename' => $filename,
                            'start_time' => $startSeconds,
                            'duration' => 900, // asumsikan 15 menit
                            'size_mb' => $fileSizeMb
                        ]);
                        $this->line("✅ Inserted: {$filename}");
                        $totalInserted++;
                    }
                }
            }
        }
        
        $this->info("🏁 Sinkronisasi Selesai! {$totalInserted} file lama berhasil dimasukkan ke database.");
    }
}
