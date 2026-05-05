<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class FfmpegStatusController extends Controller
{
    public function index(Request $request)
    {
        $selectedServerId = $request->query('server_id');
        $servers = \App\Models\Server::orderBy('id')->get();

        // 1. Ambil Kamera sesuai Filter Server
        $query = Cctv::where('status', 'online')->with(['building', 'server']);

        if ($selectedServerId) {
            $query->where('server_id', $selectedServerId);
        }

        $cctvs = $query->orderBy('nama_cctv')->get();

        $statusData = [];
        $now = now();

        foreach ($cctvs as $cctv) {
            $isRecording = false;
            $fileSize = '0 MB';
            $filename = '-';
            $lastUpdateText = 'Belum ada rekaman';
            
            // Cari rekaman terbaru di DATABASE (bukan di disk, agar bisa lintas server)
            $latestRec = \App\Models\Recording::where('cctv_id', $cctv->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latestRec) {
                // Toleransi 25 menit (karena sinkronisasi biasanya per 15 menit)
                if ($latestRec->created_at->diffInMinutes($now) < 25) {
                    $isRecording = true;
                }
                $lastUpdateText = $latestRec->created_at->diffForHumans();
                $fileSize = $latestRec->size_mb . ' MB';
                $filename = $latestRec->filename;
            }

            $statusData[] = (object) [
                'id' => $cctv->id,
                'name' => $cctv->nama_cctv,
                'server_ip' => $cctv->server ? $cctv->server->ip_address : 'Master',
                'building' => $cctv->building ? $cctv->building->nama_gedung : '-',
                'is_recording' => $isRecording,
                'last_update' => $lastUpdateText,
                'file_size' => $fileSize,
                'filename' => $filename
            ];
        }

        return view('monitoring.ffmpeg', compact('statusData', 'servers', 'selectedServerId'));
    }
    }
}