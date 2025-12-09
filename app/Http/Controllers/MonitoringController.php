<?php
namespace App\Http\Controllers;
use App\Models\Cctv;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function index(): View
    {
        $cctvs = Cctv::accessibleByAuth()
                     ->with('building')
                     ->orderBy('nama_cctv')
                     ->get();
        return view('monitoring.index', compact('cctvs'));
    }

    public function getTimelineJson(Request $request, $cctvId)
    {
        // 1. Ambil Tanggal (Default Hari Ini)
        $date = $request->input('date', now()->format('Y-m-d'));
        $path = storage_path("app/public/recordings/{$date}");

        if (!\Illuminate\Support\Facades\File::exists($path)) {
            return response()->json([]);
        }
        $cctvInfo = Cctv::with('building')->find($cctvId);
        $files = glob("{$path}/cam_{$cctvId}_*.mp4");
        $segments = [];
        foreach ($files as $file) {
            $filename = basename($file);
            // Parse: cam_1_2025-12-03_08-15-00.mp4
            if (preg_match('/_(\d{2}-\d{2}-\d{2})\.mp4$/', $filename, $matches)) {
                $timeParts = explode('-', $matches[1]); // [08, 15, 00]
                // Konversi ke Detik (INT)
                $h = (int)$timeParts[0];
                $m = (int)$timeParts[1];
                $s = (int)$timeParts[2];
                $startSeconds = ($h * 3600) + ($m * 60) + $s;
                $duration = 900; // 15 menit fix
                $segments[] = [
                    'start' => $startSeconds,
                    'duration' => $duration,
                    'human_start' => sprintf("%02d:%02d", $h, $m),
                    // Gunakan aset URL yang valid
                    'url' => $cctvInfo->getRecordingUrl($date, $filename),
                    // Info tambahan untuk player
                    'cctv_name' => $cctvInfo->nama_cctv ?? 'Camera',
                    'building_name' => $cctvInfo->building->nama_gedung ?? '-',
                    'faculty_name' => $cctvInfo->building->fakultas ?? '-'
                ];
            }
        }
       
        usort($segments, fn($a, $b) => $a['start'] <=> $b['start']);
        return response()->json($segments);
    }
}