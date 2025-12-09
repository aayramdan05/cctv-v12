<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cctv;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class RecordCamera extends Command
{
    protected $signature = 'cctv:record {cctv_id} {--duration=60}';
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

        while (Carbon::now()->lessThan($endTime)) {
            $remainingSeconds = Carbon::now()->diffInSeconds($endTime, false);
            
            if ($remainingSeconds < 5) break;

            // Kita pakai .ts agar file tidak corrupt saat di-kill paksa oleh PHP
            $tempFilename = "temp_{$partCounter}_" . uniqid() . ".ts";
            $tempPath = "{$folderPath}/{$tempFilename}";

            $this->line("⏺️  Merekam part {$partCounter} (Sisa: {$remainingSeconds}s)...");

            $command = [
                'ffmpeg', '-y', 
                '-rtsp_transport', 'tcp', 
                '-i', $rtspUrl,
                '-t', $remainingSeconds,
                '-c', 'copy', 
                $tempPath
            ];

            // EKSEKUSI
            // Timeout PHP dilebihkan 10 detik. 
            // Jika FFmpeg macet (koneksi putus), PHP akan mematikan prosesnya.
            // Karena formatnya .ts, file rekaman yang sudah tertulis tetap aman.
            $process = Process::timeout($remainingSeconds + 10)->run($command);

            if (File::exists($tempPath) && File::size($tempPath) > 0) {
                $recordedParts[] = $tempPath;
                $this->info("✅ Part {$partCounter} tersimpan.");
            } else {
                $this->warn("⚠️  Gagal/Putus. Retry 2 detik...");
                sleep(2);
            }

            $partCounter++;
        }

        // --- 2. PROSES PENGGABUNGAN (MERGE & CONVERT TO MP4) ---
        $count = count($recordedParts);

        if ($count === 0) {
            $this->error("❌ Gagal total. Tidak ada file yang terekam.");
            return;
        }

        $this->info("🔄 Menggabungkan {$count} pecahan file .ts menjadi .mp4 final...");
        
        // Buat file list.txt
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
            '-bsf:a', 'aac_adtstoasc', // Fix audio saat convert TS -> MP4
            '-movflags', '+faststart',
            $finalPath
        ];

        $process = Process::run($concatCmd);

        if ($process->successful()) {
            $this->info("✅ Penggabungan Sukses!");
            File::delete($listTxtPath);
            File::delete($recordedParts); // Hapus file .ts sementara
        } else {
            $this->error("❌ Gagal menggabungkan file.");
        }

        if (File::exists($finalPath)) {
            $this->info("🏁 FINISH: {$finalFilename}");
        }
    }
}