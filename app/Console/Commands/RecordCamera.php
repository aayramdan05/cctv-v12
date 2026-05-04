<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cctv;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class RecordCamera extends Command
{
    protected $signature = 'cctv:record {cctv_id} {--duration=900}'; 
    protected $description = 'Merekam dengan fitur Auto-Reconnect, Auto-Merge, & Anti-Corrupt (TS)';

    public function handle()
    {
        $id = $this->argument('cctv_id');
        $totalDuration = (int) $this->option('duration'); 

        $cctv = Cctv::find($id);

        if (!$cctv) {
            $this->error("Kamera ID {$id} tidak ditemukan!");
            return;
        }

        // Setup Folder
        $dateFolder = now()->format('Y-m-d');
        $folderPath = storage_path("app/public/recordings/{$dateFolder}");
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0775, true);
        }

        // Trim URL
        $rtspUrl = trim($cctv->stream_url);
        
        $recordedParts = [];
        $startTime = Carbon::now();
        $endTime = $startTime->copy()->addSeconds($totalDuration);
        
        $finalFilename = "cam_{$cctv->id}_" . $startTime->format('Y-m-d_H-i-s') . ".mp4";
        $finalPath = "{$folderPath}/{$finalFilename}";

        $this->info("📹 Mulai Merekam: {$cctv->nama_cctv}");
        $this->info("🎯 Target Selesai: " . $endTime->format('H:i:s'));

        // --- 1. PROSES REKAMAN (LOOPING) ---
        $partCounter = 1;

        // --- PENGATURAN GOP/FPS DINONAKTIFKAN (Menggunakan -c copy) ---
        // $FRAME_RATE = 15; 
        // $GOP_SIZE = 15;   
        // --------------------------------------------------------------

        while (Carbon::now()->lessThan($endTime)) {
            $remainingSeconds = Carbon::now()->diffInSeconds($endTime, false);
            
            if ($remainingSeconds < 5) break; 

            $tempFilename = "temp_{$partCounter}_" . uniqid() . ".ts";
            $tempPath = "{$folderPath}/{$tempFilename}";

            $this->line("⏺️ Merekam part {$partCounter} (Target: {$remainingSeconds}s, Mode: COPY)..."); // Updated log message
            
            $command = [
                'ffmpeg', '-y', 
                // PERBAIKAN STABILITAS INPUT RTSP (TETAP)
                '-rtsp_transport', 'tcp', 
                '-probesize', '5M', 
                '-analyzeduration', '5M', 
                '-i', $rtspUrl,
                
                '-t', $remainingSeconds,
                '-f', 'mpegts',
                
                // --- PENGATURAN KODEK: KEMBALI KE -c copy (CPU RENDAH) ---
                '-c', 'copy', 
                '-map', '0:v:0', 
                '-map', '0:a?', 
                // ---------------------------------------------------------
                
                $tempPath
            ];

            // EKSEKUSI
            $process = Process::timeout($remainingSeconds + 10)->run($command);

            if (File::exists($tempPath) && File::size($tempPath) > 1024 * 50) { 
                $recordedParts[] = $tempPath;
                $this->info("✅ Part {$partCounter} tersimpan (". round(File::size($tempPath) / 1024 / 1024, 2) . " MB).");
            } else {
                $this->warn("⚠️ Gagal/Putus. File terlalu kecil atau hilang. Menghapus file kecil...");
                if(File::exists($tempPath)) File::delete($tempPath);
                
                $this->warn("⏳ Retry 5 detik...");
                sleep(5); 
            }

            $partCounter++;
        }

        // --- 2. PROSES PENGGABUNGAN (MERGE & CONVERT TO MP4) ---
        $count = count($recordedParts);

        if ($count === 0) {
            $this->error("❌ Gagal total. Tidak ada file yang terekam dengan ukuran memadai.");
            return;
        }

        $this->info("🔄 Menggabungkan {$count} pecahan file .ts menjadi .mp4 final...");
        
        $listContent = "";
        foreach ($recordedParts as $part) {
            $listContent .= "file '{$part}'\n";
        }
        $listTxtPath = "{$folderPath}/list_" . uniqid() . ".txt";
        File::put($listTxtPath, $listContent);

        // Command Concat dan Convert ke MP4
        $concatCmd = [
            'ffmpeg', '-y', '-hide_banner', '-loglevel', 'error',
            '-f', 'concat',
            '-safe', '0',
            '-i', $listTxtPath,
            '-c', 'copy', 
            '-movflags', '+faststart',
            $finalPath
        ];

        $process = Process::run($concatCmd);

        // --- PEMBERSIHAN ---
        File::delete($listTxtPath); 
        File::delete($recordedParts); 
        // -------------------

        if ($process->successful()) {
            $this->info("✅ Penggabungan Sukses!");
        } else {
            $this->error("❌ Gagal menggabungkan file.");
            $this->error($process->errorOutput());
        }

        if (File::exists($finalPath)) {
            $fileSizeMb = round(File::size($finalPath) / 1024 / 1024, 2);
            $this->info("🏁 FINISH: {$finalFilename} (Size: {$fileSizeMb} MB)");

            // SIMPAN KE DATABASE BARU
            try {
                $startSeconds = $startTime->hour * 3600 + $startTime->minute * 60 + $startTime->second;
                
                \App\Models\Recording::create([
                    'cctv_id' => $cctv->id,
                    'date' => $dateFolder,
                    'filename' => $finalFilename,
                    'start_time' => $startSeconds,
                    'duration' => $totalDuration,
                    'size_mb' => $fileSizeMb
                ]);
                $this->info("💾 Berhasil mencatat {$finalFilename} ke database master.");
            } catch (\Exception $e) {
                $this->error("❌ Gagal mencatat ke database: " . $e->getMessage());
            }
        }
    }
}