<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cctv;
use App\Models\Server;
use App\Models\Recording;
use Carbon\Carbon;

class FfmpegStatusController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $servers = Server::all();
        $serverStats = [];

        foreach ($servers as $server) {
            $cctvs = Cctv::where('server_id', $server->id)->get();
            $totalCctv = $cctvs->count();
            $activeStreams = 0;
            $details = [];

            foreach ($cctvs as $cctv) {
                $isRecording = false;
                $lastUpdateText = 'Never';
                $fileSize = '0 MB';
                $filename = '-';

                $latestRec = Recording::where('cctv_id', $cctv->id)
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

                $details[] = (object) [
                    'id' => $cctv->id,
                    'name' => $cctv->nama_cctv,
                    'ip' => $cctv->ip,
                    'status' => $isRecording ? 'Recording' : 'Idle',
                    'last_update' => $lastUpdateText,
                    'file_size' => $fileSize,
                    'filename' => $filename
                ];
            }

            $serverStats[] = (object) [
                'id' => $server->id,
                'name' => $server->name,
                'ip' => $server->ip_address,
                'total' => $totalCctv,
                'active' => $activeStreams,
                'details' => $details
            ];
        }

        return view('monitoring.ffmpeg', compact('serverStats'));
    }
}