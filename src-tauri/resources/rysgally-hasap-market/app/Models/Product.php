<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'product_code',
        'barcode',
        'unit_type',        
        'units_per_box',    
        'total_quantity_units', 
        'price',
        'received_price',
        'profit_margin',
        'discount',
        'description',
        'manufacturer',
        'category',
        'produced_date',
        'expiry_date',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'received_price' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'discount' => 'integer',
        'units_per_box' => 'integer',
        'total_quantity_units' => 'decimal:3',
        'produced_date' => 'date',
        'expiry_date' => 'date',
    ];

    
    public function barcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function storage(): HasMany
    {
        return $this->hasMany(Storage::class, 'product_id', 'id');
    }

    public function wholesaleStorage(): HasMany
    {
        return $this->hasMany(WholesaleStorage::class, 'product_id', 'id');
    }

    
    public function getTotalWholesaleStorageAttribute()
    {
        return $this->wholesaleStorage()->sum('quantity');
    }

    
    public function getDiscountedPriceAttribute(): string
    {
        $price = (float) ($this->price ?? 0);
        $discount = (int) ($this->discount ?? 0);

        if ($discount > 0 && $price > 0) {
            $value = $price * (1 - ($discount / 100));
            return number_format($value, 2, '.', '');
        }

        return number_format($price, 2, '.', '');
    }

    
    public function getFinalPriceAttribute(): float
    {
        $price = (float) ($this->price ?? 0);
        $discount = (int) ($this->discount ?? 0);

        if ($discount > 0 && $price > 0) {
            return $price * (1 - ($discount / 100));
        }

        return $price;
    }

    
    public function getUnitLabelAttribute(): string
    {
        return ($this->unit_type ?? 'piece') === 'weight' ? 'kg' : 'pcs';
    }
public function formatQuantity($value)
{
    if ($this->unit_type === 'weight') {
        return number_format($value, 3) . ' ' . ($this->unit_label ?? 'kg');
    }
    return number_format($value, 0) . ' ' . ($this->unit_label ?? 'pcs');
}


public function isWeight(): bool
{
    return $this->unit_type === 'weight';
}
}
