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
        'unit_type',        // 'piece' or 'weight'
        'units_per_box',    // How many units per item (for weight: units per kg)
        'total_quantity_units', // Total units in storage
        'price',
        'received_price',
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
        'discount' => 'integer',
        'units_per_box' => 'integer',
        'total_quantity_units' => 'decimal:3',
        'produced_date' => 'date',
        'expiry_date' => 'date',
    ];

    // Relations
    public function storage(): HasMany
    {
        return $this->hasMany(Storage::class, 'product_id', 'id');
    }

    public function wholesaleStorage(): HasMany
    {
        return $this->hasMany(WholesaleStorage::class, 'product_id', 'id');
    }

    // Sum of wholesale quantities (helper)
    public function getTotalWholesaleStorageAttribute()
    {
        return $this->wholesaleStorage()->sum('quantity');
    }

    // Discounted price formatted
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

    // Final numeric price for calculations
    public function getFinalPriceAttribute(): float
    {
        $price = (float) ($this->price ?? 0);
        $discount = (int) ($this->discount ?? 0);

        if ($discount > 0 && $price > 0) {
            return $price * (1 - ($discount / 100));
        }

        return $price;
    }

    // Display unit label (kg or pcs)
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

// Добавим вспомогательный метод для проверки, весовой ли это товар
public function isWeight(): bool
{
    return $this->unit_type === 'weight';
}
}
