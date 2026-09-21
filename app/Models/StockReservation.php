<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'store_id',
        'qty',
        'status',
    ];

    protected $casts = [
        'qty'        => 'integer',
        'created_at' => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(TownshipStore::class, 'store_id');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'reserved';
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeReserved($query)
    {
        return $query->where('status', 'reserved');
    }
}
