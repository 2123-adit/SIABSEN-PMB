<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        // Set timezone untuk seluruh aplikasi
        date_default_timezone_set('Asia/Jakarta');
            // Register image helper
        if (!class_exists('App\Helpers\ImageHelper')) {
            require_once app_path('Helpers/ImageHelper.php');
        }
    }
}