<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ServerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Server::withCount('cctvs');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('ip_address', 'ilike', "%{$search}%");
            });
        }

        $sortField = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        $servers = $query->orderBy($sortField, $sortDir)->paginate(15)->withQueryString();
        return view('servers.index', compact('servers'));
    }

    public function create(): View
    {
        return view('servers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer|min:1|unique:servers,id',
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ipv4|unique:servers,ip_address',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'retention_days' => 'required|integer|min:1|max:365',
            'is_active' => 'boolean',
        ]);

        // Default is_active true jika tidak dikirim
        $validated['is_active'] = $request->has('is_active');

        // Unset ID if empty to let database auto-increment work correctly
        if (empty($validated['id'])) {
            unset($validated['id']);
        }

        Server::create($validated);

        // Sync PostgreSQL sequence if using pgsql
        if ($request->filled('id') && DB::getDriverName() === 'pgsql') {
            $maxId = DB::table('servers')->max('id');
            if ($maxId) {
                DB::statement("SELECT setval('servers_id_seq', ?)", [$maxId]);
            }
        }

        return redirect()->route('servers.index')->with('success', 'Server node berhasil ditambahkan.');
    }

    public function edit(Server $server): View
    {
        return view('servers.edit', compact('server'));
    }

    public function update(Request $request, Server $server): RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|min:1|unique:servers,id,' . $server->id,
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ipv4|unique:servers,ip_address,' . $server->id,
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'retention_days' => 'required|integer|min:1|max:365',
        ]);

        // Handle checkbox toggle
        $validated['is_active'] = $request->has('is_active');

        $oldId = $server->id;
        $newId = (int)$validated['id'];

        DB::transaction(function () use ($server, $validated, $oldId, $newId) {
            if ($newId !== $oldId) {
                // 1. Create a new server row with the new ID
                $newServerData = $validated;
                $newServerData['id'] = $newId;
                Server::create($newServerData);

                // 2. Update dependent cctvs
                DB::table('cctvs')->where('server_id', $oldId)->update(['server_id' => $newId]);
                
                // 3. Delete the old server
                DB::table('servers')->where('id', $oldId)->delete();
                
                // Sync PostgreSQL sequence if pgsql
                if (DB::getDriverName() === 'pgsql') {
                    $maxId = DB::table('servers')->max('id');
                    if ($maxId) {
                        DB::statement("SELECT setval('servers_id_seq', ?)", [$maxId]);
                    }
                }

                // Trigger sync notification
                try {
                    DB::statement("NOTIFY cctv_update, 'ALL'");
                } catch (\Exception $e) {
                    // Ignore if notify fails or not supported
                }
            } else {
                $server->update($validated);
            }
        });

        return redirect()->route('servers.index')->with('success', 'Data server berhasil diperbarui.');
    }

    public function destroy(Server $server): RedirectResponse
    {
        // Cek apakah masih ada kamera yang nyantol di server ini
        if ($server->cctvs()->count() > 0) {
            return back()->with('error', 'Gagal hapus! Masih ada kamera yang terhubung ke server ini. Pindahkan dulu kameranya.');
        }

        $server->delete();
        return redirect()->route('servers.index')->with('success', 'Server node dihapus.');
    }
}