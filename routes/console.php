<?php

use App\Console\Commands\ResetDemoData;
use Illuminate\Support\Facades\Schedule;

/*
 * MenuQR console routes.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

// Reseed the shared demo dataset every hour. Documented in INSTALL.md.
Schedule::command(ResetDemoData::class)
    ->hourly()
    ->when(fn () => (bool) config('menuqr.demo.enabled') && (bool) config('menuqr.demo.reset_enabled'));
