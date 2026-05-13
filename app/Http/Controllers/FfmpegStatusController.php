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

        // 2. RESOURCE STATS PER NODE (REAL-TIME FETCH)
        $nodeStats = [];
        foreach ($servers as $srv) {
            $nodeCameras = Cctv::where('server_id', $srv->id)->get();
            $dbCount = $nodeCameras->count();
            
            // Default Values (Fallback if node offline)
            $go2rtcCount = 0;
            $ffmpegRealCount = 0;
            $nodeStatus = 'Offline';
            $go2rtcStatus = 'Offline';
            $missingIds = [];

            try {
                // A. Cek Go2RTC (Port 1984)
                $go2rtcRes = \Illuminate\Support\Facades\Http::timeout(2)->get("http://{$srv->ip_address}:1984/api/streams");
                if ($go2rtcRes->successful()) {
                    $go2rtcCount = count($go2rtcRes->json() ?: []);
                    $go2rtcStatus = 'Online';
                }

                // B. Cek Health API Baru (Port 1985)
                $healthRes = \Illuminate\Support\Facades\Http::timeout(2)->get("http://{$srv->ip_address}:1985/health");
                if ($healthRes->successful()) {
                    $healthData = $healthRes->json();
                    $ffmpegRealCount = $healthData['ffmpeg_count'] ?? 0;
                    $nodeStatus = 'Online';
                    
                    // Deteksi ID yang hilang
                    $activeIds = $healthData['active_ids'] ?? [];
                    foreach ($nodeCameras as $cam) {
                        if (!in_array($cam->id, $activeIds)) {
                            $missingIds[] = $cam->id;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Keep default values
            }

            // Hitung Storage Node (Estimasi terpakai)
            $usedMb = \App\Models\Recording::whereHas('cctv', function($q) use ($srv) {
                $q->where('server_id', $srv->id);
            })->sum('size_mb');

            $totalNodeGb = 87000;
            $usedNodeGb = 52000 + ($usedMb / 1024);
            $percent = ($usedNodeGb / $totalNodeGb) * 100;

            $nodeStats[] = (object) [
                'id' => $srv->id,
                'name' => $srv->name,
                'ip' => $srv->ip_address,
                'disk_text' => number_format($usedNodeGb / 1000, 1) . 'T / ' . ($totalNodeGb / 1000) . 'T',
                'disk_percent' => round($percent, 1),
                'bandwidth' => number_format($ffmpegRealCount * 1.5, 1) . ' Mbps',
                'db_count' => $dbCount,
                'go2rtc_count' => $go2rtcCount,
                'ffmpeg_count' => $ffmpegRealCount,
                'missing_ids' => $missingIds,
                'status' => $nodeStatus,
                'go2rtc_status' => $go2rtcStatus
            ];
        }

        return view('monitoring.ffmpeg', compact(
            'statusData', 'servers', 'buildings', 
            'selectedServerId', 'nodeStats'
        ));
    }
}