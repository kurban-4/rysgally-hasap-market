<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Till extends Model
{
    protected $guarded = [];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}