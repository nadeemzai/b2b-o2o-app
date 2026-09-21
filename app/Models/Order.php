<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'retailer_id',
        'store_id',
        'status',
        'total_pkr',
        'payment_method',
        'collected_pkr',
        'notes',
    ];

    protected $casts = [
        'total_pkr'     => 'decimal:2',
        'collected_pkr' => 'decimal:2',
    ];

    // ──────────────────────────────────────────────
    // Status constants
    // ──────────────────────────────────────────────

    public const STATUS_PENDING            = 'pending';
    public const STATUS_PREPARING          = 'preparing';
    public const STATUS_READY_FOR_DELIVERY = 'ready_for_delivery';
    public const STATUS_DELIVERED          = 'delivered';
    public const STATUS_CANCELLED          = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PREPARING,
        self::STATUS_READY_FOR_DELIVERY,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function retailer(): BelongsTo
    {
        return $this->belongsTo(Retailer::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(TownshipStore::class, 'store_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    public function latestStatus(): HasOne
    {
        return $this->hasOne(OrderStatusHistory::class)->latestOfMany();
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PREPARING]);
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeForRetailer($query, int $retailerId)
    {
        return $query->where('retailer_id', $retailerId);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_DELIVERED, self::STATUS_CANCELLED]);
    }
}
