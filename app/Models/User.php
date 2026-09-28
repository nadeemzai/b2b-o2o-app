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

    // ──────────────────────────────────────────────────────────────────────────
    // Multi-role pivot relationship
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * All roles this user holds (from the user_roles pivot table).
     */
    public function portalRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * Check whether the user has a specific portal role.
     * Checks the user_roles pivot; falls back to the legacy users.role column
     * so existing admin/huashu users work before a back-fill is run.
     */
    public function hasPortalRole(string $role): bool
    {
        // Fast in-memory check if portalRoles were eager-loaded
        if ($this->relationLoaded('portalRoles')) {
            $found = $this->portalRoles->contains('role', $role);
            if ($found) {
                return true;
            }
        } else {
            if ($this->portalRoles()->where('role', $role)->exists()) {
                return true;
            }
        }

        // Fallback: legacy single-role column
        return $this->role === $role;
    }

    /**
     * Returns all role strings for this user (pivot + legacy).
     */
    public function allPortalRoles(): array
    {
        $pivot  = $this->portalRoles()->pluck('role')->toArray();
        $legacy = $this->role ? [$this->role] : [];
        return array_values(array_unique(array_merge($pivot, $legacy)));
    }

    /**
     * Grant a portal role (idempotent — safe to call multiple times).
     */
    public function grantPortalRole(string $role): void
    {
        $this->portalRoles()->firstOrCreate(['role' => $role]);
    }

    /**
     * Revoke a portal role.
     */
    public function revokePortalRole(string $role): void
    {
        $this->portalRoles()->where('role', $role)->delete();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Filament panel access
    // ──────────────────────────────────────────────────────────────────────────

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return match ($panel->getId()) {
            'admin'  => $this->hasPortalRole('admin'),
            'store'  => $this->hasPortalRole('store_staff'),
            'huashu' => $this->hasPortalRole('huashu') || $this->hasPortalRole('oz_admin'),
            default  => false,
        };
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Role helpers (backward-compatible — still check legacy column AND pivot)
    // ──────────────────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->hasPortalRole('admin');
    }

    public function isRetailer(): bool
    {
        return $this->hasPortalRole('retailer');
    }

    public function isBuyer(): bool
    {
        return $this->hasPortalRole('buyer');
    }

    public function isStoreStaff(): bool
    {
        return $this->hasPortalRole('store_staff');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────────────────────────────────

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

    // ──────────────────────────────────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
