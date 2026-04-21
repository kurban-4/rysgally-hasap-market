<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $guarded = [];
    // app/Models/Expense.php
protected $fillable = ['title', 'amount', 'expense_date'];
}
