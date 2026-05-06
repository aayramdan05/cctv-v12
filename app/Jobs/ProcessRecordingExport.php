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

        $path = storage_path("app/public/recordings/{$this->date}");
        
        if (!File::exists($path)) {
            $this->user->notifications()->where('type', ExportProcessing::class)->delete();
            $this->user->notify(new ExportFailed("Folder rekaman untuk tanggal {$this->date} tidak ditemukan di server master."));
            return;
        }

        // Folder temp (hanya untuk menyimpan potongan awal/akhir)
        $jobId = $this->job ? $this->job->getJobId() : uniqid();
        $tempDir = storage_path("app/public/temp_export/{$jobId}");
        
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0775, true);
        }

        $files = File::files($path);
        // Urutkan file agar di dalam ZIP rapi
        usort($files, function($a, $b) {
            return strcmp($a->getFilename(), $b->getFilename());
        });

        // Array untuk menyimpan path file yang akan di-zip
        // Format: ['path' => '/real/path', 'name' => 'nama_di_zip.mp4']
        $filesToZip = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            // Regex match: cam_1_2025-12-16_08-15-00.mp4
            if (preg_match('/cam_(\d+)_(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})/', $filename, $matches)) {
                $fileCamId = $matches[1];
                if ($fileCamId != $this->cctvId) continue;

                $timePart = str_replace('-', ':', $matches[3]);
                $fileStart = Carbon::createFromFormat('Y-m-d H:i:s', "{$this->date} $timePart");
                $fileEnd = $fileStart->copy()->addSeconds(900); // Asumsi 15 menit

                // LOGIKA IRISAN WAKTU
                $trimStart = $fileStart->max($reqStart);
                $trimEnd = $fileEnd->min($reqEnd);

                // Jika ada irisan valid
                if ($trimStart < $trimEnd) {
                    
                    // CEK APAKAH PERLU DIPOTONG?
                    // Jika waktu trim SAMA DENGAN waktu asli file, berarti file ini UTUH.
                    // Tidak perlu FFmpeg, langsung ambil aslinya.
                    $isFullSegment = ($trimStart->eq($fileStart) && $trimEnd->eq($fileEnd));

                    if ($isFullSegment) {
                        // --- OPTIMASI: LANGSUNG MASUKKAN FILE ASLI ---
                        $filesToZip[] = [
                            'path' => $file->getPathname(),
                            'name' => $filename // Gunakan nama asli
                        ];
                    } else {
                        // --- PROSES POTONG (Hanya untuk awal/akhir) ---
                        $seekSeconds = $fileStart->diffInSeconds($trimStart);
                        $durationSeconds = $trimStart->diffInSeconds($trimEnd);

                        // Beri nama khusus agar admin tahu ini hasil potongan
                        $trimFilename = "Trim_" . $trimStart->format('H-i-s') . "_" . $filename;
                        $outputPath = "{$tempDir}/{$trimFilename}";

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
                            $filesToZip[] = [
                                'path' => $outputPath,
                                'name' => $trimFilename
                            ];
                        }
                    }
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