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

        $cctvs = $query->latest()->paginate(15)->withQueryString();
        
        $buildings = \App\Models\Building::orderBy('nama_gedung')->get();
        $servers = \App\Models\Server::all();
                     
        return view('cctvs.index', compact('cctvs', 'buildings', 'servers'));
    }

    public function create()
    {
        abort_if(auth()->user()->role === 'faculty_operator', 403, 'Operator Fakultas tidak diizinkan menambah kamera.');
        
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
        abort_if(auth()->user()->role === 'faculty_operator', 403, 'Operator Fakultas tidak diizinkan menambah kamera.');

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
                
                'status'        => 'nullable|in:online,offline,maintenance',
            ]);

            Cctv::create($validated);
            Artisan::call('cctv:sync-config');

            return redirect()->route('cctv.index')->with('success', 'Kamera berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambah kamera: ' . $e->getMessage());
        }
    }

    public function edit(Cctv $cctv): View
    {
        // Pengecekan Hak Akses (Wajib)
        abort_if(auth()->user()->role === 'faculty_operator', 403, 'Operator Fakultas tidak diizinkan mengedit kamera.');

        $buildings = Building::all();
        // Ambil Daftar Server Node (Wajib ditambahkan)
        $servers = Server::where('is_active', true)->get();
        
        return view('cctvs.edit', compact('cctv', 'buildings', 'servers'));
    }

    public function update(Request $request, Cctv $cctv): RedirectResponse
    {
        abort_if(auth()->user()->role === 'faculty_operator', 403, 'Operator Fakultas tidak diizinkan mengedit kamera.');

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
        abort_if(auth()->user()->role === 'faculty_operator', 403, 'Operator Fakultas tidak diizinkan menghapus kamera.');

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
        abort_if(auth()->user()->role === 'faculty_operator', 403, 'Operator Fakultas tidak diizinkan memindahkan kamera.');

        $request->validate([
            'cctv_ids' => 'required|array',
            'cctv_ids.*' => 'exists:cctvs,id',
            'target_server_id' => 'required|exists:servers,id',
        ]);

        Cctv::whereIn('id', $request->cctv_ids)->update([
            'server_id' => $request->target_server_id
        ]);

        // Opsional: Jalankan sync config setelah pindah masal
        \Artisan::call('cctv:sync-config');

        return redirect()->route('cctv.index')->with('success', count($request->cctv_ids) . ' Kamera berhasil dipindahkan ke Node baru.');
    }
}