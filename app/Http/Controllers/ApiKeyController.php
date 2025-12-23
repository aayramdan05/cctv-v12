<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    // Tampilkan daftar token user saat ini
    public function index()
    {
        $user = auth()->user();
        // Ambil semua token milik user yang login
        $tokens = $user->tokens;
        
        return view('api.index', compact('tokens'));
    }

    // Buat Token Baru
    public function store(Request $request)
    {
        $request->validate([
            'token_name' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        
        // Buat token dengan permission 'read' (bisa disesuaikan nanti)
        // createToken mengembalikan instance NewAccessToken
        $token = $user->createToken($request->token_name);

        // Token plain text HANYA muncul sekali saat dibuat
        return back()->with('success', 'API Token berhasil dibuat. Simpan ini sekarang: ' . $token->plainTextToken);
    }

    // Hapus Token (Revoke)
    public function destroy($id)
    {
        auth()->user()->tokens()->where('id', $id)->delete();
        return back()->with('success', 'Token berhasil dihapus.');
    }
}