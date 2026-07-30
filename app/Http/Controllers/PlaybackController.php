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
        try {
            $date = $request->input('date', now()->format('Y-m-d'));
            $targetCamId = $request->input('cctv_id');
            
            $cctvInfo = Cctv::with('building')->find($targetCamId);
            
            if (!$cctvInfo) {
                return response()->json([]);
            }

            // Ambil dari database Recording berdasarkan tanggal (termasuk yang sedang berjalan: size_mb == 0)
            $recordings = \App\Models\Recording::where('cctv_id', $targetCamId)
                ->where('date', $date)
                ->orderBy('start_time', 'asc')
                ->get();

            $data = [];

            foreach ($recordings as $rec) {
                $start = Carbon::parse($date)->startOfDay()->addSeconds($rec->start_time)->timezone(config('app.timezone'));
                $end = $start->copy()->addSeconds($rec->duration);

                $data[] = [
                    'id' => $rec->filename,
                    'url' => $cctvInfo->getRecordingUrl($date, $rec->filename),
                    'start_time' => $start->format('H:i'),
                    'end_time' => $end->format('H:i'),
                    'start_ts' => $rec->start_time,
                    'duration' => $rec->duration,
                    'cctv_name' => $cctvInfo->nama_cctv,
                    'building_name' => $cctvInfo->building->nama_gedung ?? 'Unknown Building',
                    'faculty_name' => $cctvInfo->building->fakultas ?? 'Unknown Faculty',
                    'is_live' => ($rec->size_mb == 0)
                ];
            }

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error("Playback Error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getLiveBuffer($cctv_id, Request $request)
    {
        $cctv = Cctv::findOrFail($cctv_id);
        $date = now()->format('Y-m-d');
        
        $activeRecording = \App\Models\Recording::where('cctv_id', $cctv_id)
            ->where('date', $date)
            ->orderBy('start_time', 'desc')
            ->first();

        if (!$activeRecording || $activeRecording->size_mb > 0) {
            return response()->json(['error' => 'No active recording found'], 404);
        }

        $sourceUrl = $cctv->getRecordingUrl($date, $activeRecording->filename);
        // Karena sekarang file aktif sedang direkam dengan format .mp4.tmp (fragmented), tambahkan eksistensinya
        $sourceUrl = $sourceUrl . '.tmp';
        
        // Pastikan URL absolute (berawalan http/https) agar FFmpeg tidak mengiranya sebagai local path
        $fullSourceUrl = url($sourceUrl); 
        
        $tempFilename = 'live_buffer_' . $cctv_id . '.mp4';
        $tempPath = storage_path('app/public/live_buffers/' . $tempFilename);

        if (!\Illuminate\Support\Facades\File::exists(storage_path('app/public/live_buffers'))) {
            \Illuminate\Support\Facades\File::makeDirectory(storage_path('app/public/live_buffers'), 0755, true);
        }

        // Dapatkan semua cookie dari request browser user saat ini (untuk bypass 401 Unauthorized)
        $cookies = request()->header('Cookie');
        $headerArg = $cookies ? "Cookie: {$cookies}\r\n" : "";

        // Jalankan ekstraksi instan dari URL source dengan menyertakan Cookie Auth
        if ($headerArg) {
            $cmd = ['ffmpeg', '-y', '-hide_banner', '-loglevel', 'error', '-headers', $headerArg, '-i', $fullSourceUrl, '-c', 'copy', $tempPath];
        } else {
            $cmd = ['ffmpeg', '-y', '-hide_banner', '-loglevel', 'error', '-i', $fullSourceUrl, '-c', 'copy', $tempPath];
        }
        
        // SANGAT PENTING: Lepaskan kunci session Laravel agar tidak terjadi DEADLOCK
        // ketika FFmpeg melakukan HTTP request (dengan cookie yang sama) ke route internal!
        session()->save();
        
        $process = new \Symfony\Component\Process\Process($cmd);
        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            return response()->json(['error' => 'FFmpeg Snap failed: ' . $process->getErrorOutput()], 500);
        }

        return response()->json([
            'url' => asset('storage/live_buffers/' . $tempFilename . '?t=' . time())
        ]);
    }

    public function exportRecordings(Request $request)
    {
        // 1. Validasi
        \Illuminate\Support\Facades\Gate::authorize('playback_export');

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
        \Illuminate\Support\Facades\Gate::authorize('playback_export');

        // Download file yang sudah jadi di folder storage/app/public/exports
        $path = storage_path("app/public/exports/{$filename}");

        if (!File::exists($path)) {
            return back()->with('error', 'File belum siap atau sudah dihapus.');
        }

        return response()->download($path);
    }
}