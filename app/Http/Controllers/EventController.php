<?php

namespace App\Http\Controllers;

use App\Models\CameraEvent;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = CameraEvent::with(['cctv.building'])->latest();

        if ($request->filled('cctv_id')) {
            $query->where('cctv_id', $request->cctv_id);
        }

        $events = $query->paginate(20);
        
        return view('events.index', compact('events'));
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
