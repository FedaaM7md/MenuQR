<?php

/*
 * MenuQR — demo mode helpers.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Support;

use App\Models\User;

class DemoMode
{
    public static function enabled(): bool
    {
        return (bool) config('menuqr.demo.enabled');
    }

    public static function user(): ?User
    {
        $user = auth()->user();

        return $user instanceof User && $user->isDemo() ? $user : null;
    }

    public static function active(): bool
    {
        return self::user() !== null;
    }

    public static function uploadMaxKb(): int
    {
        if (self::active()) {
            return (int) config('menuqr.uploads.demo_max_kb', 512);
        }

        return (int) config('menuqr.uploads.max_kb', 2048);
    }
}
