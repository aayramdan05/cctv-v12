<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cctv;
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
        $cctvs = Cctv::accessibleByAuth()->with('building')->get();
        // Kita kirim list fakultas dari sini biar rapi
        $faculties = \App\Models\Building::distinct()->pluck('fakultas')->filter();
        
        return view('users.create', compact('cctvs', 'faculties'));
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,operator,user,faculty_operator'],
            'cctv_access' => ['array'],
        ];

        // PERBAIKAN DISINI:
        // Cek jika user saat ini adalah Admin ATAU Operator Pusat
        if (in_array($currentUser->role, ['admin', 'operator'])) {
            // Jika mereka membuat 'faculty_operator' atau 'user', WAJIB isi fakultas
            if (in_array($request->role, ['faculty_operator', 'user'])) {
                $rules['faculty'] = ['required', 'string'];
            }
        }

        $request->validate($rules);

        $assignedFaculty = null;

        if ($currentUser->role === 'faculty_operator') {
            $assignedFaculty = $currentUser->faculty;
        } else {
            $assignedFaculty = $request->faculty;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'faculty' => $assignedFaculty,
        ]);

        if ($request->role === 'user' && $request->has('cctv_access')) {
            $user->cctvs()->sync($request->cctv_access);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat.');
    }

    // ... (Method edit, update, destroy biarkan sama, sesuaikan logika validasinya saja) ...
    public function edit(User $user)
    {
        $cctvs = Cctv::accessibleByAuth()->with('building')->get();
        $assignedCctvs = $user->cctvs->pluck('id')->toArray();
        $faculties = \App\Models\Building::distinct()->pluck('fakultas')->filter(); // Tambahkan ini
        
        return view('users.edit', compact('user', 'cctvs', 'assignedCctvs', 'faculties'));
    }
    
    public function update(Request $request, User $user)
    {
         // Pastikan copy logika validasi & penentuan fakultas yang sama seperti store()
         // ...
         
         // CONTOH UPDATE SINGKAT:
         $currentUser = auth()->user();
         $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:admin,operator,user,faculty_operator'],
         ];
         
         if (in_array($currentUser->role, ['admin', 'operator'])) {
            if (in_array($request->role, ['faculty_operator', 'user'])) {
                $rules['faculty'] = ['required', 'string'];
            }
         }
         
         $request->validate($rules);
         
         $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
         ];
         
         if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Rules\Password::defaults()]]);
            $data['password'] = Hash::make($request->password);
         }
         
         // Update Fakultas Logic
         if ($currentUser->role === 'faculty_operator') {
            $data['faculty'] = $currentUser->faculty;
         } else {
            $data['faculty'] = $request->faculty;
         }

         $user->update($data);
         
         if ($request->role === 'user') {
            $user->cctvs()->sync($request->cctv_access ?? []);
         } else {
            $user->cctvs()->detach();
         }
         
         return redirect()->route('users.index')->with('success', 'User update berhasil.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) return back()->with('error', 'Tidak bisa hapus diri sendiri');
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User dihapus.');
    }
}