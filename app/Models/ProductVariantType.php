<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariantType extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductVariantOption::class, 'variant_type_id')
            ->orderBy('display_order')
            ->orderBy('id');
    }

    public function activeOptions(): HasMany
    {
        return $this->hasMany(ProductVariantOption::class, 'variant_type_id')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('id');
    }
}
