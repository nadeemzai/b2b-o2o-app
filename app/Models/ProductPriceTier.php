<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPriceTier extends Model
{
    protected $fillable = [
        'product_id',
        'min_qty',
        'max_qty',
        'price_pkr',
        'label',
    ];

    protected $casts = [
        'min_qty'   => 'integer',
        'max_qty'   => 'integer',
        'price_pkr' => 'decimal:2',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Human-readable range label, e.g. "10 – 49 units" or "50+ units".
     */
    public function rangeLabel(string $unit = 'units'): string
    {
        if ($this->max_qty === null) {
            return "{$this->min_qty}+ {$unit}";
        }

        if ($this->min_qty === $this->max_qty) {
            return "{$this->min_qty} {$unit}";
        }

        return "{$this->min_qty} – {$this->max_qty} {$unit}";
    }

    /**
     * True if the given quantity falls within this tier's band.
     */
    public function matchesQty(int $qty): bool
    {
        if ($qty < $this->min_qty) {
            return false;
        }

        return $this->max_qty === null || $qty <= $this->max_qty;
    }
}
