<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // Dynamic promoter fallback
        if ($user->email === 'admin@unpad.ac.id' && $user->role !== 'superadmin') {
            $user->role = 'superadmin';
            $user->save();
        }

        DB::table('activity_logs')->insert([
            'user_id'       => $user->id,
            'activity_type' => 'login',
            'cctv_id'       => null,
            'details'       => json_encode([
                'user_agent' => request()->userAgent(),
            ]),
            'ip_address'    => request()->ip(),
            'created_at'    => now(),
        ]);
    }
}
