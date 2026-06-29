<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cctv;
use App\Models\Server;
use App\Models\Recording;
use Carbon\Carbon;

class FfmpegStatusController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $servers = Server::all();
        $serverStats = [];

        // 1. Calculate stats per server efficiently
        foreach ($servers as $server) {
            $totalCctv = Cctv::where('server_id', $server->id)->count();
            
            $activeStreams = Cctv::where('server_id', $server->id)
                ->whereHas('recordings', function ($q) use ($now) {
                    $q->where('created_at', '>=', $now->copy()->subMinutes(25));
                })->count();

            $serverStats[] = (object) [
                'id' => $server->id,
                'name' => $server->name,
                'ip' => $server->ip_address,
                'total' => $totalCctv,
                'active' => $activeStreams,
            ];
        }

        // 2. Query Detail Kamera with Pagination, Search, and Server Filter
        $cctvQuery = Cctv::with(['server']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $cctvQuery->where(function ($q) use ($search) {
                $q->where('nama_cctv', 'like', "%{$search}%")
                  ->orWhere('kode_cctv', 'like', "%{$search}%")
                  ->orWhere('ip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('server_id')) {
            $cctvQuery->where('server_id', $request->input('server_id'));
        }

        // Subquery select the latest recording information to avoid N+1 queries
        $cctvs = $cctvQuery->addSelect([
            'latest_rec_created_at' => Recording::select('created_at')
                ->whereColumn('cctv_id', 'cctvs.id')
                ->latest()
                ->take(1),
            'latest_rec_size_mb' => Recording::select('size_mb')
                ->whereColumn('cctv_id', 'cctvs.id')
                ->latest()
                ->take(1),
            'latest_rec_filename' => Recording::select('filename')
                ->whereColumn('cctv_id', 'cctvs.id')
                ->latest()
                ->take(1),
        ])->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return view('monitoring.ffmpeg', compact('serverStats', 'cctvs', 'servers'));
        }

        return view('monitoring.ffmpeg', compact('serverStats', 'cctvs', 'servers'));
    }
}