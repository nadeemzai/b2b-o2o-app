<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/**
 * Categories are managed by OZ Admin only.
 *  - Huashu (and any other panel user) : view only
 *  - OZ Admin                          : create / edit / soft delete / restore
 *  - Nobody                            : permanent (force) delete
 */
class CategoryPolicy
{
    private function isOzAdmin(User $user): bool
    {
        return $user->hasPortalRole('admin') || $user->hasPortalRole('oz_admin');
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Category $category): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->isOzAdmin($user);
    }

    public function update(User $user, Category $category): bool
    {
        return $this->isOzAdmin($user);
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->isOzAdmin($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->isOzAdmin($user);
    }

    public function restore(User $user, Category $category): bool
    {
        return $this->isOzAdmin($user);
    }

    public function restoreAny(User $user): bool
    {
        return $this->isOzAdmin($user);
    }

    // Strictly soft delete — hard delete is never allowed.
    public function forceDelete(User $user, Category $category): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
