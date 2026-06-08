<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Models\User;
use Exception;

class PAuSIDController extends Controller
{
    public function redirectToProvider()
    {
        return Socialite::driver('paus')->stateless()->redirect();
    }

    public function handleProviderCallback(Request $request)
    {
        try {
            $pausUser = Socialite::driver('paus')->stateless()->user();
            $token = $pausUser->token;

            $response = Http::withToken($token)->get('https://paus.unpad.ac.id/api/accounts');

            if (!$response->successful()) {
                abort($response->status(), 'Gagal akses API');
            }

            $data = $response->json();

            // $accounts = collect($data['accounts'] ?? []);
            // $allowedGroupCodes = ['staff', 'lecturer', 'student'];

            // // Filter for active accounts with allowed group_code
            // $activeGroups = $accounts
            //     ->where('type_code', 'active')
            //     ->whereIn('group_code', $allowedGroupCodes)
            //     ->pluck('group_code');

            // // Map special cases (graduated, retired)
            // $specialGroups = $accounts
            //     ->filter(fn($acc) => in_array($acc['type_code'], ['graduated', 'retired-staff', 'retired-lecturer']))
            //     ->map(fn($acc) => match ($acc['type_code']) {
            //         'graduated' => 'graduated',
            //         'retired-staff' => 'retired-staff',
            //         'retired-lecturer' => 'retired-lecturer',
            //     });

            // // Merge and deduplicate all allowed groups
            // $groupCodes = $activeGroups->merge($specialGroups)->unique()->values();

            // if ($groupCodes->isEmpty()) {
            //     return view('message.not-allowed', [
            //         'message' => 'Akun tidak aktif atau tidak memiliki akses yang diizinkan.'
            //     ]);
            // }

            session([
                // 'group_codes' => $groupCodes->toArray(),
                'profil_picture' => Arr::get($pausUser->user, 'image_url'),
            ]);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'paus_id' => $data['id'],
                    'paus_username' => $data['username'],
                    'password' => bcrypt(Str::random(24)),
                    'role' => 'user', // Default: View Only (User Biasa)
                ]
            );

            // $user->update([
            //     'last_login_at' => now(),
            //     'last_login_ip' => $request->ip(),
            // ]);

            Auth::login($user);

            return redirect()->intended('/dashboard');
        } catch (Exception $e) {
            return redirect('/login')->with('error', 'Gagal login menggunakan SSO Unpad: ' . $e->getMessage());
        }
    }
}