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

    public function getDisplayQuantityAttribute(): string
    {
        if ($this->is_weight) {
            return number_format((float) $this->quantity, 3, '.', '') . ' kg';
        }
        return (string) (int) $this->quantity;
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
