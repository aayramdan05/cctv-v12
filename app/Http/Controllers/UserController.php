<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cctv;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Services\RoleService;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request, RoleService $roleService)
    {
        $currentUser = auth()->user();
        $query = User::query();

        // RBAC Filter
        if ($currentUser->role !== 'superadmin') {
            $query->where('role', '!=', 'superadmin');
        }

        if ($currentUser->role === 'faculty_operator') {
            $query->where('faculty', $currentUser->faculty);
        } elseif (!in_array($currentUser->role, ['superadmin', 'admin'])) {
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
        $rolesList = $roleService->getAllRoles();
        return view('users.index', compact('users', 'rolesList'));
    }

    public function create(RoleService $roleService)
    {
        \Illuminate\Support\Facades\Gate::authorize('user_create');

        // Pastikan variabel ini dikirim ke View
        $cctvs = Cctv::orderBy('nama_cctv')->get();
        $faculties = \App\Models\Faculty::orderBy('name')->pluck('name');
        $rolesList = $roleService->getAllRoles();
        
        return view('users.create', compact('cctvs', 'faculties', 'rolesList'));
    }

    public function store(Request $request, RoleService $roleService)
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
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::in(array_keys($roleService->getAllRoles()))],
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

    public function edit(User $user, RoleService $roleService)
    {
        \Illuminate\Support\Facades\Gate::authorize('user_edit');

        abort_if($user->role === 'superadmin' && auth()->user()->role !== 'superadmin', 403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengedit akun Super Admin.');

        $cctvs = Cctv::orderBy('nama_cctv')->get();
        $faculties = \App\Models\Faculty::orderBy('name')->pluck('name');
        
        // --- FIX: KIRIM DATA CCTV YANG SUDAH DIPILIH ---
        $assignedCctvs = $user->cctvs->pluck('id')->toArray();
        $rolesList = $roleService->getAllRoles();
        // -----------------------------------------------

        return view('users.edit', compact('user', 'cctvs', 'faculties', 'assignedCctvs', 'rolesList'));
    }

    public function update(Request $request, User $user, RoleService $roleService)
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
        } elseif (!in_array($currentUser->role, ['superadmin', 'admin'])) {
            // Cegah non-admin mengedit akun admin
            if (in_array($user->role, ['admin', 'superadmin'])) {
                abort(403, 'Tidak boleh mengedit akun Administrator tingkat atas.');
            }
            // Cegah non-admin mengubah role menjadi admin
            if ($request->has('role') && in_array($request->role, ['admin', 'superadmin'])) {
                abort(403, 'Tidak boleh mengangkat user menjadi Administrator.');
            }
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'string', Rule::in(array_keys($roleService->getAllRoles()))],
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
            // Jika role berubah jadi admin/operator/upt_lingkungan, hapus relasi cctv karena mereka akses semua
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
        } elseif (!in_array($currentUser->role, ['superadmin', 'admin'])) {
            if (in_array($user->role, ['admin', 'superadmin'])) {
                abort(403, 'Tidak diizinkan menghapus akun Administrator.');
            }
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    public function deactivate(Request $request, User $user)
    {
        \Illuminate\Support\Facades\Gate::authorize('user_edit');

        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $user->update([
            'status' => 'deactivated',
            'deactivation_reason' => $request->reason,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil dinonaktifkan.');
    }

    public function activate(User $user)
    {
        \Illuminate\Support\Facades\Gate::authorize('user_edit');

        $user->update([
            'status' => 'approved',
            'deactivation_reason' => null,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil diaktifkan.');
    }
}