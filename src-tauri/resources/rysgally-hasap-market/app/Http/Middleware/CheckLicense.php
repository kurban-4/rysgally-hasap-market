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
        // 1. Пропускаем всё, что связано со страницей лицензии
        if ($request->is('license*') || $request->is('api*')) {
            return $next($request);
        }

        // 2. Если таблицы нет ИЛИ лицензия не активирована — кидаем на страницу ввода ключа
        // Используем путь '/license', чтобы не зависеть от ->name()
        if (!Schema::hasTable('licenses') || !License::isActivated()) {
            return redirect('/license');
        }

        return $next($request);
    }
}