<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class License extends Model
{
    // Laravel сам найдет таблицу 'licenses' (с S), 
    // так как модель называется License. Это стандарт.
    protected $fillable = ['key', 'is_activated', 'activated_at'];

    public static function isActivated(): bool
    {
        // Проверяем 'licenses' (с S), как в твоей последней миграции
        if (!Schema::hasTable('licenses')) {
            return false;
        }

        return self::where('is_activated', true)->exists();
    }

    public static function validate(string $key): bool
    {
        $secret = 'rysgally-hasap';
        
        // Минимум 17 символов: RYSGALLY- (9) + Код (8)
        if (strlen($key) < 17) {
            return false;
        }

        $randomPart = substr($key, 9, 8); 
        $hash = strtoupper(substr(hash('sha256', $secret . $randomPart), 0, 4));
        $expectedKey = 'RYSGALLY-' . $randomPart . '-' . $hash;
        
        return $key === $expectedKey;
    }
}