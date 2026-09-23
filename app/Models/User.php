<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // ──────────────────────────────────────────────
    // Filament panel access
    // ──────────────────────────────────────────────

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return match ($panel->getId()) {
            'admin'  => $this->role === 'admin',
            'store'  => $this->role === 'store_staff',
            'huashu' => in_array($this->role, ['huashu', 'oz_admin']),
            default  => false,
        };
    }

    // ──────────────────────────────────────────────
    // Role helpers
    // ──────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isRetailer(): bool
    {
        return $this->role === 'retailer';
    }

    public function isStoreStaff(): bool
    {
        return $this->role === 'store_staff';
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function retailerProfile(): HasOne
    {
        return $this->hasOne(Retailer::class);
    }

    public function storeStaffAssignments(): HasMany
    {
        return $this->hasMany(StoreStaff::class);
    }

    public function activeStoreStaff(): HasOne
    {
        return $this->hasOne(StoreStaff::class)->where('is_active', true);
    }

    public function managedStores(): HasMany
    {
        return $this->hasMany(TownshipStore::class, 'manager_user_id');
    }

    public function stockMovementsCreated(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'created_by_user_id');
    }

    public function orderStatusChanges(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class, 'changed_by_user_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
