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
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // ──────────────────────────────────────────────
    // Convenience
    // ──────────────────────────────────────────────

    /**
     * Price for a specific store (null if not listed).
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
     * Only products that have an active price in the given store.
     */
    public function scopeAvailableInStore($query, int $storeId)
    {
        return $query->whereHas('storePrices', function ($q) use ($storeId) {
            $q->where('store_id', $storeId)->where('is_active', true);
        });
    }
}
