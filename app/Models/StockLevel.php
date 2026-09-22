<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLevel extends Model
{
    use HasFactory;

    /**
     * StockLevel has no created_at — only updated_at (managed by the DB CURRENT_TIMESTAMP).
     * We disable Laravel's auto-manage of timestamps and declare the single column manually.
     */
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'store_id',
        'qty_on_hand',
        'qty_reserved',
    ];

    protected $casts = [
        'qty_on_hand'   => 'integer',
        'qty_reserved'  => 'integer',
        'qty_available' => 'integer', // GENERATED ALWAYS AS — read-only from DB
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

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

    public function hasEnoughStock(int $qty): bool
    {
        return $this->qty_available >= $qty;
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeForStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeInStock($query)
    {
        // qty_available is a generated column so we can filter on it
        return $query->whereRaw('qty_available > 0');
    }
}
