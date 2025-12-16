<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use App\Models\Cctv;
use App\Models\Building;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessRecordingExport;

class PlaybackController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $date = $request->input('date', now()->format('Y-m-d'));
        
        // --- 1. FILTER FAKULTAS ---
        $faculties = Building::distinct()->pluck('fakultas')->filter();
        $selectedFaculty = $request->input('faculty');
        
        // Jika Operator Fakultas login, kunci filter ke fakultas dia
        if ($user->role === 'faculty_operator') {
            $selectedFaculty = $user->faculty;
        }

        // --- 2. FILTER GEDUNG ---
        $buildingsQuery = Building::query();
        if ($selectedFaculty) {
            $buildingsQuery->where('fakultas', $selectedFaculty);
        }
        $buildings = $buildingsQuery->get();
        $selectedBuildingId = $request->input('building_id');

        // --- 3. FILTER KAMERA ---
        // Ambil CCTV berdasarkan gedung yang dipilih (atau semua jika belum pilih gedung)
        // Gunakan accessibleByAuth() agar tetap aman sesuai role
        $cctvsQuery = Cctv::accessibleByAuth()->orderBy('nama_cctv');

        if ($selectedBuildingId) {
            $cctvsQuery->where('building_id', $selectedBuildingId);
        } elseif ($selectedFaculty) {
            // Jika cuma pilih fakultas tapi belum pilih gedung, tampilkan semua cctv di fakultas itu
            $cctvsQuery->whereHas('building', function($q) use ($selectedFaculty) {
                $q->where('fakultas', $selectedFaculty);
            });
        }

        $cctvs = $cctvsQuery->get();
        
        // Default pilih kamera pertama di list jika belum ada yang dipilih
        $selectedCctvId = $request->input('cctv_id', $cctvs->first()->id ?? null);

        return view('playback.timeline', compact(
            'date', 'faculties', 'selectedFaculty', 
            'buildings', 'selectedBuildingId',
            'cctvs', 'selectedCctvId'
        ));
    }

    public function getRecordings(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $targetCamId = $request->input('cctv_id');
        
        // --- PERBAIKAN: DEFINISIKAN CCTV INFO DULU ---
        // Kita butuh info nama kamera/gedung untuk ditampilkan di playlist
        $cctvInfo = Cctv::with('building')->find($targetCamId);
        
        // Cek folder tanggal
        $path = storage_path("app/public/recordings/{$date}");
        
        // Jika kamera tidak ditemukan ATAU folder rekaman belum ada
        if (!$cctvInfo || !File::exists($path)) {
            return response()->json([]);
        }

        $files = File::files($path);
        $data = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            
            // Regex: cam_{id}_{Y-m-d_H-i-s}.mp4
            if (preg_match('/cam_(\d+)_(\d{4}-\d{2}-\d{2})_(\d{2}-\d{2}-\d{2})/', $filename, $matches)) {
                
                $fileCamId = $matches[1];
                $fileDate = $matches[2];
                
                // Filter hanya file milik kamera ini
                if ($fileCamId != $targetCamId || $fileDate !== $date) {
                    continue;
                }

                $timeStr = str_replace('-', ':', $matches[3]);
                $start = Carbon::createFromFormat('Y-m-d H:i:s', "$fileDate $timeStr", config('app.timezone'));
                $end = $start->copy()->addSeconds(900);

                $data[] = [
                    'id' => $filename,
                    'url' => $cctvInfo->getRecordingUrl($date, $filename),
                    'start_time' => $start->format('H:i'),
                    'end_time' => $end->format('H:i'),
                    'start_ts' => $start->timestamp,
                    'duration' => 900,
                    
                    // DATA DETAIL (Sekarang sudah aman karena $cctvInfo ada)
                    'cctv_name' => $cctvInfo->nama_cctv,
                    'building_name' => $cctvInfo->building->nama_gedung ?? 'Unknown Building',
                    'faculty_name' => $cctvInfo->building->fakultas ?? 'Unknown Faculty',
                ];
            }
        }

        usort($data, fn($a, $b) => $a['start_ts'] <=> $b['start_ts']);

        return response()->json($data);
    }

    public function exportRecordings(Request $request)
    {
        // 1. Validasi
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'cctv_id' => 'required',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        // 2. DISPATCH JOB (Kirim tugas ke Worker)
        // Controller langsung selesai dalam milidetik, tidak akan 502 Bad Gateway.
        ProcessRecordingExport::dispatch(
            auth()->user(),
            $request->input('cctv_id'),
            $request->input('date'),
            $request->input('start_time'),
            $request->input('end_time')
        );

        // 3. Kembali ke halaman dengan pesan sukses
        return back()->with('success', 'Permintaan Export sedang diproses di latar belakang. Silakan cek notifikasi nanti.');
    }

    public function downloadExport($filename)
    {
        if (auth()->user()->role !== 'admin') { abort(403); }

        // Download file yang sudah jadi di folder storage/app/public/exports
        $path = storage_path("app/public/exports/{$filename}");

        if (!File::exists($path)) {
            return back()->with('error', 'File belum siap atau sudah dihapus.');
        }

        return response()->download($path);
    }
}