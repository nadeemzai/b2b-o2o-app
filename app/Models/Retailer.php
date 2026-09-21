<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Retailer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_id',
        'business_name',
        'cnic',
        'ntn',
        'strn',
        'phone',
        'address',
        'kyc_status',
        'kyc_rejection_reason',
        'kyc_documents',
    ];

    protected $casts = [
        'kyc_documents' => 'array',
    ];

    // ──────────────────────────────────────────────
    // KYC helpers
    // ──────────────────────────────────────────────

    public function isApproved(): bool
    {
        return $this->kyc_status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->kyc_status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->kyc_status === 'rejected';
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(TownshipStore::class, 'store_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeApproved($query)
    {
        return $query->where('kyc_status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('kyc_status', 'pending');
    }
}
