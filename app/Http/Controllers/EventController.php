<?php

namespace App\Http\Controllers;

use App\Models\CameraEvent;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        // 1. Fetch ONVIF Stats (Always from total cameras)
        $cameras = \App\Models\Cctv::with('server')->get();
        $totalCameras = $cameras->count();
        
        $onlineCount = $cameras->where('onvif_status', 'online')->count();
        $failedCount = $cameras->where('onvif_status', 'failed')->count();
        $unconfiguredCount = $cameras->filter(function($cam) {
            return empty($cam->onvif_user) && empty($cam->onvif_password);
        })->count();
        
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
            'onlineCount',
            'failedCount',
            'unconfiguredCount',
            'activeTab'
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
