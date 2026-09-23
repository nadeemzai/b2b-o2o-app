<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantOption extends Model
{
    protected $fillable = [
        'variant_type_id',
        'value',
        'sku_suffix',
        'price_adjustment_pkr',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'price_adjustment_pkr' => 'decimal:2',
        'is_active'            => 'boolean',
        'display_order'        => 'integer',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function variantType(): BelongsTo
    {
        return $this->belongsTo(ProductVariantType::class, 'variant_type_id');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Cart item key used to store this variant choice in the session.
     */
    public function cartKey(): string
    {
        return 'p' . $this->variantType->product_id . '_v' . $this->id;
    }

    /**
     * Human-readable label for cart / order display.
     */
    public function label(): string
    {
        return $this->variantType->name . ': ' . $this->value;
    }
}
