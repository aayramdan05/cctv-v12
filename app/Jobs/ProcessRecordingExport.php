<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use ZipArchive;
use App\Models\User;
use App\Notifications\ExportReady; // Kita akan buat ini di langkah 2

class ProcessRecordingExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $cctvId;
    protected $date;
    protected $startTimeStr;
    protected $endTimeStr;

    // Timeout job 1 jam (biar aman untuk file besar)
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
        // --- 1. LOGIKA PENCARIAN FILE (Sama seperti sebelumnya) ---
        $filterStart = Carbon::createFromFormat('Y-m-d H:i', "{$this->date} {$this->startTimeStr}");
        $filterEnd = Carbon::createFromFormat('Y-m-d H:i', "{$this->date} {$this->endTimeStr}");

        $path = storage_path("app/public/recordings/{$this->date}");
        
        if (!File::exists($path)) {
            // Opsional: Kirim notifikasi gagal
            return;
        }

        $files = File::files($path);
        $filesToZip = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (preg_match('/cam_(\d+)_(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})/', $filename, $matches)) {
                $fileCamId = $matches[1];
                if ($fileCamId != $this->cctvId) continue;

                $timePart = str_replace('-', ':', $matches[3]);
                $fileStart = Carbon::createFromFormat('Y-m-d H:i:s', "{$this->date} $timePart");
                
                if ($fileStart->between($filterStart, $filterEnd) || $fileStart->eq($filterStart)) {
                    $filesToZip[] = $file->getPathname();
                }
            }
        }

        if (empty($filesToZip)) {
            // Opsional: Kirim notifikasi kosong
            return;
        }

        // --- 2. PROSES ZIP DI BACKGROUND ---
        $zipFileName = "Export_CAM{$this->cctvId}_{$this->date}_{$this->startTimeStr}-{$this->endTimeStr}.zip";
        // Simpan di folder exports khusus agar rapi
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

        // --- 3. KIRIM NOTIFIKASI KE USER ---
        // Kita kirim nama filenya saja, nanti controller yang handle downloadnya
        $this->user->notify(new ExportReady($zipFileName));
    }
}