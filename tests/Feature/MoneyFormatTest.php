<?php

/*
 * MenuQR - MoneyFormatTest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoneyFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_formats_money_with_the_egp_label_in_both_locales(): void
    {
        setting()->update(['currency' => 'EGP']);

        app()->setLocale('en');
        $this->assertSame('45.75 EGP', Money::format(4575));

        app()->setLocale('ar');
        $this->assertSame('45.75 ج.م', Money::format(4575));
    }
}
