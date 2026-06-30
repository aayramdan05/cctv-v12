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

        // Detect mobile device
        $userAgent = $request->header('User-Agent');
        $isMobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent) 
            || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|co|am)|semc|i230|shg\-|shar|sie(\-|m)|sk\-0|sl(id|im)|sn(    |x)|sony|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($userAgent, 0, 4));

        if ($isMobile) {
            return view('monitoring.mobile', compact('cctvs', 'servers', 'buildings', 'faculties'));
        }

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

    public function logCctvView($cctvId)
    {
        \DB::table('activity_logs')->insert([
            'user_id'       => auth()->id(),
            'activity_type' => 'cctv_view',
            'cctv_id'       => $cctvId,
            'details'       => null,
            'ip_address'    => request()->ip(),
            'created_at'    => now(),
        ]);

        return response()->json(['status' => 'logged']);
    }
}