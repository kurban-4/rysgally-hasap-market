<?php

namespace App\Http\Controllers;

use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LicenseController extends Controller
{
    public function show()
    {
        // Если база есть и всё уже активировано - пускаем на логин
        if (Schema::hasTable('licenses') && License::isActivated()) {
            return redirect('/login');
        }
        
        return view('license');
    }

    public function activate(Request $request)
    {
        $key = strtoupper(trim($request->key));

        if (!License::validate($key)) {
            return back()->withErrors(['key' => 'Неверный лицензионный ключ для RysgallyHasap']);
        }

        try {
            License::updateOrCreate(
                ['is_activated' => false], 
                [
                    'key' => $key,
                    'is_activated' => true,
                    'activated_at' => now(),
                ]
            );
            return redirect('/login')->with('success', 'Приложение активировано!');
        } catch (\Exception $e) {
            return back()->withErrors(['key' => 'Ошибка базы данных. Проверьте миграции!']);
        }
    }
}