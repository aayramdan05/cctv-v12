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
    public function index(Request $request): View
    {
        $query = Building::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_gedung', 'ilike', "%{$search}%")
                  ->orWhere('kode_gedung', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('fakultas')) {
            $query->where('fakultas', $request->fakultas);
        }

        $sortField = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        $buildings = $query->orderBy($sortField, $sortDir)->paginate(15)->withQueryString();
        $faculties = \App\Models\Faculty::orderBy('name')->pluck('name');

        return view('buildings.index', compact('buildings', 'faculties'));
    }

    /**
     * Menampilkan form tambah gedung.
     */
    public function create(): View
    {
        $faculties = \App\Models\Faculty::orderBy('name')->get();
        return view('buildings.create', compact('faculties'));
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
        $faculties = \App\Models\Faculty::orderBy('name')->get();
        return view('buildings.edit', compact('building', 'faculties'));
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