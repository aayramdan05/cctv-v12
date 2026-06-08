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
    }
}
