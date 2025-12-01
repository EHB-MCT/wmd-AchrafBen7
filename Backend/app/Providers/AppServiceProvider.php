<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

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
        Inertia::setRootView('app');
        Inertia::share([
            'app' => [
                'name' => config('app.name', 'NiOS Analytics'),
                'version' => config('app.version', 'v1.0.0-beta'),
            ],
        ]);
    }
}
