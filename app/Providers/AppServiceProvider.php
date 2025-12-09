<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Jika proxy mengirim header 'X-Forwarded-Proto: https', paksa HTTPS
        if (request()->header('X-Forwarded-Proto') == 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
