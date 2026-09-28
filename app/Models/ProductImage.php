<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_url',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary'  => 'boolean',
        'sort_order'  => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /**
     * Return a usable <img src> URL regardless of whether image_url is an
     * external https:// URL or a relative storage path.
     */
    public function getDisplayUrlAttribute(): string
    {
        if (Str::startsWith($this->image_url, ['http://', 'https://'])) {
            return $this->image_url;
        }

        return asset('storage/' . $this->image_url);
    }
}
