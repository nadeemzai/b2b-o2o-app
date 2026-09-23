<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'name_en',
        'name_ur',
        'description_en',
        'description_ur',
        'category_id',
        'unit',
        'pieces_per_carton',
        'huashu_base_price_pkr',
        'moq',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active'             => 'boolean',
        'huashu_base_price_pkr' => 'decimal:2',
        'moq'                   => 'integer',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    /**
     * @deprecated — legacy per-store prices; use PricingService instead.
     */
    public function storePrices(): HasMany
    {
        return $this->hasMany(ProductStorePrice::class);
    }

    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function variantTypes(): HasMany
    {
        return $this->hasMany(ProductVariantType::class)
            ->orderBy('display_order')
            ->orderBy('id');
    }

    /** True if at least one active variant type with options exists. */
    public function hasVariants(): bool
    {
        return $this->variantTypes()->whereHas('activeOptions')->exists();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // ──────────────────────────────────────────────
    // Convenience
    // ──────────────────────────────────────────────

    /**
     * @deprecated — legacy per-store price lookup; use PricingService::retailerPrice() instead.
     */
    public function priceForStore(int $storeId): ?ProductStorePrice
    {
        return $this->storePrices()
            ->where('store_id', $storeId)
            ->where('is_active', true)
            ->first();
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Products that have a Huashu base price set (i.e. available for sale).
     */
    public function scopeWithPrice($query)
    {
        return $query->whereNotNull('huashu_base_price_pkr');
    }

    /**
     * @deprecated — legacy scope; use scopeWithPrice() for new code.
     */
    public function scopeAvailableInStore($query, int $storeId)
    {
        return $query->whereHas('storePrices', function ($q) use ($storeId) {
            $q->where('store_id', $storeId)->where('is_active', true);
        });
    }
}
