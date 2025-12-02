<?php

declare(strict_types=1);

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
        // Allow application to work with both HTTP and HTTPS
        // Don't force URL scheme - Laravel will automatically detect from request
        // This allows the app to work on both HTTP and HTTPS
        
        // Allow application to work with both domain names and IP addresses
        // Don't force a specific host - Laravel will use the host from the current request
    }
}
