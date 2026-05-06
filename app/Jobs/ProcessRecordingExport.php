<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Carbon\Carbon;
use ZipArchive;
use App\Models\User;
use App\Models\Cctv;
use App\Models\Recording;
use App\Notifications\ExportReady;
use App\Notifications\ExportProcessing;
use App\Notifications\ExportFailed;

class ProcessRecordingExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $cctvId;
    protected $date;
    protected $startTimeStr;
    protected $endTimeStr;

    // Timeout 1 jam (Sangat cukup karena prosesnya cepat)
    public $timeout = 3600; 

    public function __construct(User $user, $cctvId, $date, $startTimeStr, $endTimeStr)
    {
        $this->user = $user;
        $this->cctvId = $cctvId;
        $this->date = $date;
        $this->startTimeStr = $startTimeStr;
        $this->endTimeStr = $endTimeStr;
    }

    public function handle(): void
    {
        // Beri notifikasi sedang berjalan
        $this->user->notify(new ExportProcessing("Memulai export rekaman untuk CAM {$this->cctvId} dari {$this->startTimeStr} - {$this->endTimeStr}..."));

        $reqStart = Carbon::createFromFormat('Y-m-d H:i', "{$this->date} {$this->startTimeStr}");
        $reqEnd = Carbon::createFromFormat('Y-m-d H:i', "{$this->date} {$this->endTimeStr}");

        $cctv = Cctv::with('server')->find($this->cctvId);
        if (!$cctv) {
            $this->user->notifications()->where('type', ExportProcessing::class)->delete();
            $this->user->notify(new ExportFailed("CCTV dengan ID {$this->cctvId} tidak ditemukan."));
            return;
        }

        // Ambil data rekaman langsung dari database berdasarkan kolom 'date'
        $recordings = Recording::where('cctv_id', $this->cctvId)
            ->where('date', $this->date)
            ->get();

        // Filter rekaman yang beririsan dengan waktu permintaan
        $recordings = $recordings->filter(function($rec) use ($reqStart, $reqEnd) {
            $recStart = Carbon::parse($this->date)->startOfDay()->addSeconds($rec->start_time);
            $recEndCarbon = $recStart->copy()->addSeconds($rec->duration);
            return $recStart < $reqEnd && $recEndCarbon > $reqStart;
        })->sortBy('start_time');

        if ($recordings->isEmpty()) {
            $this->user->notifications()->where('type', ExportProcessing::class)->delete();
            $this->user->notify(new ExportFailed("Tidak ada data rekaman video di database untuk rentang waktu tersebut pada CAM {$this->cctvId}."));
            return;
        }

        // Folder temp (untuk menyimpan hasil download dan potongan)
        $jobId = $this->job ? $this->job->getJobId() : uniqid();
        $tempDir = storage_path("app/public/temp_export/{$jobId}");
        
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0775, true);
        }

        // Array untuk menyimpan path file yang akan di-zip
        $filesToZip = [];

        foreach ($recordings as $rec) {
            $filename = $rec->filename;
            $fileStart = Carbon::parse($this->date)->startOfDay()->addSeconds($rec->start_time);
            $fileEnd = $fileStart->copy()->addSeconds($rec->duration);

            // LOGIKA IRISAN WAKTU
            $trimStart = $fileStart->max($reqStart);
            $trimEnd = $fileEnd->min($reqEnd);

            // Jika ada irisan valid
            if ($trimStart < $trimEnd) {
                // Generate URL HTTP Langsung ke IP Node (bukan via HTTPS public)
                // Ini untuk menghindari "Connection refused" pada port 443 di internal server
                if ($cctv->server) {
                    $nodeIp = $cctv->server->ip_address;
                    $sourceUrl = "http://{$nodeIp}/storage/recordings/{$this->date}/{$filename}";
                } else {
                    $sourceUrl = "http://127.0.0.1/storage/recordings/{$this->date}/{$filename}";
                }

                $seekSeconds = $fileStart->diffInSeconds($trimStart);
                $durationSeconds = $trimStart->diffInSeconds($trimEnd);

                // Beri nama khusus
                $trimFilename = "Trim_" . $trimStart->format('H-i-s') . "_" . $filename;
                $outputPath = "{$tempDir}/{$trimFilename}";

                // FFmpeg command over HTTP
                // Karena file berada di server lain, kita selalu perlu mendownloadnya ke tempDir.
                // Opsi -ss diletakkan sebelum -i agar FFmpeg melompat (seek) ke detik yang dituju lewat HTTP (sangat cepat).
                $cmd = [
                    'ffmpeg', '-y', '-hide_banner', '-loglevel', 'error'
                ];

                if ($seekSeconds > 0) {
                    $cmd[] = '-ss';
                    $cmd[] = $seekSeconds;
                }

                $cmd = array_merge($cmd, [
                    '-i', $sourceUrl,
                    '-t', $durationSeconds,
                    '-c', 'copy', 
                    $outputPath
                ]);

                $result = Process::run($cmd);

                if ($result->successful() && File::exists($outputPath)) {
                    $filesToZip[] = [
                        'path' => $outputPath,
                        'name' => $trimFilename
                    ];
                } else {
                    \Log::error("FFmpeg Failed for CAM {$this->cctvId}. URL: {$sourceUrl}");
                    \Log::error("FFmpeg Output: " . $result->errorOutput());
                }
            }
        }

        if (empty($filesToZip)) {
            $this->user->notifications()->where('type', ExportProcessing::class)->delete();
            $this->user->notify(new ExportFailed("Tidak ada rekaman video yang tersedia untuk CAM {$this->cctvId} pada jam tersebut."));
            File::deleteDirectory($tempDir);
            return;
        }

        // --- ZIP PROCESS ---
        $zipFileName = "Export_CAM{$this->cctvId}_{$this->date}_{$this->startTimeStr}-{$this->endTimeStr}.zip";
        $exportPath = storage_path("app/public/exports");
        
        if (!File::exists($exportPath)) {
            File::makeDirectory($exportPath, 0775, true);
        }

        $zipFullPath = "{$exportPath}/{$zipFileName}";

        $zip = new ZipArchive;
        if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($filesToZip as $item) {
                // Masukkan file ke zip dengan nama yang sudah ditentukan
                $zip->addFile($item['path'], $item['name']);
            }
            $zip->close();
        }

        // Cleanup temp folder (hapus potongan segmen)
        // File asli di folder recordings tidak akan terhapus karena kita hanya baca
        File::deleteDirectory($tempDir);

        // Hapus notifikasi proses sebelumnya
        $this->user->notifications()->where('type', ExportProcessing::class)->delete();

        // Notify User
        $this->user->notify(new ExportReady($zipFileName));
    }
}