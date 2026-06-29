<?php
namespace App\Http\Controllers;
use App\Models\Cctv;
use App\Models\Building;
use App\Models\LayoutPreset;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $query = Cctv::accessibleByAuth()
                     ->with(['building', 'server'])
                     ->orderBy('nama_cctv');

        // Filter by Node (Server)
        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        // Filter by Penempatan (Indoor/Outdoor)
        if ($request->filled('penempatan')) {
            $query->where('penempatan', $request->penempatan);
        }

        // Filter by Fakultas (via Building)
        if ($request->filled('faculty')) {
            $query->whereHas('building', function($q) use ($request) {
                $q->where('fakultas', $request->faculty);
            });
        }

        // Filter by Building
        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }

        // Filter by specific CCTV (from Intelligence Event)
        if ($request->filled('cctv_id')) {
            $query->where('id', $request->cctv_id);
        }

        $cctvs = $query->get();
        $servers = \App\Models\Server::all();
        $buildings = \App\Models\Building::orderBy('nama_gedung')->get();
        $faculties = \App\Models\Building::select('fakultas')->distinct()->whereNotNull('fakultas')->orderBy('fakultas')->pluck('fakultas');

        return view('monitoring.index', compact('cctvs', 'servers', 'buildings', 'faculties'));
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
            ->where('size_mb', '>', 0) // Sembunyikan yang masih 0 MB
            ->orderBy('start_time')
            ->get();

        // Ambil riwayat kejadian (Motion) dengan range waktu yang tepat (WIB)
        $startDay = \Carbon\Carbon::parse($date, 'Asia/Jakarta')->startOfDay();
        $endDay = \Carbon\Carbon::parse($date, 'Asia/Jakarta')->endOfDay();

        $events = \App\Models\CameraEvent::where('cctv_id', $cctvId)
            ->whereBetween('event_time', [$startDay, $endDay])
            ->get()
            ->map(function($ev) {
                $time = \Carbon\Carbon::parse($ev->event_time)->timezone('Asia/Jakarta');
                return [
                    'start' => ($time->hour * 3600) + ($time->minute * 60) + $time->second,
                    'type' => $ev->event_type
                ];
            });

        $segments = [];
        foreach ($recordings as $rec) {
            $h = floor($rec->start_time / 3600);
            $m = floor(($rec->start_time % 3600) / 60);

            // Cek apakah ada motion di dalam rentang waktu rekaman ini
            $motionEventsCount = $events->filter(function($ev) use ($rec) {
                return $ev['start'] >= $rec->start_time && $ev['start'] <= ($rec->start_time + $rec->duration);
            })->count();

            // Asumsi 1 event = 10 detik gerakan (karena cooldown agent 10s)
            $motionSeconds = $motionEventsCount * 10;
            $motionPercentage = 0;
            if ($rec->duration > 0) {
                $motionPercentage = min(100, round(($motionSeconds / $rec->duration) * 100));
            }

            $segments[] = [
                'start' => $rec->start_time,
                'duration' => $rec->duration,
                'human_start' => sprintf("%02d:%02d", $h, $m),
                'url' => $cctvInfo->getRecordingUrl($date, $rec->filename),
                'has_motion' => $motionEventsCount > 0, // Tanda motion
                'motion_percentage' => $motionPercentage,
                'size_mb' => $rec->size_mb
            ];
        }
       
        return response()->json([
            'segments' => $segments,
            'events' => $events
        ]);
    }

    public function getPresets()
    {
        $presets = LayoutPreset::where('user_id', auth()->id())->orderBy('name')->get();
        return response()->json($presets);
    }

    public function savePreset(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grid_size' => 'required|integer|in:1,4,9',
            'slots' => 'required|array',
        ]);

        $preset = LayoutPreset::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'grid_size' => $request->grid_size,
            'slots' => $request->slots,
        ]);

        return response()->json($preset);
    }

    public function deletePreset(LayoutPreset $preset)
    {
        if ($preset->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $preset->delete();

        return response()->json(['success' => true]);
    }
}