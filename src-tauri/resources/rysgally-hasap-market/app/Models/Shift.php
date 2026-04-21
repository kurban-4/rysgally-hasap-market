<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = ['user_id', 'opened_at', 'closed_at', 'total_revenue', 'status'];
    
    // Кастуем даты, чтобы удобно работать с ними через библиотеку Carbon
    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
