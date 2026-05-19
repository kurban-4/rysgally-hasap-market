<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        
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
            
            $defaultLocale = env('APP_LOCALE', 'en');
            if (in_array($defaultLocale, $allowedLocales)) {
                app()->setLocale($defaultLocale);
                Log::debug('LocaleMiddleware: Setting default locale to ' . $defaultLocale);
            }
        }
        
        return $next($request);
    }
}

