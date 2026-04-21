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

    /** True if the linked product is sold by weight (kg) */
    public function getIsWeightAttribute(): bool
    {
        return ($this->product->unit_type ?? 'piece') === 'weight';
    }

    /** Human-readable quantity: "12.500 kg" or "8 items" */
    public function getDisplayQuantityAttribute(): string
    {
        if ($this->is_weight) {
            return number_format((float) $this->quantity, 3, '.', '') . ' kg';
        }
        return (int) $this->quantity . ' items';
    }

    /** Unit label for display */
    public function getUnitLabelAttribute(): string
    {
        return $this->is_weight ? 'kg' : 'items';
    }

    /** Is stock level low? */
    public function getIsLowStockAttribute(): bool
    {
        if ($this->is_weight) {
            return (float) $this->quantity < 5.0;   // less than 5 kg
        }
        return (int) $this->quantity < 10;           // less than 10 items
    }

    /** Get item quantity (units or items) */
    public function getItemAttribute(): float
    {
        if ($this->is_weight) {
            // For weight-based, item is number of units
            $unitsPerKg = $this->product ? ($this->product->units_per_box ?? 1) : 1; // assume units per kg
            return (float) $this->quantity * $unitsPerKg;
        } else {
            // For piece-based, item is items
            return (int) $this->quantity;
        }
    }

    /** Get weight quantity (kg) */
    public function getWeightAttribute(): float
    {
        if ($this->is_weight) {
            return (float) $this->quantity;
        } else {
            // For piece-based, assume 1 kg per box or something, but perhaps 0
            return 0.0;
        }
    }
}
