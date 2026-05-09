<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cctv;
use Illuminate\Support\Facades\File;

class SyncGo2rtcConfig extends Command
{
    protected $signature = 'cctv:sync-config';
    protected $description = 'Update go2rtc.yaml dengan data kamera terbaru dari database';

    public function handle()
    {
        // 1. Lokasi File Config
        $configPath = '/etc/go2rtc.yaml'; 
        
        // Cek izin tulis
        if (!is_writable($configPath)) {
            $this->error("Tidak bisa menulis ke {$configPath}. Pastikan permission benar (chown www-data).");
            return;
        }

        // 2. Header Config Standar + TUNING HLS
        $content = "api:\n  listen: \":1984\"\n  origin: \"*\"\n\n";
        
        $content .= "rtsp:\n  listen: \":8554\"\n\n";

        // --- REVISI TUNING HLS (MODE STABIL) ---
        $content .= "hls:\n";
        // Simpan 15 segmen di playlist (15 x 6 detik = 90 detik buffer)
        // Ini menjamin player tidak akan kehabisan stok video
        $content .= "  segment_count: 15\n";     
        
        // Durasi per potong 6 detik (Standar industri HLS Apple)
        $content .= "  segment_duration: 6\n\n";  
        // ---------------------------------------

        $content .= "streams:\n";

        // 3. Ambil Semua CCTV & Buat Alias
        $cctvs = Cctv::all();

        foreach ($cctvs as $cctv) {
            $alias = "camera_{$cctv->id}";
            $url = $cctv->stream_url;
            
            // LOGIKA BUILD URL
            if (str_starts_with($url, 'http')) {
                // Kasus MJPEG HTTP (Panasonic)
                // Biarkan polos, Go2RTC handle otomatis
                $configUrl = $url;
            } else {
                // KASUS RTSP (H.264 Standard)
                // Gunakan prefix 'ffmpeg:' agar Go2RTC menggunakan engine FFmpeg.
                // Ini solusi paling stabil untuk password yang mengandung karakter spesial seperti # atau @.
                $configUrl = "ffmpeg:" . $url . "#video=copy#audio=aac#rtsp_transport=tcp";
            }
            
            // Tulis stream (pakai tanda kutip agar aman)
            $content .= "  {$alias}:\n    - \"{$configUrl}\"\n";
        }

        // 4. Tulis ke File
        File::put($configPath, $content);

        $this->info("Config berhasil diupdate! Merestart Go2RTC...");

        // 5. Restart Service Otomatis
        // Menggunakan exec sudo (Pastikan sudah setup visudo)
        exec('sudo systemctl restart go2rtc', $output, $returnCode);

        if ($returnCode === 0) {
            $this->info("✅ Go2RTC Berhasil Direstart! Kamera baru siap.");
        } else {
            $this->error("❌ Gagal Restart otomatis. Cek izin visudo.");
            // Fallback manual info
            $this->comment("Silakan jalankan manual: sudo systemctl restart go2rtc");
        }
    }
}