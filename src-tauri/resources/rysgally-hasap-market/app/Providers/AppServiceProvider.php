<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; 

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($storagePath = env('TAURI_STORAGE_PATH')) {
            $this->app->useStoragePath($storagePath);
        }

        if ($bootstrapPath = env('TAURI_BOOTSTRAP_PATH')) {
            $this->app->useBootstrapPath($bootstrapPath);
        }
    }

    
    
    
    
    
}
