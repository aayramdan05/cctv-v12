<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BuildingController extends Controller
{
    /**
     * Menampilkan daftar gedung.
     */
    public function index(): View
    {
        $buildings = Building::latest()->paginate(10);
        return view('buildings.index', compact('buildings'));
    }

    /**
     * Menampilkan form tambah gedung.
     */
    public function create(): View
    {
        return view('buildings.create');
    }

    /**
     * Menyimpan data gedung baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_gedung' => 'required|string|unique:buildings,kode_gedung|max:10',
            'nama_gedung' => 'required|string|max:255',
            'fakultas'    => 'required|string|max:255',
        ]);

        Building::create($validated);

        return redirect()->route('building.index')
            ->with('success', 'Gedung berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit gedung.
     */
    public function edit(Building $building): View
    {
        return view('buildings.edit', compact('building'));
    }

    /**
     * Update data gedung.
     */
    public function update(Request $request, Building $building): RedirectResponse
    {
        $validated = $request->validate([
            'kode_gedung' => 'required|string|max:10|unique:buildings,kode_gedung,' . $building->id,
            'nama_gedung' => 'required|string|max:255',
            'fakultas'    => 'required|string|max:255',
        ]);

        $building->update($validated);

        return redirect()->route('building.index')
            ->with('success', 'Data gedung berhasil diperbarui.');
    }

    /**
     * Hapus data gedung.
     */
    public function destroy(Building $building): RedirectResponse
    {
        $building->delete();

        return redirect()->route('building.index')
            ->with('success', 'Gedung berhasil dihapus.');
    }
}