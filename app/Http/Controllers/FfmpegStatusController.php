<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class FfmpegStatusController extends Controller
{
    public function index()
    {
        // Variabel untuk menyimpan IP yang terdeteksi (untuk debug jika data kosong)
        $detectedIp = 'Unknown';
        
        try {
            // 1. Cek Koneksi DB & Filter Kamera Aktif
            $query = Cctv::where('status', 'online');

            // --- LOGIKA MULTI-SERVER OTOMATIS ---
            
            // Prioritas 1: Ambil dari file .env (Jika diset manual)
            // Tambahkan baris MY_SERVER_IP=10.69.69.21 di .env jika auto-detect gagal
            $myIp = env('MY_SERVER_IP');

            // Prioritas 2: Auto Detect IP Address Server
            if (!$myIp) {
                // SERVER_ADDR biasanya tersedia di Nginx/Apache
                $myIp = request()->server('SERVER_ADDR');
            }

            // Prioritas 3: Fallback ke Hostname (Jika jalan via CLI/Artisan)
            if (!$myIp) {
                $myIp = gethostbyname(gethostname());
            }
            
            $detectedIp = $myIp;

            // FIX: Menggunakan kolom 'ip_address' sesuai skema tabel 'servers'
            $query->whereHas('server', function($q) use ($myIp) {
                $q->where('ip_address', $myIp);
            });
            
            $cctvs = $query->with('building')->orderBy('nama_cctv')->get();
            
            // --- DEBUG MODE: JIKA DATA KOSONG ---
            // Tampilkan pesan error agar user tahu kenapa kosong
            if ($cctvs->isEmpty()) {
                return "<div style='font-family:sans-serif; padding:20px; border:1px solid orange; background:#fff8e1; color:#bf360c;'>
                        <h3>⚠️ Dashboard Kosong (No Data)</h3>
                        <p>Sistem tidak menemukan kamera yang terhubung ke server ini.</p>
                        <hr>
                        <strong>Info Debugging:</strong><br>
                        <ul>
                            <li>IP Server Terdeteksi: <code><strong>{$detectedIp}</strong></code></li>
                            <li>Status Kamera Dicari: <code>online</code></li>
                        </ul>
                        <strong>Solusi:</strong><br>
                        1. Cek tabel <code>servers</code> di database, pastikan kolom <code>ip_address</code> isinya sama dengan <strong>{$detectedIp}</strong>.<br>
                        2. Pastikan relasi CCTV ke Server sudah benar (server_id sesuai).<br>
                        3. Atau set manual IP di file <code>.env</code>: <code>MY_SERVER_IP=10.69.69.xx</code>
                        </div>";
            }

        } catch (\Throwable $e) {
            return "Fatal Error DB: " . $e->getMessage();
        }

        $statusData = [];
        $todayFolder = storage_path('app/public/recordings/' . now()->format('Y-m-d'));

        // 2. CEK PERMISSION FATAL
        // Ini memastikan folder bisa dibaca oleh www-data
        if (File::exists($todayFolder)) {
            if (!is_readable($todayFolder)) {
                return "<div style='color:red; font-family:monospace; padding:20px; border:1px solid red; background:#ffebeb;'>
                        <strong>CRITICAL PERMISSION ERROR:</strong><br>
                        Web Server (www-data) dilarang membaca folder: <code>{$todayFolder}</code>.<br><br>
                        
                        <strong>Penyebab:</strong><br>
                        Folder project ada di <code>/home/aay/...</code>. Secara default Linux memblokir user lain (www-data) untuk masuk ke /home/user.<br><br>
                        
                        <strong>Solusi Cepat (Terminal):</strong><br>
                        <code>sudo chmod +x /home/aay</code><br>
                        <code>sudo chmod +x /home/aay/cctv-unpad</code>
                        </div>";
            }
        }

        foreach ($cctvs as $cctv) {
            $isRecording = false;
            $fileSize = '0 KB';
            $filename = '-';
            $lastUpdateText = 'Belum ada rekaman hari ini';
            
            $namaGedung = $cctv->building ? $cctv->building->nama_gedung : '-';

            try {
                // LOGIC PENCARIAN FILE (Native PHP agar lebih robust)
                if (is_dir($todayFolder) && is_readable($todayFolder)) {
                    
                    // Pola pencarian file (Support format Go2RTC & Manual)
                    // Gunakan @ untuk menekan error permission level file
                    $pattern1 = $todayFolder . '/camera_' . $cctv->id . '_*.mp4';
                    $pattern2 = $todayFolder . '/cam_' . $cctv->id . '_*.mp4';
                    
                    $files = array_merge(@glob($pattern1) ?: [], @glob($pattern2) ?: []);

                    if (!empty($files)) {
                        // Ambil file terbaru (manual loop anti-crash)
                        $latestFile = null;
                        $latestTime = 0;

                        foreach ($files as $f) {
                            $mtime = @filemtime($f);
                            if ($mtime > $latestTime) {
                                $latestTime = $mtime;
                                $latestFile = $f;
                            }
                        }

                        if ($latestFile) {
                            $lastWrite = Carbon::createFromTimestamp($latestTime);
                            
                            // Toleransi 60 detik untuk dianggap "Running"
                            if ($lastWrite->diffInSeconds(now()) < 60) {
                                $isRecording = true;
                            }
                            $lastUpdateText = $lastWrite->diffForHumans();

                            $size = @filesize($latestFile);
                            $fileSize = number_format($size / 1024 / 1024, 2) . ' MB';
                            $filename = basename($latestFile);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Jangan crash dashboard jika 1 kamera error logic
                $filename = "Error Logic: " . $e->getMessage();
            }

            // PERBAIKAN: Cast ke (object) agar Blade bisa baca $cam->name
            $statusData[] = (object) [
                'id' => $cctv->id,
                'name' => $cctv->nama_cctv,
                'building' => $namaGedung,
                'is_recording' => $isRecording,
                'last_update' => $lastUpdateText,
                'file_size' => $fileSize,
                'filename' => $filename
            ];
        }

        return view('monitoring.ffmpeg', compact('statusData'));
    }
}