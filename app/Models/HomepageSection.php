<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSection extends Model
{
    protected $fillable = ['key', 'label_en', 'is_active', 'display_order'];

    protected $casts = [
        'is_active'     => 'boolean',
        'display_order' => 'integer',
    ];

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Is the "deals" section active? */
    public static function dealsActive(): bool
    {
        return (bool) static::where('key', 'deals')->value('is_active');
    }

    /** Is the "new_arrivals" section active? */
    public static function newArrivalsActive(): bool
    {
        return (bool) static::where('key', 'new_arrivals')->value('is_active');
    }

    /** Return all section states in one query. */
    public static function activeSections(): array
    {
        $rows = static::whereIn('key', ['deals', 'new_arrivals', 'show_stock_badge'])
            ->orderBy('display_order')
            ->get(['key', 'is_active']);

        return [
            'deals'            => (bool) $rows->firstWhere('key', 'deals')?->is_active,
            'new_arrivals'     => (bool) $rows->firstWhere('key', 'new_arrivals')?->is_active,
            'show_stock_badge' => (bool) $rows->firstWhere('key', 'show_stock_badge')?->is_active,
        ];
    }
}
