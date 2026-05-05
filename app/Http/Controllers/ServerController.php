<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ServerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Server::withCount('cctvs');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $servers = $query->latest()->paginate(15)->withQueryString();
        return view('servers.index', compact('servers'));
    }

    public function create(): View
    {
        return view('servers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ipv4|unique:servers,ip_address',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'retention_days' => 'required|integer|min:1|max:365',
            'is_active' => 'boolean',
        ]);

        // Default is_active true jika tidak dikirim
        $validated['is_active'] = $request->has('is_active');

        Server::create($validated);

        return redirect()->route('servers.index')->with('success', 'Server node berhasil ditambahkan.');
    }

    public function edit(Server $server): View
    {
        return view('servers.edit', compact('server'));
    }

    public function update(Request $request, Server $server): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ipv4|unique:servers,ip_address,' . $server->id,
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'retention_days' => 'required|integer|min:1|max:365',
        ]);

        // Handle checkbox toggle
        $validated['is_active'] = $request->has('is_active');

        $server->update($validated);

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