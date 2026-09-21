<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\Retailer;
use App\Models\User;

class RetailerPolicy
{
    /**
     * Only KYC-approved retailers may place / view orders.
     */
    public function placeOrder(User $user): bool
    {
        return $user->isRetailer()
            && $user->retailerProfile
            && $user->retailerProfile->isApproved();
    }

    /**
     * A retailer may view only their own orders.
     */
    public function viewOrder(User $user, Order $order): bool
    {
        if (! $user->isRetailer() || ! $user->retailerProfile) {
            return false;
        }

        return $user->retailerProfile->id === $order->retailer_id;
    }

    /**
     * A retailer may cancel only their own pending/preparing orders.
     */
    public function cancelOrder(User $user, Order $order): bool
    {
        return $this->viewOrder($user, $order) && $order->isCancellable();
    }
}
