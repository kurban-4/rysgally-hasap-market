<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\License; 

class CheckLicense
{
    public function handle($request, Closure $next)
    {
        
        if ($request->is('license*') || $request->is('api*')) {
            return $next($request);
        }

        
        
        if (!Schema::hasTable('licenses') || !License::isActivated()) {
            return redirect('/license');
        }

        return $next($request);
    }
}