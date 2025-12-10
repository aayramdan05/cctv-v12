<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cctv;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class RecordCamera extends Command
{
    protected $signature = 'cctv:record {cctv_id} {--duration=900}'; // Ubah default ke 900s (15m)
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
            
            // Jika sisa waktu kurang dari 5 detik, proses merge tidak efektif.
            if ($remainingSeconds < 5) break; 

            // Kita pakai .ts agar file tidak corrupt saat di-kill paksa oleh PHP
            $tempFilename = "temp_{$partCounter}_" . uniqid() . ".ts";
            $tempPath = "{$folderPath}/{$tempFilename}";

            $this->line("⏺️ Merekam part {$partCounter} (Target: {$remainingSeconds}s)...");

            $command = [
                'ffmpeg', '-y', 
                // --- PERBAIKAN STABILITAS INPUT RTSP ---
                '-rtsp_transport', 'tcp', 
                // Ambil lebih banyak data sebelum mulai (menghindari quick exit)
                '-probesize', '5M', 
                '-analyzeduration', '5M', 
                '-i', $rtspUrl,
                // ----------------------------------------
                '-t', $remainingSeconds,
                // Menggunakan output format MPEGTS
                '-f', 'mpegts',
                '-c', 'copy', 
                $tempPath
            ];

            // EKSEKUSI
            // Timeout PHP dilebihkan 10 detik.
            $process = Process::timeout($remainingSeconds + 10)->run($command);

            // Cek size file setelah proses (lebih baik daripada hanya mengecek exit code FFmpeg)
            if (File::exists($tempPath) && File::size($tempPath) > 1024 * 50) { // Minimal 50KB agar dianggap berhasil
                $recordedParts[] = $tempPath;
                $this->info("✅ Part {$partCounter} tersimpan (". round(File::size($tempPath) / 1024 / 1024, 2) . " MB).");
            } else {
                $this->warn("⚠️ Gagal/Putus. File terlalu kecil atau hilang. Menghapus file kecil...");
                // Hapus file kecil/gagal agar tidak dimasukkan ke list merge
                if(File::exists($tempPath)) File::delete($tempPath);
                
                $this->warn("⏳ Retry 5 detik...");
                sleep(5); // Tambahkan jeda lebih lama agar kamera dan jaringan sempat pulih
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

        // --- PEMBERSIHAN ---
        File::delete($listTxtPath); 
        File::delete($recordedParts); // Hapus file .ts sementara
        // -------------------

        if ($process->successful()) {
            $this->info("✅ Penggabungan Sukses!");
        } else {
            $this->error("❌ Gagal menggabungkan file.");
            $this->error($process->errorOutput());
        }

        if (File::exists($finalPath)) {
            $this->info("🏁 FINISH: {$finalFilename} (Size: " . round(File::size($finalPath) / 1024 / 1024, 2) . " MB)");
            
            // --- TODO: Jika Anda memilih Opsi Database/Webhook (Sangat Disarankan) ---
            // Panggil API Webhook/Simpan ke database di sini
            // $this->call('cctv:report-finish', ['filename' => $finalFilename, 'cctv_id' => $id]); 
        }
    }
}