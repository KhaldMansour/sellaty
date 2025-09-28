<?php

namespace App\Policies;

use App\Models\WantedProduct;
use App\Models\User;

class WantedProductPolicy
{
    /**
     * Determine whether the user can delete the product.
     */
    public function delete(User $user, WantedProduct $wantedProduct): bool
    {
        return $user->isAdmin() || $user->id === $wantedProduct->user_id;
    }
}
