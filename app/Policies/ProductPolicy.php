<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * Back-office permissions for the catalog. Storefront visibility is not a
 * policy concern: public pages rely on the Product::active() scope.
 */
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }
}
