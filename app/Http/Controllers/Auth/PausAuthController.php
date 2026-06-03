<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class PausAuthController extends Controller
{
    /**
     * Redirect ke SSO Unpad (PAUS)
     */
    public function redirectToPaus()
    {
        Log::info('Redirecting to PAUS SSO with redirect_uri: ' . config('services.paus.redirect'));
        
        return Socialite::driver('paus')
            ->redirectUrl(config('services.paus.redirect'))
            ->scopes(['user.basic', 'user.email'])
            ->redirect();
    }

    /**
     * Handle Callback dari PAUS
     */
    public function handlePausCallback()
    {
        Log::info('Received callback from PAUS SSO');

        try {
            $pausUser = Socialite::driver('paus')
                ->redirectUrl(config('services.paus.redirect'))
                ->stateless()
                ->user();
            
            dd([
                'id' => $pausUser->getId(),
                'nickname' => $pausUser->getNickname(),
                'name' => $pausUser->getName(),
                'email' => $pausUser->getEmail(),
                'raw' => $pausUser->getRaw(),
            ]);
            
            Log::info('PAUS User authenticated: ' . $pausUser->getEmail());
            
            $pausId = $pausUser->getId();
            $email = $pausUser->getEmail();

            // 1. Cari user berdasarkan paus_id terlebih dahulu (jika ada)
            $user = null;
            if ($pausId) {
                $user = User::where('paus_id', $pausId)->first();
            }

            // Jika tidak ditemukan berdasarkan paus_id, cari berdasarkan email (jika ada)
            if (!$user && $email) {
                $user = User::where('email', $email)->first();
            }

            if ($user) {
                // Update data PAUS jika sudah ada
                $user->update([
                    'paus_id' => $pausId,
                    'paus_username' => $pausUser->getNickname(),
                    'name' => $pausUser->getName(), // Update nama sesuai PAUS
                ]);
            } else {
                // 2. Buat user baru jika belum terdaftar
                $user = User::create([
                    'name' => $pausUser->getName(),
                    'email' => $email,
                    'paus_id' => $pausId,
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
