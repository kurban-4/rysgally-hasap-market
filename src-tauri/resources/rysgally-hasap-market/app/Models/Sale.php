<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',       // pcs or kg
        'price',          // price per unit at time of sale
        'total_price',
        'sale_type',      // 'piece' or 'weight'
        'transaction_id',
        'customer_name',
        'till_id',
        'discount',
        'items_json',     // JSON with all sale items
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function till(): BelongsTo
    {
        return $this->belongsTo(Till::class);
    }

    // Display quantity with unit for views/exports
    public function getDisplayQuantityAttribute()
    {
        $unit = ($this->sale_type ?? $this->product->unit_type ?? 'piece') === 'weight' ? 'kg' : 'pcs';
        if ($unit === 'kg') {
            return number_format((float)$this->quantity, 3, '.', '') . ' ' . $unit;
        }
        return (int)$this->quantity . ' ' . $unit;
    }
}
