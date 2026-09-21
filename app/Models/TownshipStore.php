<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TownshipStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'city',
        'address',
        'phone',
        'manager_user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(StoreStaff::class, 'store_id');
    }

    public function retailers(): HasMany
    {
        return $this->hasMany(Retailer::class, 'store_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'store_id');
    }

    public function stockLevels(): HasMany
    {
        return $this->hasMany(StockLevel::class, 'store_id');
    }

    public function productStorePrices(): HasMany
    {
        return $this->hasMany(ProductStorePrice::class, 'store_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
