<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
        public function locale($locale)
    {
        $locale = in_array($locale, ['tm', 'ru']) ? $locale : 'en';
        session()->put('locale', $locale);
        return redirect()->back();
    }
}
