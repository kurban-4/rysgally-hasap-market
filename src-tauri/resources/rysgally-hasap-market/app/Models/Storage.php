<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Storage extends Model
{
    protected $fillable = [
        'product_id',
        'barcode',
        'quantity',         
        'category',
        'expiry_date',
        'received_price',
        'selling_price',
        'discount',
        'batch_number',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'expiry_date' => 'date',
        'received_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'discount' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    
    public function getDisplayQuantityAttribute()
    {
        $unit = ($this->product->unit_type ?? 'piece') === 'weight' ? 'kg' : 'pcs';
        if ($unit === 'kg') {
            return number_format((float)$this->quantity, 3, '.', '') . ' ' . $unit;
        }
        return (int)$this->quantity . ' ' . $unit;
    }
}
