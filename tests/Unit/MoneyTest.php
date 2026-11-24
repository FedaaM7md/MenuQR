<?php

/*
 * MenuQR - MoneyTest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_converts_decimal_input_to_integer_piasters(): void
    {
        $this->assertSame(4575, Money::toMinor('45.75'));
        $this->assertSame(4570, Money::toMinor('45.7'));
        $this->assertSame(123456, Money::toMinor('1,234.56'));
        $this->assertSame(0, Money::toMinor('0'));
        $this->assertSame(0, Money::toMinor(null));
        $this->assertSame(10001, Money::toMinor('100.005'));
    }

    public function test_formats_minor_units_as_decimal_string(): void
    {
        $this->assertSame('45.75', Money::decimal(4575));
        $this->assertSame('0.05', Money::decimal(5));
        $this->assertSame('0.00', Money::decimal(0));
    }

    public function test_applies_basis_point_percentages_in_minor_units(): void
    {
        $this->assertSame(1400, Money::percentOf(10000, 1400));
        $this->assertSame(1400, Money::percentOf(9999, 1400));
        $this->assertSame(3, Money::percentOf(25, 1000)); // 2.5 rounds half up
        $this->assertSame(0, Money::percentOf(1000, 0));
    }

    public function test_labels_basis_points_as_percentage(): void
    {
        $this->assertSame('14%', Money::percentLabel(1400));
        $this->assertSame('12.5%', Money::percentLabel(1250));
        $this->assertSame('0%', Money::percentLabel(0));
    }
}
