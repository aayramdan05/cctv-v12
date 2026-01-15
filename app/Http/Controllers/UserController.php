<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cctv;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        $query = User::query();

        if ($currentUser->role === 'faculty_operator') {
            $query->where('faculty', $currentUser->faculty);
        } elseif ($currentUser->role === 'operator') {
            $query->where('role', '!=', 'admin');
        }

        $users = $query->latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        // Pastikan variabel ini dikirim ke View
        $cctvs = Cctv::orderBy('nama_cctv')->get();
        $faculties = Building::distinct()->pluck('fakultas')->filter();
        
        return view('users.create', compact('cctvs', 'faculties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:admin,operator,faculty_operator,user,api_viewer'],
            'faculty' => ['nullable', 'string'],
            'cctv_access' => ['nullable', 'array'], // Validasi array checkbox
            'cctv_access.*' => ['exists:cctvs,id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'faculty' => $request->role === 'faculty_operator' || $request->role === 'user' ? $request->faculty : null,
        ]);

        // --- FIX: SIMPAN RELASI CCTV ---
        if (in_array($request->role, ['user', 'api_viewer'])) {
            $user->cctvs()->sync($request->cctv_access ?? []);
        }
        // -------------------------------

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $cctvs = Cctv::orderBy('nama_cctv')->get();
        $faculties = Building::distinct()->pluck('fakultas')->filter();
        
        // --- FIX: KIRIM DATA CCTV YANG SUDAH DIPILIH ---
        $assignedCctvs = $user->cctvs->pluck('id')->toArray();
        // -----------------------------------------------

        return view('users.edit', compact('user', 'cctvs', 'faculties', 'assignedCctvs'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'string', 'in:admin,operator,faculty_operator,user,api_viewer'],
            'faculty' => ['nullable', 'string'],
            'cctv_access' => ['nullable', 'array'],
            'cctv_access.*' => ['exists:cctvs,id'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'faculty' => $request->role === 'faculty_operator' || $request->role === 'user' ? $request->faculty : null,
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // --- FIX: UPDATE RELASI CCTV ---
        if (in_array($request->role, ['user', 'api_viewer'])) {
            $user->cctvs()->sync($request->cctv_access ?? []);
        } else {
            // Jika role berubah jadi admin/operator, hapus relasi cctv karena mereka akses semua
            $user->cctvs()->detach();
        }
        // -------------------------------

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}