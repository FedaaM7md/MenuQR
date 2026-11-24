<?php

/*
 * MenuQR - RoleHome.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Support;

use App\Models\User;

class RoleHome
{
    public static function for(User $user): string
    {
        return match ($user->role) {
            User::ROLE_ADMIN => route('admin.dashboard'),
            User::ROLE_CASHIER => route('staff.orders.index'),
            User::ROLE_KITCHEN => route('staff.kitchen.index'),
            default => route('login'),
        };
    }
}
