<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'image_path',
        'link_url',
        'is_active',
        'display_order',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'display_order' => 'integer',
        'starts_at'     => 'datetime',
        'ends_at'       => 'datetime',
    ];

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /**
     * Banners that should appear right now.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('display_order');
    }
}
