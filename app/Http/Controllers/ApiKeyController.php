<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ApiKeyController extends Controller
{
    public function index()
    {
        // Ambil hanya user dengan role 'api_viewer'
        // Beserta token-token yang mereka miliki
        $apiUsers = User::where('role', 'api_viewer')
                        ->with('tokens')
                        ->orderBy('name')
                        ->get();
        
        return view('api.index', compact('apiUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'token_name' => 'required|string|max:255',
        ]);

        // Cari user target (Pastikan dia memang api_viewer)
        $targetUser = User::where('id', $request->user_id)
                          ->where('role', 'api_viewer')
                          ->firstOrFail();
        
        // Buat token ATAS NAMA user target tersebut
        $token = $targetUser->createToken($request->token_name);

        return back()->with('success', 'Token berhasil dibuat untuk ' . $targetUser->name . '. Token: ' . $token->plainTextToken);
    }

    public function destroy($id)
    {
        // Hapus token berdasarkan ID token (dari tabel personal_access_tokens)
        // Kita gunakan model User milik Laravel Sanctum atau query builder
        \Laravel\Sanctum\PersonalAccessToken::findOrFail($id)->delete();

        return back()->with('success', 'Token berhasil dicabut (revoked).');
    }
}