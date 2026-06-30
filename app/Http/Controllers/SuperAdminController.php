<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    /**
     * Display a listing of user login and CCTV viewing logs.
     */
    public function userLogs(Request $request)
    {
        $query = ActivityLog::with(['user', 'cctv.building'])->latest();

        // Filter by User Name / Email
        if ($request->filled('search_user')) {
            $search = $request->search_user;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Activity Type
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        // Filter by Date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Paginate logs
        $logs = $query->paginate(30)->withQueryString();

        // Get aggregate stats
        $totalLogins = ActivityLog::where('activity_type', 'login')->count();
        $totalViews = ActivityLog::where('activity_type', 'cctv_view')->count();

        return view('superadmin.logs', compact('logs', 'totalLogins', 'totalViews'));
    }
}
