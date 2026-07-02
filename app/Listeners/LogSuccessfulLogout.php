<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;

class LogSuccessfulLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if ($user) {
            DB::table('activity_logs')->insert([
                'user_id'       => $user->id,
                'activity_type' => 'logout',
                'cctv_id'       => null,
                'details'       => json_encode([
                    'user_agent' => request()->userAgent(),
                ]),
                'ip_address'    => request()->ip(),
                'created_at'    => now(),
            ]);
        }
    }
}
