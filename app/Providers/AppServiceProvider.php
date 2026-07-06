<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Laravel\Socialite\Facades\Socialite;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Deteksi header dari Proxy Kampus
        if (request()->header('X-Forwarded-Proto') == 'https' || request()->getHost() === 'cctv.unpad.net') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Socialite::extend('paus', function ($app) {
            $config = config('services.paus');

            return Socialite::buildProvider(PAuSIDProvider::class, $config);
        });

        // Register Activity Log Event Listeners directly
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            \App\Listeners\LogSuccessfulLogin::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Logout::class,
            \App\Listeners\LogSuccessfulLogout::class
        );
    }
}
