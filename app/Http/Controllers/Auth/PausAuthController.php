<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class PausAuthController extends Controller
{
    /**
     * Redirect ke SSO Unpad (PAUS)
     */
    public function redirectToPaus()
    {
        return Socialite::driver('paus')
            ->scopes(['email', 'profile'])
            ->with(['redirect_uri' => config('services.paus.redirect')])
            ->redirect();
    }

    /**
     * Handle Callback dari PAUS
     */
    public function handlePausCallback()
    {
        try {
            $pausUser = Socialite::driver('paus')->user();
            
            // 1. Cari user berdasarkan paus_id atau email
            $user = User::where('paus_id', $pausUser->getId())
                        ->orWhere('email', $pausUser->getEmail())
                        ->first();

            if ($user) {
                // Update data PAUS jika sudah ada
                $user->update([
                    'paus_id' => $pausUser->getId(),
                    'paus_username' => $pausUser->getNickname(),
                    'name' => $pausUser->getName(), // Update nama sesuai PAUS
                ]);
            } else {
                // 2. Buat user baru jika belum terdaftar
                $user = User::create([
                    'name' => $pausUser->getName(),
                    'email' => $pausUser->getEmail(),
                    'paus_id' => $pausUser->getId(),
                    'paus_username' => $pausUser->getNickname(),
                    'password' => bcrypt(Str::random(24)), // Password random karena login via SSO
                    'role' => 'user', // Default: View Only (User Biasa)
                ]);
            }

            // 3. Login-kan user
            Auth::login($user);

            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Gagal login menggunakan SSO Unpad: ' . $e->getMessage());
        }
    }
}
