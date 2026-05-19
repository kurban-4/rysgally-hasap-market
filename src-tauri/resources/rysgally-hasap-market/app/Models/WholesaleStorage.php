<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleStorage extends Model
{
    protected $fillable = [
        'product_id',
        'product_name',
        'quantity',
        'received_price',
        'selling_price',
        'batch_number',
        'expiry_date',
    ];

    protected $casts = [
        'quantity'       => 'decimal:3',
        'received_price' => 'decimal:2',
        'selling_price'  => 'decimal:2',
        'expiry_date'    => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getDisplayQuantityAttribute(): string
    {
        if ($this->is_weight) {
            return number_format((float) $this->quantity, 3, '.', '') . ' kg';
        }
        return (int) $this->quantity . ' items';
    }

    public function getIsLowStockAttribute(): bool
    {
        if ($this->is_weight) {
            return (float) $this->quantity < 5.0;   
        }
        return (int) $this->quantity < 10;           
    }

    public function getWeightAttribute(): float
    {
        if ($this->is_weight) {
            return (float) $this->quantity;
        } else {
            
            return 0.0;
        }
    }
}
