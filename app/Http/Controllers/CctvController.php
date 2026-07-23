<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use App\Models\Building;
use App\Models\Server; // Wajib di-import untuk Multi-Node
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Eloquent\Builder; // Import untuk Builder

class CctvController extends Controller
{
    public function index(Request $request): View
    {
        $query = Cctv::accessibleByAuth()->with(['building', 'server']);

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_cctv', 'ilike', "%{$search}%")
                  ->orWhere('kode_cctv', 'ilike', "%{$search}%")
                  ->orWhere('ip', 'ilike', "%{$search}%");
            });
        }

        // Filter Gedung
        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }

        // Filter Server Node
        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        // Filter Penempatan
        if ($request->filled('penempatan')) {
            $query->where('penempatan', $request->penempatan);
        }

        $sortField = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        $cctvs = $query->orderBy($sortField, $sortDir)->paginate(15)->withQueryString();
        
        $buildings = \App\Models\Building::orderBy('nama_gedung')->get();
        $servers = \App\Models\Server::all();
                     
        return view('cctvs.index', compact('cctvs', 'buildings', 'servers'));
    }

    public function create()
    {
        \Illuminate\Support\Facades\Gate::authorize('cctv_create');
        
        $user = auth()->user();
        
        // 1. Filter Gedung berdasarkan Role (Sudah benar)
        $buildings = ($user->role === 'faculty_operator') 
                        ? Building::where('fakultas', $user->faculty)->get() 
                        : Building::all();
        
        // 2. Ambil Daftar Server Node (Wajib ditambahkan)
        $servers = Server::where('is_active', true)->get();

        return view('cctvs.create', compact('buildings', 'servers'));
    }

    public function store(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('cctv_create');

        try {
            $validated = $request->validate([
                'building_id'   => 'required|exists:buildings,id',
                'server_id'     => 'nullable|exists:servers,id', // Server Node
                
                'kode_cctv'     => 'required|string|unique:cctvs,kode_cctv|max:50',
                'nama_cctv'     => 'required|string|max:255',
                'ip'            => 'nullable|ip|unique:cctvs,ip',
                'rtsp_url'      => 'required|string',
                'rtsp_user'     => 'nullable|string',
                'rtsp_password' => 'nullable|string',
                'rtsp_url_sub'  => 'nullable|string',
                
                // ONVIF
                'onvif_port'     => 'nullable|integer',
                'onvif_user'     => 'nullable|string',
                'onvif_password' => 'nullable|string',
                
                'penempatan'     => 'required|in:Indoor,Outdoor',
                'lat'            => 'nullable|numeric',
                'lng'            => 'nullable|numeric',
                'status'        => 'nullable|in:online,offline,maintenance',
            ]);

            // Handle Password RTSP (Jangan timpa dengan NULL jika kosong)
            if (empty($validated['rtsp_password'])) {
                unset($validated['rtsp_password']);
            }

            // Handle Password ONVIF (Jangan timpa dengan NULL jika kosong)
            if (empty($validated['onvif_password'])) {
                unset($validated['onvif_password']);
            }
            
            // Set default onvif_port if null
            if (empty($validated['onvif_port'])) {
                $validated['onvif_port'] = 80;
            }

            Cctv::create($validated);
            Artisan::call('cctv:sync-config');

            return redirect()->route('cctv.index')->with('success', 'Kamera berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambah kamera: ' . $e->getMessage());
        }
    }

    public function edit(Cctv $cctv): View
    {
        \Illuminate\Support\Facades\Gate::authorize('cctv_edit');

        $buildings = Building::all();
        // Ambil Daftar Server Node (Wajib ditambahkan)
        $servers = Server::where('is_active', true)->get();
        
        return view('cctvs.edit', compact('cctv', 'buildings', 'servers'));
    }

    public function update(Request $request, Cctv $cctv): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('cctv_edit');

        try {
            $validated = $request->validate([
                'building_id'   => 'required|exists:buildings,id',
                'server_id'     => 'nullable|exists:servers,id', // Server Node
                
                'kode_cctv'     => 'required|string|max:50|unique:cctvs,kode_cctv,' . $cctv->id,
                'nama_cctv'     => 'required|string|max:255',
                'ip'            => 'nullable|ip|unique:cctvs,ip,' . $cctv->id,
                'rtsp_url'      => 'required|string',
                'rtsp_user'     => 'nullable|string',
                'rtsp_password' => 'nullable|string',
                'rtsp_url_sub'  => 'nullable|string',

                // ONVIF
                'onvif_port'     => 'nullable|integer',
                'onvif_user'     => 'nullable|string',
                'onvif_password' => 'nullable|string',

                'penempatan'     => 'required|in:Indoor,Outdoor',
                'lat'            => 'nullable|numeric',
                'lng'            => 'nullable|numeric',
                'status'        => 'required|in:online,offline,maintenance',
            ]);

            // Handle Password RTSP (Jangan timpa dengan NULL jika kosong)
            if (empty($validated['rtsp_password'])) {
                unset($validated['rtsp_password']);
            }

            // Handle Password ONVIF (Jangan timpa dengan NULL jika kosong)
            if (empty($validated['onvif_password'])) {
                unset($validated['onvif_password']);
            }
            
            // Set default onvif_port if null
            if (empty($validated['onvif_port'])) {
                $validated['onvif_port'] = 80;
            }

            $cctv->update($validated);
            Artisan::call('cctv:sync-config');

            return redirect()->route('cctv.index')
                ->with('success', 'Data kamera berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui kamera: ' . $e->getMessage());
        }
    }

    public function destroy(Cctv $cctv): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('cctv_delete');

        try {
            
            $cctv->delete();
            Artisan::call('cctv:sync-config');
            
            return redirect()->route('cctv.index')->with('success', 'Kamera berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus kamera: ' . $e->getMessage());
        }
    }

    public function bulkMove(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('cctv_bulk_move');

        $request->validate([
            'cctv_ids' => 'required|array',
            'cctv_ids.*' => 'exists:cctvs,id',
            'target_server_id' => 'nullable|exists:servers,id',
            'target_penempatan' => 'nullable|in:Indoor,Outdoor',
        ]);

        $updateData = [];
        if ($request->filled('target_server_id')) {
            $updateData['server_id'] = $request->target_server_id;
        }
        if ($request->filled('target_penempatan')) {
            $updateData['penempatan'] = $request->target_penempatan;
        }

        if (!empty($updateData)) {
            // 1. Dapatkan IP address dari server-server lama sebelum memindahkan
            $oldServerIps = [];
            if (isset($updateData['server_id'])) {
                $oldServerIps = Cctv::whereIn('id', $request->cctv_ids)
                    ->whereNotNull('server_id')
                    ->with('server')
                    ->get()
                    ->pluck('server.ip_address')
                    ->filter()
                    ->unique()
                    ->toArray();
            }

            Cctv::whereIn('id', $request->cctv_ids)->update($updateData);

            // 2. Beritahu Server Baru & Server-Server Lama secara real-time via PG NOTIFY
            if (isset($updateData['server_id'])) {
                $targetServer = Server::find($updateData['server_id']);
                $targetServerIp = $targetServer ? $targetServer->ip_address : 'ALL';
                
                // Beritahu server baru
                \DB::statement("NOTIFY cctv_update, '{$targetServerIp}'");
                
                // Beritahu semua server lama
                foreach ($oldServerIps as $ip) {
                    if ($ip !== $targetServerIp) {
                        \DB::statement("NOTIFY cctv_update, '{$ip}'");
                    }
                }
            } else {
                // Jika hanya update lokasi penempatan, infokan ALL
                \DB::statement("NOTIFY cctv_update, 'ALL'");
            }

            \Artisan::call('cctv:sync-config');
            return redirect()->route('cctv.index')->with('success', count($request->cctv_ids) . ' Kamera berhasil diperbarui secara masal.');
        }

        return redirect()->route('cctv.index')->with('info', 'Tidak ada perubahan yang dipilih.');
    }
}