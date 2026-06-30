<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cctv;
use App\Models\Building;
use App\Models\Server;
use Rap2hpoutre\FastExcel\FastExcel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display the CCTV report view with filters.
     */
    public function index(Request $request)
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'superadmin']), 403);

        $query = Cctv::with(['building', 'server'])->withAvg('recordings', 'size_mb');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_cctv', 'like', "%{$search}%")
                  ->orWhere('kode_cctv', 'like', "%{$search}%")
                  ->orWhere('ip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->input('building_id'));
        }

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->input('server_id'));
        }

        if ($request->filled('penempatan')) {
            $query->where('penempatan', $request->input('penempatan'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Retrieve lists for dropdown filters
        $buildings = Building::orderBy('nama_gedung')->get();
        $servers = Server::orderBy('id')->get();

        // Paginate for web view (ajax supported)
        $cctvs = $query->paginate(25)->withQueryString();

        if ($request->ajax()) {
            return view('reports.index', compact('cctvs', 'buildings', 'servers'));
        }

        return view('reports.index', compact('cctvs', 'buildings', 'servers'));
    }

    /**
     * Export the filtered CCTV dataset to CSV.
     */
    public function exportCsv(Request $request)
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'superadmin']), 403);

        $query = Cctv::with(['building', 'server'])->withAvg('recordings', 'size_mb');

        // Apply same filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_cctv', 'like', "%{$search}%")
                  ->orWhere('kode_cctv', 'like', "%{$search}%")
                  ->orWhere('ip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->input('building_id'));
        }

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->input('server_id'));
        }

        if ($request->filled('penempatan')) {
            $query->where('penempatan', $request->input('penempatan'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $cctvs = $query->get();

        $exportData = [];
        $no = 1;

        foreach ($cctvs as $cctv) {
            $exportData[] = [
                'No.' => $no++,
                'Kode CCTV' => $cctv->kode_cctv,
                'Nama CCTV' => $cctv->nama_cctv,
                'Merk CCTV' => $cctv->merk,
                'IP Address' => $cctv->ip ?? '-',
                'Ukuran File / 15 Menit' => $cctv->recordings_avg_size_mb ? round($cctv->recordings_avg_size_mb, 2) . ' MB' : '0 MB',
                'Penempatan' => $cctv->penempatan,
                'Gedung' => $cctv->building ? $cctv->building->nama_gedung : '-',
                'Kode Gedung' => $cctv->building ? $cctv->building->kode_gedung : '-',
                'Fakultas' => $cctv->building ? $cctv->building->fakultas : '-',
                'Server Node' => $cctv->server ? $cctv->server->name : '-',
                'IP Server' => $cctv->server ? $cctv->server->ip_address : '-',
                'Latitude' => $cctv->lat ?? '-',
                'Longitude' => $cctv->lng ?? '-',
                'Status' => strtoupper($cctv->status),
            ];
        }

        return (new FastExcel(collect($exportData)))->download('Report_CCTV_' . date('Ymd_His') . '.csv');
    }

    /**
     * Export the filtered CCTV dataset to PDF.
     */
    public function exportPdf(Request $request)
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'superadmin']), 403);

        $query = Cctv::with(['building', 'server'])->withAvg('recordings', 'size_mb');

        // Apply same filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_cctv', 'like', "%{$search}%")
                  ->orWhere('kode_cctv', 'like', "%{$search}%")
                  ->orWhere('ip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->input('building_id'));
        }

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->input('server_id'));
        }

        if ($request->filled('penempatan')) {
            $query->where('penempatan', $request->input('penempatan'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $cctvs = $query->get();

        // Prevent memory timeouts on large downloads
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $pdf = Pdf::loadView('reports.pdf', compact('cctvs'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download('Report_CCTV_' . date('Ymd_His') . '.pdf');
    }
}
