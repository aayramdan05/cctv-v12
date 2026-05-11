<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Models\Building;
use App\Models\Cctv;
use Illuminate\Support\Facades\Process;
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CctvMigrationController extends Controller
{
    public function index()
    {
        abort_if(auth()->user()->role !== 'admin', 403);
        return view('cctvs.migration');
    }

    public function downloadTemplate()
    {
        abort_if(auth()->user()->role !== 'admin', 403);
        
        $templateData = [
            [
                'Fakultas' => 'Kedokteran',
                'Gedung' => 'Gedung A',
                'Kode CCTV' => 'CAM.KED-A.01',
                'Nama CCTV' => 'Lobby Utama',
                'IP Address' => '10.69.69.100',
                'Merk' => 'Hikvision',
                'Username' => 'admin',
                'Password' => 'password123'
            ],
            [
                'Fakultas' => 'Kedokteran',
                'Gedung' => 'Gedung B',
                'Kode CCTV' => 'CAM.KED-B.01',
                'Nama CCTV' => 'Parkiran Belakang',
                'IP Address' => '10.69.69.101',
                'Merk' => 'Dahua',
                'Username' => 'admin',
                'Password' => 'admin123'
            ],
        ];

        // Buat Data Referensi Gedung
        $buildings = Building::orderBy('fakultas')->orderBy('nama_gedung')->get();
        $buildingData = [];
        foreach ($buildings as $b) {
            $kode = $b->kode_gedung ?? 'KODE';
            $buildingData[] = [
                'Fakultas' => $b->fakultas,
                'Gedung' => $b->nama_gedung,
                'Kode Gedung' => $kode,
                'Contoh Kode CCTV' => "CAM.{$kode}.01"
            ];
        }

        // Data Referensi Merk & Format RTSP
        $merkData = [
            ['Merk' => 'Hikvision', 'Format Otomatis' => 'rtsp://IP:554/Streaming/Channels/101'],
            ['Merk' => 'Dahua / IMOU', 'Format Otomatis' => 'rtsp://IP:554/cam/realmonitor?channel=1&subtype=0'],
            ['Merk' => 'TP-Link VIGI', 'Format Otomatis' => 'rtsp://IP:554/stream1'],
            ['Merk' => 'Uniview (UNV)', 'Format Otomatis' => 'rtsp://IP:554/unicast/c1/s0/live'],
            ['Merk' => 'Ezviz', 'Format Otomatis' => 'rtsp://IP:554/h264/ch1/main/av_stream'],
            ['Merk' => 'Bardi / Tuya', 'Format Otomatis' => 'rtsp://IP:554/live/ch0'],
            ['Merk' => 'SPC', 'Format Otomatis' => 'rtsp://IP:554/live/ch00_0'],
            ['Merk' => 'Hanwha / Samsung', 'Format Otomatis' => 'rtsp://IP:554/snw/live/cam/realmonitor'],
            ['Merk' => 'Generic / ONVIF', 'Format Otomatis' => 'rtsp://IP:554/live (Default)'],
        ];

        // Fallback jika belum ada gedung sama sekali
        if (empty($buildingData)) {
            $buildingData[] = [
                'Fakultas' => 'Belum ada data gedung di sistem',
                'Gedung' => '-',
                'Kode Gedung' => '-',
                'Contoh Kode CCTV' => '-'
            ];
        }

        $sheets = new \Rap2hpoutre\FastExcel\SheetCollection([
            'Template Input' => $templateData,
            'Referensi Gedung' => $buildingData,
            'Referensi Merk' => $merkData
        ]);

        return (new FastExcel($sheets))->download('Template_Migrasi_CCTV.xlsx');
    }

    public function import(Request $request)
    {
        abort_if(auth()->user()->role !== 'admin', 403);

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,csv'
        ]);

        $file = $request->file('excel_file');
        
        // Baca data excel
        $collection = (new FastExcel)->import($file);
        
        if ($collection->isEmpty()) {
            return back()->with('error', 'File Excel kosong!');
        }

        // Siapkan penampung data untuk di-test dan hasil feedback
        $feedbacks = [];
        $testsToRun = [];

        foreach ($collection as $index => $row) {
            $rowNum = $index + 2; 

            $fakultas = $row['Fakultas'] ?? '';
            $namaGedung = $row['Gedung'] ?? '';
            $kodeCctv = $row['Kode CCTV'] ?? '';
            $namaCctv = $row['Nama CCTV'] ?? '';
            $ipAddress = $row['IP Address'] ?? '';
            $merk = $row['Merk'] ?? '';
            $username = (string)($row['Username'] ?? '');
            $password = (string)($row['Password'] ?? '');

            $feedbackRow = $row;

            if (empty($kodeCctv) || empty($ipAddress)) {
                $feedbackRow['Status'] = 'Gagal';
                $feedbackRow['Alasan'] = 'Kode CCTV dan IP Address wajib diisi.';
                $feedbacks[] = $feedbackRow;
                continue;
            }

            if (Cctv::where('kode_cctv', $kodeCctv)->exists()) {
                $feedbackRow['Status'] = 'Gagal';
                $feedbackRow['Alasan'] = 'Kode CCTV sudah ada di database.';
                $feedbacks[] = $feedbackRow;
                continue;
            }

            $building = Building::where('fakultas', $fakultas)
                                ->where('nama_gedung', $namaGedung)
                                ->first();

            if (!$building) {
                $feedbackRow['Status'] = 'Gagal';
                $feedbackRow['Alasan'] = "Gedung '$namaGedung' di Fakultas '$fakultas' tidak ditemukan.";
                $feedbacks[] = $feedbackRow;
                continue;
            }

            // Auto-Generate RTSP URL
            $url = $this->generateRtspUrl($merk, $ipAddress);

            $testUrl = $url;
            if (!empty($username) && !empty($password)) {
                $userEnc = rawurlencode($username);
                $passEnc = rawurlencode($password);
                $cleanPath = substr($url, 7); // buang rtsp://
                $testUrl = "rtsp://{$userEnc}:{$passEnc}@{$cleanPath}";
            }

            $testsToRun[] = [
                'feedback_index' => count($feedbacks),
                'feedback_row' => $feedbackRow, 
                'building_id' => $building->id,
                'kode_cctv' => $kodeCctv,
                'nama_cctv' => $namaCctv,
                'ip_address' => $ipAddress,
                'rtsp_url' => $url,
                'username' => $username,
                'password' => $password,
                'test_url' => $testUrl,
                'temp_image_path' => storage_path('app/public/temp/migrasi_' . time() . '_' . $kodeCctv . '.jpg')
            ];

            $feedbacks[] = null;
        }

        if (!file_exists(storage_path('app/public/temp'))) {
            mkdir(storage_path('app/public/temp'), 0775, true);
        }

        $chunks = array_chunk($testsToRun, 10);
        $successCount = 0;
        $failCount = 0;

        foreach ($chunks as $chunk) {
            $poolResults = Process::pool(function (Pool $pool) use ($chunk) {
                foreach ($chunk as $test) {
                    $pool->as($test['kode_cctv'])->command([
                        'ffmpeg', '-y', '-rtsp_transport', 'tcp', '-timeout', '8000000',
                        '-i', $test['test_url'], '-ss', '00:00:01', '-frames:v', '1',
                        '-q:v', '2', $test['temp_image_path']
                    ]);
                }
            })->start()->wait();

            foreach ($chunk as $test) {
                $kode = $test['kode_cctv'];
                $result = $poolResults[$kode];
                $feedbackIndex = $test['feedback_index'];
                $feedbackRow = $test['feedback_row'];

                if ($result->successful() && file_exists($test['temp_image_path'])) {
                    Cctv::create([
                        'building_id' => $test['building_id'],
                        'kode_cctv' => $test['kode_cctv'],
                        'nama_cctv' => $test['nama_cctv'],
                        'ip' => $test['ip_address'],
                        'rtsp_url' => $test['rtsp_url'],
                        'rtsp_user' => $test['username'],
                        'rtsp_password' => $test['password'],
                        'onvif_user' => $test['username'],     
                        'onvif_password' => $test['password'], 
                        'status' => 'online'
                    ]);

                    $feedbackRow['Status'] = 'Sukses';
                    $feedbackRow['Alasan'] = 'Koneksi Berhasil, Data Disimpan.';
                    $successCount++;
                } else {
                    $feedbackRow['Status'] = 'Gagal';
                    $feedbackRow['Alasan'] = 'Gagal terhubung ke Kamera (Timeout/Password Salah).';
                    $failCount++;
                }

                if (file_exists($test['temp_image_path'])) {
                    @unlink($test['temp_image_path']);
                }

                $feedbacks[$feedbackIndex] = $feedbackRow;
            }
        }

        $feedbackFileName = 'Feedback_Migrasi_' . date('Ymd_His') . '.xlsx';
        $feedbackPath = storage_path('app/public/temp/' . $feedbackFileName);
        
        (new FastExcel(collect($feedbacks)))->export($feedbackPath);
        
        $downloadUrl = asset('storage/temp/' . $feedbackFileName);

        return redirect()->route('cctv.migration')->with('success', "Migrasi Selesai! Berhasil: $successCount, Gagal: $failCount.")->with('feedback_url', $downloadUrl);
    }

    private function generateRtspUrl($merk, $ip)
    {
        $merk = strtolower(trim($merk));
        
        // Daftar Format RTSP Berdasarkan Merk
        $rules = [
            'hikvision' => "rtsp://{$ip}:554/Streaming/Channels/101",
            'dahua'     => "rtsp://{$ip}:554/cam/realmonitor?channel=1&subtype=0",
            'imou'      => "rtsp://{$ip}:554/cam/realmonitor?channel=1&subtype=0",
            'vigi'      => "rtsp://{$ip}:554/stream1",
            'tp-link'   => "rtsp://{$ip}:554/stream1",
            'unv'       => "rtsp://{$ip}:554/unicast/c1/s0/live",
            'uniview'   => "rtsp://{$ip}:554/unicast/c1/s0/live",
            'ezviz'     => "rtsp://{$ip}:554/h264/ch1/main/av_stream",
            'bardi'     => "rtsp://{$ip}:554/live/ch0",
            'tuya'      => "rtsp://{$ip}:554/live/ch0",
            'spc'       => "rtsp://{$ip}:554/live/ch00_0",
            'hanwha'    => "rtsp://{$ip}:554/snw/live/cam/realmonitor",
            'samsung'   => "rtsp://{$ip}:554/snw/live/cam/realmonitor",
        ];

        foreach ($rules as $key => $path) {
            if (str_contains($merk, $key)) {
                return $path;
            }
        }

        // Default RTSP jika merk tidak dikenal
        return "rtsp://{$ip}:554/live";
    }

}
