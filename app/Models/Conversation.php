<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'product_id',
        'retailer_id',
        'store_id',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function retailer(): BelongsTo
    {
        return $this->belongsTo(Retailer::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(TownshipStore::class, 'store_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest()->limit(1);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Unread messages from the retailer's perspective (i.e. store replies not yet read).
     */
    public function unreadByRetailer(): int
    {
        return $this->messages()
            ->where('sender_type', 'store_staff')
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Unread messages from the store's perspective (i.e. retailer messages not yet read).
     */
    public function unreadByStore(): int
    {
        return $this->messages()
            ->where('sender_type', 'retailer')
            ->whereNull('read_at')
            ->count();
    }
}
