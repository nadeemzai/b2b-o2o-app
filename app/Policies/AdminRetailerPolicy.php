<?php

namespace App\Policies;

use App\Models\Retailer;
use App\Models\User;

/**
 * Gate checks for admin actions on Retailer KYC.
 *
 * Register in AuthServiceProvider:
 *   \App\Models\Retailer::class => \App\Policies\AdminRetailerPolicy::class,
 *
 * (The RetailerPolicy handles retailer-owned resources; this policy handles
 *  admin operations on the Retailer model.)
 */
class AdminRetailerPolicy
{
    /**
     * Only admins may list, approve or reject retailers.
     */
    public function before(User $user): bool|null
    {
        return $user->role === 'admin' ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return false; // handled by before()
    }

    public function approve(User $user, Retailer $retailer): bool
    {
        return false; // handled by before()
    }

    public function reject(User $user, Retailer $retailer): bool
    {
        return false; // handled by before()
    }
}
