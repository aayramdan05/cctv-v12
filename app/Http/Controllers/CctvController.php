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
    public function index(): View
    {
        // Gunakan scope accessibleByAuth untuk RBAC (Operator Fakultas hanya lihat kameranya)
        $cctvs = Cctv::accessibleByAuth()
                     ->with(['building', 'server'])
                     ->latest()
                     ->paginate(10);
                     
        return view('cctvs.index', compact('cctvs'));
    }

    public function create()
    {
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
        $validated = $request->validate([
            'building_id'   => 'required|exists:buildings,id',
            'server_id'     => 'nullable|exists:servers,id', // Server Node
            
            'kode_cctv'     => 'required|string|unique:cctvs,kode_cctv|max:50',
            'nama_cctv'     => 'required|string|max:255',
            'ip'            => 'nullable|ip',
            'rtsp_url'      => 'required|string',
            'rtsp_user'     => 'nullable|string',
            'rtsp_password' => 'nullable|string',
            'rtsp_url_sub'  => 'nullable|string', // URL Substream
            
            'status'        => 'nullable|in:online,offline,maintenance',
        ]);

        Cctv::create($validated);
        Artisan::call('cctv:sync-config');

        return redirect()->route('cctv.index')->with('success', 'Kamera berhasil ditambahkan.');
    }

    public function edit(Cctv $cctv): View
    {
        // Pengecekan Hak Akses (Wajib)
        if (auth()->user()->role === 'faculty_operator' && $cctv->building->fakultas !== auth()->user()->faculty) {
            abort(403, 'Anda tidak memiliki izin mengedit kamera ini.');
        }

        $buildings = Building::all();
        // Ambil Daftar Server Node (Wajib ditambahkan)
        $servers = Server::where('is_active', true)->get();
        
        return view('cctvs.edit', compact('cctv', 'buildings', 'servers'));
    }

    public function update(Request $request, Cctv $cctv): RedirectResponse
    {
        $validated = $request->validate([
            'building_id'   => 'required|exists:buildings,id',
            'server_id'     => 'nullable|exists:servers,id', // Server Node
            
            'kode_cctv'     => 'required|string|max:50|unique:cctvs,kode_cctv,' . $cctv->id,
            'nama_cctv'     => 'required|string|max:255',
            'ip'            => 'nullable|ip',
            'rtsp_url'      => 'required|string',
            'rtsp_user'     => 'nullable|string',
            'rtsp_password' => 'nullable|string',
            'rtsp_url_sub'  => 'nullable|string',
            'status'        => 'required|in:online,offline,maintenance',
        ]);

        // Handle Password (Jangan timpa dengan NULL jika kosong)
        if (empty($validated['rtsp_password'])) {
            unset($validated['rtsp_password']);
        }

        $cctv->update($validated);
        Artisan::call('cctv:sync-config');

        return redirect()->route('cctv.index')
            ->with('success', 'Data kamera berhasil diperbarui.');
    }

    public function destroy(Cctv $cctv): RedirectResponse
    {
        // Pengecekan Hak Akses Hapus
        if (auth()->user()->role === 'faculty_operator' && $cctv->building->fakultas !== auth()->user()->faculty) {
            abort(403);
        }
        
        $cctv->delete();
        Artisan::call('cctv:sync-config');
        
        return redirect()->route('cctv.index')->with('success', 'Kamera berhasil dihapus.');
    }
}