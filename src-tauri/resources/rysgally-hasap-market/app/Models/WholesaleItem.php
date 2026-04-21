<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleItem extends Model
{
    protected $fillable = [
        'wholesale_invoice_id',
        'product_id',
        'unit_type',
        'quantity',
        'unit_price',
        'discount_percent',
        'row_total',
        'expiry_date_text',
        'batch_number_text',
    ];

    protected $casts = [
        'quantity'         => 'decimal:3',
        'unit_price'       => 'decimal:2',
        'discount_percent' => 'integer',
        'row_total'        => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(WholesaleInvoice::class, 'wholesale_invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** True if this line item is weight-based */
    public function getIsWeightAttribute(): bool
    {
        return ($this->unit_type ?? $this->product->unit_type ?? 'piece') === 'weight';
    }

    /** Human-readable quantity: "12.500 kg" or "8" */
    public function getDisplayQuantityAttribute(): string
    {
        if ($this->is_weight) {
            return number_format((float) $this->quantity, 3, '.', '') . ' kg';
        }
        return (string) (int) $this->quantity;
    }

    /** Get item quantity (units or items) */
    public function getItemAttribute(): float
    {
        if ($this->is_weight) {
            // For weight-based, item is number of units
            $unitsPerKg = $this->product ? ($this->product->units_per_box ?? 1) : 1;
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
            // For piece-based, assume 0 weight
            return 0.0;
        }
    }
}
