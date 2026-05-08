<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class FacultyController extends Controller
{
    /**
     * Menampilkan daftar fakultas.
     */
    public function index(Request $request): View
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'operator']), 403);

        $query = Faculty::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $faculties = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('faculties.index', compact('faculties'));
    }

    /**
     * Menampilkan form tambah fakultas.
     */
    public function create(): View
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'operator']), 403);
        
        return view('faculties.create');
    }

    /**
     * Menyimpan data fakultas baru.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'operator']), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:faculties,name',
        ]);

        Faculty::create($validated);

        return redirect()->route('faculties.index')
            ->with('success', 'Fakultas berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit fakultas.
     */
    public function edit(Faculty $faculty): View
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'operator']), 403);

        return view('faculties.edit', compact('faculty'));
    }

    /**
     * Update data fakultas.
     */
    public function update(Request $request, Faculty $faculty): RedirectResponse
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'operator']), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:faculties,name,' . $faculty->id,
        ]);

        $faculty->update($validated);

        return redirect()->route('faculties.index')
            ->with('success', 'Data fakultas berhasil diperbarui.');
    }

    /**
     * Hapus data fakultas.
     */
    public function destroy(Faculty $faculty): RedirectResponse
    {
        abort_if(!in_array(auth()->user()->role, ['admin', 'operator']), 403);

        $faculty->delete();

        return redirect()->route('faculties.index')
            ->with('success', 'Fakultas berhasil dihapus.');
    }
}
