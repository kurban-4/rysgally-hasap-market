<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // УБРАЛИ PREPEND! Он ломал всю логику.
        // $middleware->append(\App\Http\Middleware\Cors::class); // Раскомментируй, если нужен CORS

        // Add locale middleware early to ensure it runs for all requests
        $middleware->web(\App\Http\Middleware\LocaleMiddleware::class);

        $middleware->validateCsrfTokens([
            'api/*',
        ]);

        $middleware->alias([
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
            'license' => \App\Http\Middleware\CheckLicense::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();