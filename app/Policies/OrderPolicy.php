<?php

/*
 * MenuQR - OrderPolicy.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_CASHIER);
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_CASHIER);
    }

    public function updateStatus(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_CASHIER, User::ROLE_KITCHEN);
    }

    public function cancel(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN, User::ROLE_CASHIER);
    }
}
