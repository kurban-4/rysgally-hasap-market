<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LanguageController extends Controller
{
    public function switch(Request $request, $locale)
    {
        Log::debug('LanguageController: Attempting to switch locale to ' . $locale);
        
        // Validate locale is allowed
        $allowedLocales = ['en', 'ru', 'tm'];
        
        if (in_array($locale, $allowedLocales)) {
            // Save to session explicitly
            $request->session()->put('locale', $locale);
            
            // Also set the app locale immediately for this request
            app()->setLocale($locale);
            
            Log::debug('LanguageController: Successfully saved locale', [
                'locale' => $locale,
                'session_locale' => $request->session()->get('locale')
            ]);
        } else {
            Log::warning('LanguageController: Invalid locale requested', ['locale' => $locale]);
        }
        
        // Redirect back to the previous page
        return redirect()->back();
    }
}
