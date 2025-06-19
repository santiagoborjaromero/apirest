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
        // error_log("register");
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // error_log("boot");
    }
}
