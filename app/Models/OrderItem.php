<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_option_id',
        'variant_label',
        'qty',
        'unit_price_pkr',
        'huashu_unit_price_pkr',
        'commission_rate',
    ];

    protected $casts = [
        'qty'                   => 'integer',
        'unit_price_pkr'        => 'decimal:2',
        'line_total_pkr'        => 'decimal:2', // GENERATED ALWAYS AS — read-only
        'huashu_unit_price_pkr' => 'decimal:2',
        'commission_rate'       => 'decimal:4',
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

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * OZ commission for this line item.
     *
     * Commission is the category-defined flat percentage of the retailer's
     * payment (unit_price_pkr × qty × commission_rate). The rate is snapshotted
     * from the active category commission at order-placement time, so it is
     * locked and cannot be affected by future rate changes.
     */
    public function ozCommissionPkr(): float
    {
        if (! $this->commission_rate || ! $this->unit_price_pkr) {
            return 0.0;
        }

        return round((float) $this->unit_price_pkr * $this->qty * (float) $this->commission_rate, 2);
    }
}
