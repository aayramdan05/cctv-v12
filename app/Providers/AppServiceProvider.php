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
        if (request()->header('X-Forwarded-Proto') == 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Register PAUS Socialite Driver
        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend('paus', function ($app) use ($socialite) {
            $config = $app['config']['services.paus'];
            return $socialite->buildProvider(\App\Socialite\PausProvider::class, $config);
        });
    }
}
