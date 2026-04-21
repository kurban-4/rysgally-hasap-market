<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if locale is in the session and is allowed
        $allowedLocales = ['tm', 'ru', 'en'];
        
        $locale = $request->session()->get('locale');
        $currentAppLocale = app()->getLocale();
        
        Log::debug('LocaleMiddleware: Checking locale', [
            'session_locale' => $locale,
            'current_app_locale' => $currentAppLocale,
            'session_all' => $request->session()->all()
        ]);
        
        if ($locale && in_array($locale, $allowedLocales)) {
            app()->setLocale($locale);
            Log::debug('LocaleMiddleware: Setting locale to ' . $locale);
        } else {
            // Set default locale from env or use 'en'
            $defaultLocale = env('APP_LOCALE', 'en');
            if (in_array($defaultLocale, $allowedLocales)) {
                app()->setLocale($defaultLocale);
                Log::debug('LocaleMiddleware: Setting default locale to ' . $defaultLocale);
            }
        }
        
        return $next($request);
    }
}

