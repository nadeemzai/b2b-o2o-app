<?php

namespace App\Filament\Store;

use App\Models\StoreStaff;
use Illuminate\Support\Facades\Auth;

/**
 * Lightweight, per-request helper that exposes the logged-in
 * store-staff user's position (manager | operator | rider) and store_id.
 *
 * The result is memoised so the DB query only runs once per request,
 * regardless of how many places call it on the same page-load.
 */
class StoreStaffContext
{
    private static ?StoreStaff $cached = null;
    private static ?int $cachedForUser = null;

    private static function staff(): ?StoreStaff
    {
        $userId = Auth::id();

        if (static::$cachedForUser !== $userId) {
            static::$cached        = null;
            static::$cachedForUser = $userId;
        }

        if (static::$cached === null && $userId !== null) {
            static::$cached = StoreStaff::where('user_id', $userId)
                ->where('is_active', true)
                ->first();
        }

        return static::$cached;
    }

    /** Returns 'manager', 'operator', 'rider', or null. */
    public static function position(): ?string
    {
        return static::staff()?->position;
    }

    /** Returns the store_id for the current user, or null. */
    public static function storeId(): ?int
    {
        return static::staff()?->store_id;
    }

    public static function isManager(): bool
    {
        return static::position() === 'manager';
    }

    public static function isOperator(): bool
    {
        return static::position() === 'operator';
    }

    public static function isRider(): bool
    {
        return static::position() === 'rider';
    }

    /** True for manager OR operator. */
    public static function canManageOperations(): bool
    {
        return in_array(static::position(), ['manager', 'operator'], true);
    }

    /** True for manager OR rider. */
    public static function canDeliver(): bool
    {
        return in_array(static::position(), ['manager', 'rider'], true);
    }
}
