<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleInvoice extends Model
{
    protected $fillable = [
        'invoice_no',
        'customer_name',
        'total_amount'
    ];
    public function items()
    {
        return $this->hasMany(WholesaleItem::class);
    }
}
