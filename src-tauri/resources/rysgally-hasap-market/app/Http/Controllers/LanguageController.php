<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LanguageController extends Controller
{
    public function switch(Request $request, $locale)
    {
        Log::debug('LanguageController: Attempting to switch locale to ' . $locale);
        
        
        $allowedLocales = ['en', 'ru', 'tm'];
        
        if (in_array($locale, $allowedLocales)) {
            
            $request->session()->put('locale', $locale);
            
            
            app()->setLocale($locale);
            
            Log::debug('LanguageController: Successfully saved locale', [
                'locale' => $locale,
                'session_locale' => $request->session()->get('locale')
            ]);
        } else {
            Log::warning('LanguageController: Invalid locale requested', ['locale' => $locale]);
        }
        
        
        return redirect()->back();
    }
}
