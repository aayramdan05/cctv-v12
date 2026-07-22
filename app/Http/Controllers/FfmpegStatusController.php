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

    public function getNginxConfig()
    {
        $path = '/etc/nginx/sites-available/cctv-unpad';
        if (file_exists($path)) {
            $content = file_get_contents($path);
        } else {
            $content = "# File konfigurasi Nginx tidak ditemukan di:\n# $path\n\n# Memastikan Anda berada di server production (Linux).\n# Server saat ini: " . PHP_OS;
        }
        
        return response()->json(['config' => $content]);
    }

    public function backupDatabase()
    {
        $driver = config('database.default');
        $conn = config("database.connections.{$driver}");
        
        $database = $conn['database'];
        $username = $conn['username'];
        $password = $conn['password'];
        $host = $conn['host'];
        
        $date = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$database}_{$date}.sql";
        $path = storage_path("app/public/{$filename}");
        
        if ($driver === 'mysql') {
            $passwordOption = $password ? "--password={$password}" : "";
            $command = "mysqldump --user={$username} {$passwordOption} --host={$host} {$database} > \"{$path}\"";
        } elseif ($driver === 'pgsql') {
            putenv("PGPASSWORD={$password}");
            $command = "pg_dump -U {$username} -h {$host} -d {$database} > \"{$path}\"";
        } else {
            return back()->with('error', 'Unsupported database driver for backup');
        }
        
        exec($command, $output, $returnVar);
        
        if ($returnVar !== 0) {
            return back()->with('error', 'Backup gagal. Pastikan mysqldump / pg_dump terinstal di server.');
        }
        
        return response()->download($path)->deleteFileAfterSend(true);
    }
}