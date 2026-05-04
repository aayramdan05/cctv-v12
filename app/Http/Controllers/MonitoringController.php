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
                     ->with(['building', 'server'])
                     ->orderBy('nama_cctv')
                     ->get();
        return view('monitoring.index', compact('cctvs'));
    }

    public function getTimelineJson(Request $request, $cctvId)
    {
        // 1. Ambil Tanggal (Default Hari Ini)
        $date = $request->input('date', now()->format('Y-m-d'));
        $cctvInfo = Cctv::with('building')->find($cctvId);

        if (!$cctvInfo) {
            return response()->json([]);
        }

        // Ambil riwayat rekaman dari Database
        $recordings = \App\Models\Recording::where('cctv_id', $cctvId)
            ->where('date', $date)
            ->orderBy('start_time')
            ->get();

        $segments = [];
        foreach ($recordings as $rec) {
            // Kalkulasi jam dan menit dari start_time (seconds dari tengah malam)
            $h = floor($rec->start_time / 3600);
            $m = floor(($rec->start_time % 3600) / 60);

            $segments[] = [
                'start' => $rec->start_time,
                'duration' => $rec->duration,
                'human_start' => sprintf("%02d:%02d", $h, $m),
                // Gunakan fungsi getRecordingUrl untuk mendukung Multi-Node Path
                'url' => $cctvInfo->getRecordingUrl($date, $rec->filename),
                // Info tambahan untuk player
                'cctv_name' => $cctvInfo->nama_cctv ?? 'Camera',
                'building_name' => $cctvInfo->building->nama_gedung ?? '-',
                'faculty_name' => $cctvInfo->building->fakultas ?? '-',
                'size_mb' => $rec->size_mb
            ];
        }
       
        return response()->json($segments);
    }
}