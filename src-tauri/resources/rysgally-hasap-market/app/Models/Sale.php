<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'product_id',
        'quantity',       
        'price',          
        'total_price',
        'sale_type',      
        'transaction_id',
        'customer_name',
        'till_id',
        'discount',
        'items_json',     
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

    
    public function getDisplayQuantityAttribute()
    {
        $unit = ($this->sale_type ?? $this->product->unit_type ?? 'piece') === 'weight' ? 'kg' : 'pcs';
        if ($unit === 'kg') {
            return number_format((float)$this->quantity, 3, '.', '') . ' ' . $unit;
        }
        return (int)$this->quantity . ' ' . $unit;
    }
}
