<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;

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

    public function boot()
    {
        // Use Bootstrap 5 pagination
        Paginator::useBootstrapFive();

        // Optimize Eloquent - prevent lazy loading
        Model::preventLazyLoading(!app()->isLocal());

        // Disable query logging in production for performance
        if (app()->isProduction() || app()->environment('production')) {
            \DB::connection()->disableQueryLog();
        }

        // Enable query caching for SQLite
        if (\DB::getDriverName() === 'sqlite') {
            // SQLite optimizations
            \DB::statement("PRAGMA journal_mode = WAL");      // Write-Ahead Logging for better concurrency
            \DB::statement("PRAGMA synchronous = NORMAL");     // Faster writes
            \DB::statement("PRAGMA cache_size = -64000");      // 64MB cache
            \DB::statement("PRAGMA temp_store = MEMORY");      // Use memory for temp storage
            \DB::statement("PRAGMA foreign_keys = ON");        // Enable foreign keys
        }
    }
}

