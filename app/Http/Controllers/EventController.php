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
            'activeTab'
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
