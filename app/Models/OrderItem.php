<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /**
     * OrderItem has no timestamps in the migration.
     */
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'qty',
        'unit_price_pkr',
    ];

    protected $casts = [
        'qty'            => 'integer',
        'unit_price_pkr' => 'decimal:2',
        'line_total_pkr' => 'decimal:2', // GENERATED ALWAYS AS — read-only from DB
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
}
