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
        'claimed_by',
        'claimed_by_user_id',
        'claimed_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'claimed_at'      => 'datetime',
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

    /** The admin user who first claimed this conversation. */
    public function claimedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }

    // ── Claim helpers ─────────────────────────────────────────────────────

    /** True if no admin has replied yet. */
    public function isUnclaimed(): bool
    {
        return $this->claimed_by === null;
    }

    /** True if the given panel ('oz_admin' or 'huashu') owns this thread. */
    public function isClaimedBy(string $panel): bool
    {
        return $this->claimed_by === $panel;
    }

    /**
     * Human-readable label for which team owns the conversation.
     * Shown in the claim banner so admins know who to coordinate with.
     */
    public function claimedByLabel(): string
    {
        return match ($this->claimed_by) {
            'oz_admin' => 'OZ Admin Team',
            'huashu'   => 'Huashu Team',
            default    => 'Unknown',
        };
    }

    /**
     * Claim this conversation for the given panel + user.
     * No-op if already claimed (first-writer wins).
     */
    public function claimFor(string $panel, int $userId): void
    {
        if ($this->claimed_by !== null) {
            return; // already claimed — do not overwrite
        }

        $this->update([
            'claimed_by'         => $panel,
            'claimed_by_user_id' => $userId,
            'claimed_at'         => now(),
        ]);
    }

    // ── Unread helpers ────────────────────────────────────────────────────

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
