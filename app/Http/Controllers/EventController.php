<?php

namespace App\Http\Controllers;

use App\Models\CameraEvent;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        // 1. Fetch ONVIF Stats & Camera Data
        $cameras = \App\Models\Cctv::with('server')->get();
        $totalCameras = $cameras->count();
        
        $onvifCameras = $cameras->filter(function($cam) {
            return !empty($cam->onvif_user) || !empty($cam->onvif_password);
        });
        
        $hasOnvifCount = $onvifCameras->count();
        $noOnvifCount = $totalCameras - $hasOnvifCount;
        
        // 2. Fetch Events (Filter by type)
        $query = CameraEvent::with(['cctv.building'])->latest();

        if ($request->filled('cctv_id')) {
            $query->where('cctv_id', $request->cctv_id);
        }

        $eventType = $request->query('type', 'onvif'); // Default to onvif
        
        if ($eventType === 'onvif') {
            $query->where('event_type', 'onvif');
        } else {
            $query->where('event_type', '!=', 'onvif'); // Everything else is intelligence/motion
        }

        $events = $query->paginate(20)->withQueryString();
        
        return view('events.index', compact(
            'events', 
            'cameras',
            'totalCameras',
            'hasOnvifCount',
            'noOnvifCount',
            'eventType'
        ));
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
