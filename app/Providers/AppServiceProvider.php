<?php

/*
 * MenuQR - AppServiceProvider.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('manage-settings', fn (User $user) => $user->isAdmin());
        Gate::define('manage-menu', fn (User $user) => $user->isAdmin());
        Gate::define('manage-tables', fn (User $user) => $user->isAdmin());
        Gate::define('manage-staff', fn (User $user) => $user->isAdmin());
        Gate::define('view-reports', fn (User $user) => $user->isAdmin());
        Gate::define('view-orders', fn (User $user) => $user->hasRole(User::ROLE_ADMIN, User::ROLE_CASHIER));
        Gate::define('view-kitchen', fn (User $user) => $user->hasRole(User::ROLE_ADMIN, User::ROLE_KITCHEN));
    }
}
