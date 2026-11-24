<?php

/*
 * MenuQR — reset the shared demo dataset.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Console\Commands;

use App\Services\Audit;
use Illuminate\Console\Command;

class ResetDemoData extends Command
{
    protected $signature = 'demo:reset {--force : Run even when DEMO_MODE is disabled}';

    protected $description = 'Rebuild the demo database and reseed the clearly labelled demo dataset';

    public function handle(): int
    {
        if (! config('menuqr.demo.enabled') && ! $this->option('force')) {
            $this->error('Demo mode is disabled (DEMO_MODE=false). Use --force only if you are sure.');

            return self::FAILURE;
        }

        $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
        Audit::log('demo.reset', null, __('menuqr.activity.descriptions.demo_reset'));

        $this->info('Demo data reset. The shared demo dataset is ready.');

        return self::SUCCESS;
    }
}
