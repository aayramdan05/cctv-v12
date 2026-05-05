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
            $h = floor($rec->start_time / 3600);
            $m = floor(($rec->start_time % 3600) / 60);

            $segments[] = [
                'start' => $rec->start_time,
                'duration' => $rec->duration,
                'human_start' => sprintf("%02d:%02d", $h, $m),
                'url' => $cctvInfo->getRecordingUrl($date, $rec->filename),
                'cctv_name' => $cctvInfo->nama_cctv ?? 'Camera',
                'building_name' => $cctvInfo->building->nama_gedung ?? '-',
                'faculty_name' => $cctvInfo->building->fakultas ?? '-',
                'size_mb' => $rec->size_mb
            ];
        }

        // Ambil riwayat kejadian (Motion)
        $events = \App\Models\CameraEvent::where('cctv_id', $cctvId)
            ->whereDate('event_time', $date)
            ->get()
            ->map(function($ev) {
                $time = \Carbon\Carbon::parse($ev->event_time);
                return [
                    'start' => ($time->hour * 3600) + ($time->minute * 60) + $time->second,
                    'type' => $ev->event_type
                ];
            });
       
        return response()->json([
            'segments' => $segments,
            'events' => $events
        ]);
    }
}