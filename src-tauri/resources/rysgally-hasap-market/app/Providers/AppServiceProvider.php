<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // СТРОКА ДОЛЖНА БЫТЬ ЗДЕСЬ

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
    // public function boot(): void
    // {
    //     Paginator::useBootstrapFive(); 
    //     view()->share('lowStockCount', \App\Models\Storage::where('quantity', '<', 10)->count());
    // }
}
