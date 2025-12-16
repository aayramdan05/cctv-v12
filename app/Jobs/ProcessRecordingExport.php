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
use App\Notifications\ExportReady;

class ProcessRecordingExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $cctvId;
    protected $date;
    protected $startTimeStr;
    protected $endTimeStr;

    // Timeout 1 jam (Cukup karena proses copy sangat cepat)
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
        // 1. Tentukan Range Waktu User
        $reqStart = Carbon::createFromFormat('Y-m-d H:i', "{$this->date} {$this->startTimeStr}");
        $reqEnd = Carbon::createFromFormat('Y-m-d H:i', "{$this->date} {$this->endTimeStr}");

        $path = storage_path("app/public/recordings/{$this->date}");
        
        if (!File::exists($path)) return;

        // Folder temp khusus job ini
        $jobId = $this->job ? $this->job->getJobId() : uniqid();
        $tempDir = storage_path("app/public/temp_export/{$jobId}");
        
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0775, true);
        }

        $files = File::files($path);
        $filesToZip = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            // Regex match: cam_1_2025-12-16_08-15-00.mp4
            if (preg_match('/cam_(\d+)_(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})/', $filename, $matches)) {
                $fileCamId = $matches[1];
                if ($fileCamId != $this->cctvId) continue;

                $timePart = str_replace('-', ':', $matches[3]);
                $fileStart = Carbon::createFromFormat('Y-m-d H:i:s', "{$this->date} $timePart");
                $fileEnd = $fileStart->copy()->addSeconds(900); // Asumsi file 15 menit

                // LOGIKA IRISAN WAKTU
                $trimStart = $fileStart->max($reqStart);
                $trimEnd = $fileEnd->min($reqEnd);

                // Jika ada irisan waktu yang valid
                if ($trimStart < $trimEnd) {
                    
                    // Hitung Offset & Durasi
                    $seekSeconds = $fileStart->diffInSeconds($trimStart);
                    $durationSeconds = $trimStart->diffInSeconds($trimEnd);

                    $trimFilename = "Cut_" . $trimStart->format('H-i-s') . "_to_" . $trimEnd->format('H-i-s') . ".mp4";
                    $outputPath = "{$tempDir}/{$trimFilename}";

                    // --- PROSES POTONG CEPAT (STREAM COPY) ---
                    // -ss sebelum -i mempercepat seeking (input seeking)
                    // -c copy memastikan tidak ada re-encoding (CPU ringan)
                    $cmd = [
                        'ffmpeg', '-y', '-hide_banner', '-loglevel', 'error',
                        '-ss', $seekSeconds,
                        '-i', $file->getPathname(),
                        '-t', $durationSeconds,
                        '-c', 'copy', 
                        $outputPath
                    ];

                    Process::run($cmd);

                    if (File::exists($outputPath)) {
                        $filesToZip[] = $outputPath;
                    }
                }
            }
        }

        if (empty($filesToZip)) {
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
            foreach ($filesToZip as $filePath) {
                $zip->addFile($filePath, basename($filePath));
            }
            $zip->close();
        }

        // Cleanup temp files
        File::deleteDirectory($tempDir);

        // Notify User
        $this->user->notify(new ExportReady($zipFileName));
    }
}