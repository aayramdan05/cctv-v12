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
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $query = User::query();

        // RBAC Filter
        if ($currentUser->role === 'faculty_operator') {
            $query->where('faculty', $currentUser->faculty);
        } elseif ($currentUser->role === 'operator') {
            $query->where('role', '!=', 'admin');
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role Filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(15)->withQueryString();
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
        $currentUser = auth()->user();

        // --- HIERARKI RBAC ---
        if ($currentUser->role === 'faculty_operator') {
            // Operator Fakultas HANYA BOLEH membuat role 'user' di fakultasnya sendiri
            $request->merge([
                'role' => 'user',
                'faculty' => $currentUser->faculty
            ]);
        } elseif ($currentUser->role === 'operator') {
            // Operator Pusat HANYA BOLEH membuat role 'faculty_operator' dan 'user'
            if (!in_array($request->role, ['faculty_operator', 'user'])) {
                abort(403, 'Operator Pusat hanya boleh membuat akun Operator Fakultas dan User.');
            }
        }

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
        $currentUser = auth()->user();

        // --- HIERARKI RBAC SECURITY ---
        if ($currentUser->role === 'faculty_operator') {
            // Cegah mengedit orang selain 'user' dari fakultasnya
            if ($user->role !== 'user' || $user->faculty !== $currentUser->faculty) {
                abort(403, 'Anda hanya boleh mengedit User biasa di fakultas Anda.');
            }
            // Paksa nilai agar tidak dirubah via Inspect Element
            $request->merge([
                'role' => 'user',
                'faculty' => $currentUser->faculty
            ]);
        } elseif ($currentUser->role === 'operator') {
            // Cegah mengedit akun admin/operator/api_viewer, ATAU menset role menjadi admin/operator/api_viewer
            if (!in_array($user->role, ['faculty_operator', 'user']) || !in_array($request->role, ['faculty_operator', 'user'])) {
                abort(403, 'Operator Pusat hanya boleh mengedit dan mengubah role menjadi Operator Fakultas atau User.');
            }
        }

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
        $currentUser = auth()->user();

        // --- HIERARKI RBAC SECURITY ---
        if ($currentUser->role === 'faculty_operator') {
            if ($user->role !== 'user' || $user->faculty !== $currentUser->faculty) {
                abort(403, 'Anda hanya boleh menghapus User biasa di fakultas Anda.');
            }
        } elseif ($currentUser->role === 'operator') {
            if (!in_array($user->role, ['faculty_operator', 'user'])) {
                abort(403, 'Operator Pusat hanya boleh menghapus akun Operator Fakultas dan User.');
            }
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}