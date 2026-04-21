<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WholesaleMarketTransfer extends Model
{
    protected $fillable = [
        'product_id',
        'product_name',
        'quantity',
        'unit_type',
        'market_barcode',
        'received_price',
        'selling_price',
        'batch_number',
        'expiry_date',
        'storage_id',
        'source_batches',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'received_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'expiry_date' => 'date',
        'source_batches' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function storage(): BelongsTo
    {
        return $this->belongsTo(Storage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unitLabel(): string
    {
        return ($this->unit_type ?? 'piece') === 'weight' ? 'kg' : 'pcs';
    }
}
