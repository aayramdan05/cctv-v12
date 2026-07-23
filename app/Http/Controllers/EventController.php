<?php

namespace App\Http\Controllers;

use App\Models\CameraEvent;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        // 1. Fetch ONVIF Stats (Always from total cameras)
        $totalCameras = \App\Models\Cctv::count();
        $allCameras = \App\Models\Cctv::all();
        $hasOnvifCount = $allCameras->filter(function($cam) {
            return !empty($cam->onvif_user) || !empty($cam->onvif_password);
        })->count();
        $noOnvifCount = $totalCameras - $hasOnvifCount;
        
        $cameraFilter = $request->query('cam_filter', 'all');
        $cameraQuery = \App\Models\Cctv::with('server');
        
        if ($cameraFilter === 'configured') {
            $cameraQuery->where(function($q) {
                $q->whereNotNull('onvif_user')->where('onvif_user', '!=', '')
                  ->orWhereNotNull('onvif_password')->where('onvif_password', '!=', '');
            });
        } elseif ($cameraFilter === 'missing') {
            $cameraQuery->where(function($q) {
                $q->where(function($q2) { $q2->whereNull('onvif_user')->orWhere('onvif_user', ''); })
                  ->where(function($q2) { $q2->whereNull('onvif_password')->orWhere('onvif_password', ''); });
            });
        }
        $cameras = $cameraQuery->paginate(10, ['*'], 'camera_page')->withQueryString();
        
        // 2. Fetch Events (Separated for Tabs)
        $onvifEvents = CameraEvent::with(['cctv.building'])
            ->where('event_type', 'onvif')
            ->latest()
            ->paginate(20, ['*'], 'onvif_page')
            ->withQueryString();

        $intelEvents = CameraEvent::with(['cctv.building'])
            ->where('event_type', '!=', 'onvif')
            ->latest()
            ->paginate(20, ['*'], 'intel_page')
            ->withQueryString();

        $activeTab = $request->has('intel_page') ? 'intelligence' : 'onvif';

        return view('events.index', compact(
            'onvifEvents', 
            'intelEvents',
            'cameras',
            'totalCameras',
            'hasOnvifCount',
            'noOnvifCount',
            'activeTab',
            'cameraFilter'
        ));
    }

    public function exportCsv(Request $request)
    {
        $type = $request->query('type', 'onvif');
        $query = CameraEvent::with(['cctv.building']);
        
        if ($type === 'onvif') {
            $query->where('event_type', 'onvif');
        } else {
            $query->where('event_type', '!=', 'onvif');
        }

        $events = $query->latest()->get();
        
        $filename = "export_events_{$type}_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['ID', 'Waktu', 'Kamera', 'Lokasi', 'Tipe Event', 'Status Read'];

        $callback = function() use($events, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($events as $event) {
                $row = [
                    $event->id,
                    $event->created_at->format('Y-m-d H:i:s'),
                    $event->cctv->nama_cctv ?? 'Unknown',
                    $event->cctv->building->name ?? '-',
                    $event->event_type,
                    $event->is_read ? 'Read' : 'Unread'
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function markAsRead($id)
    {
        CameraEvent::where('id', $id)->update(['is_read' => true]);
        return back();
    }

    public function markAllRead()
    {
        CameraEvent::where('is_read', false)->update(['is_read' => true]);
        return back()->with('success', 'Semua event telah ditandai sebagai dibaca.');
    }
}
