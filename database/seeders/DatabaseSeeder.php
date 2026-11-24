<?php

/*
 * MenuQR - DatabaseSeeder.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // This is DEMO data (labelled demo in the UI and README), not real data.
        $this->call(DemoSeeder::class);
    }
}
