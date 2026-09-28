<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class StoreStaffPolicy
{
    /**
     * Staff can only interact with orders belonging to their assigned store.
     */
    public function manageOrder(User $user, Order $order): bool
    {
        if (! $user->isStoreStaff() || ! $user->activeStoreStaff) {
            return false;
        }

        return $user->activeStoreStaff->store_id === $order->store_id;
    }

    /**
     * Managers and operators may update stock.
     */
    public function manageStock(User $user): bool
    {
        if (! $user->isStoreStaff() || ! $user->activeStoreStaff) {
            return false;
        }

        return in_array($user->activeStoreStaff->position, ['manager', 'operator']);
    }
}
