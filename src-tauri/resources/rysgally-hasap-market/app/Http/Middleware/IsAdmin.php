<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        if (Auth::user()->role === 'admin' || Auth::user()->role === 'staff') {
            return $next($request);
        }
        return redirect('/')->with('error', 'Access denied. You are not a staff member.');
    }
}
