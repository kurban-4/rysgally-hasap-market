<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $guarded = [];
    
protected $fillable = ['title', 'amount', 'expense_date'];
}
