<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
        // Set default string length untuk MySQL
        Schema::defaultStringLength(191);
        
        // Set MySQL timezone ke Asia/Jakarta saat boot (backup jika PDO init command tidak bekerja)
        try {
            DB::statement("SET time_zone = '+07:00'");
        } catch (\Exception $e) {
            // Ignore jika database belum tersedia
        }
    }
}
