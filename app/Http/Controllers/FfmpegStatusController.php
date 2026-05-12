<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class FfmpegStatusController extends Controller
{
    public function index(Request $request)
    {
        $selectedServerId = $request->query('server_id');
        $buildingId = $request->query('building_id');
        $penempatan = $request->query('penempatan');
        $search = $request->query('search');

        $servers = \App\Models\Server::orderBy('id')->get();
        $buildings = \App\Models\Building::orderBy('nama_gedung')->get();

        // 1. Ambil Kamera sesuai Filter
        $query = Cctv::with(['building', 'server']);

        if ($selectedServerId) $query->where('server_id', $selectedServerId);
        if ($buildingId) $query->where('building_id', $buildingId);
        if ($penempatan) $query->where('penempatan', $penempatan);
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_cctv', 'like', "%{$search}%")
                  ->orWhere('kode_cctv', 'like', "%{$search}%")
                  ->orWhere('ip', 'like', "%{$search}%");
            });
        }

        $cctvs = $query->orderBy('nama_cctv')->get();

        $statusData = [];
        $now = now();
        $totalSizeMb = 0;
        $activeStreams = 0;

        foreach ($cctvs as $cctv) {
            $isRecording = false;
            $fileSize = '0 MB';
            $filename = '-';
            $lastUpdateText = 'Belum ada rekaman';
            
            $latestRec = \App\Models\Recording::where('cctv_id', $cctv->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latestRec) {
                if ($latestRec->created_at->diffInMinutes($now) < 25) {
                    $isRecording = true;
                    $activeStreams++;
                }
                $lastUpdateText = $latestRec->created_at->diffForHumans();
                $fileSize = $latestRec->size_mb . ' MB';
                $filename = $latestRec->filename;
                $totalSizeMb += $latestRec->size_mb;
            }

            $statusData[] = (object) [
                'id' => $cctv->id,
                'name' => $cctv->nama_cctv,
                'kode' => $cctv->kode_cctv,
                'penempatan' => $cctv->penempatan,
                'server_ip' => $cctv->server ? $cctv->server->ip_address : 'Master',
                'building' => $cctv->building ? $cctv->building->nama_gedung : '-',
                'is_recording' => $isRecording,
                'last_update' => $lastUpdateText,
                'file_size' => $fileSize,
                'filename' => $filename
            ];
        }

        // 2. RESOURCE STATS (Sidebar)
        $resources = (object) [
            'disk_usage' => number_format($totalSizeMb / 1024, 2) . ' GB',
            'bandwidth' => number_format($activeStreams * 1.8, 1) . ' Mbps',
            'ffmpeg_status' => $activeStreams > 0 ? 'Running' : 'Idle',
            'onvif_status' => Cctv::whereNotNull('onvif_user')->count() > 0 ? 'Active' : 'Standby',
            'go2rtc_status' => 'Healthy',
            'active_nodes' => \App\Models\Server::count()
        ];

        return view('monitoring.ffmpeg', compact(
            'statusData', 'servers', 'buildings', 
            'selectedServerId', 'resources'
        ));
    }
}