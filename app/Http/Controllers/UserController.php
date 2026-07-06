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
        if ($currentUser->role !== 'superadmin') {
            $query->where('role', '!=', 'superadmin');
        }

        if ($currentUser->role === 'faculty_operator') {
            $query->where('faculty', $currentUser->faculty);
        } elseif ($currentUser->role === 'operator') {
            $query->where('role', '!=', 'admin');
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Role Filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sortField = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        $users = $query->orderBy($sortField, $sortDir)->paginate(15)->withQueryString();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        \Illuminate\Support\Facades\Gate::authorize('user_create');

        // Pastikan variabel ini dikirim ke View
        $cctvs = Cctv::orderBy('nama_cctv')->get();
        $faculties = \App\Models\Faculty::orderBy('name')->pluck('name');
        
        return view('users.create', compact('cctvs', 'faculties'));
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('user_create');

        $currentUser = auth()->user();

        // --- HIERARKI RBAC ---
        if ($currentUser->role === 'faculty_operator') {
            // Operator Fakultas HANYA BOLEH membuat role 'user' di fakultasnya sendiri
            $request->merge([
                'role' => 'user',
                'faculty' => $currentUser->faculty
            ]);
        } elseif ($currentUser->role === 'operator') {
            abort(403, 'Operator Pusat tidak diizinkan menambah User.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:superadmin,admin,operator,faculty_operator,user,api_viewer'],
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
            'status' => 'approved',
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
        \Illuminate\Support\Facades\Gate::authorize('user_edit');

        abort_if($user->role === 'superadmin' && auth()->user()->role !== 'superadmin', 403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengedit akun Super Admin.');

        $cctvs = Cctv::orderBy('nama_cctv')->get();
        $faculties = \App\Models\Faculty::orderBy('name')->pluck('name');
        
        // --- FIX: KIRIM DATA CCTV YANG SUDAH DIPILIH ---
        $assignedCctvs = $user->cctvs->pluck('id')->toArray();
        // -----------------------------------------------

        return view('users.edit', compact('user', 'cctvs', 'faculties', 'assignedCctvs'));
    }

    public function update(Request $request, User $user)
    {
        \Illuminate\Support\Facades\Gate::authorize('user_edit');

        $currentUser = auth()->user();

        abort_if($user->role === 'superadmin' && $currentUser->role !== 'superadmin', 403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengubah akun Super Admin.');

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
            // Cegah mengedit akun admin/operator, dan paksa data lama (hanya bisa ubah assign camera)
            if (in_array($user->role, ['admin', 'operator'])) {
                abort(403, 'Operator Pusat tidak boleh mengedit akun Admin atau sesama Operator.');
            }
            $request->merge([
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'faculty' => $user->faculty
            ]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'string', 'in:superadmin,admin,operator,faculty_operator,user,api_viewer'],
            'faculty' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:approved,pending'],
            'cctv_access' => ['nullable', 'array'],
            'cctv_access.*' => ['exists:cctvs,id'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'faculty' => $request->role === 'faculty_operator' || $request->role === 'user' ? $request->faculty : null,
        ];

        if ($request->has('status') && \Illuminate\Support\Facades\Gate::allows('user_approve')) {
            $data['status'] = $request->status;
        }

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
        \Illuminate\Support\Facades\Gate::authorize('user_delete');

        $currentUser = auth()->user();

        abort_if($user->role === 'superadmin' && $currentUser->role !== 'superadmin', 403, 'Akses Ditolak: Anda tidak memiliki izin untuk menghapus akun Super Admin.');

        // --- HIERARKI RBAC SECURITY ---
        if ($currentUser->role === 'faculty_operator') {
            if ($user->role !== 'user' || $user->faculty !== $currentUser->faculty) {
                abort(403, 'Anda hanya boleh menghapus User biasa di fakultas Anda.');
            }
        } elseif ($currentUser->role === 'operator') {
            abort(403, 'Operator Pusat tidak diizinkan menghapus User.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}