<?php

/*
 * MenuQR - Money.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Support;

class Money
{
    /**
     * Convert a human decimal amount ("45.75") into integer minor units.
     */
    public static function toMinor(string|int|float|null $amount): int
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        $normalized = str_replace([',', ' '], '', (string) $amount);

        return (int) round(((float) $normalized) * 100);
    }

    /**
     * Convert minor units into a plain decimal string ("45.75").
     */
    public static function decimal(int $minor): string
    {
        return number_format($minor / 100, 2, '.', '');
    }

    public static function format(int $minor, ?string $currency = null): string
    {
        $currency ??= setting()->currency ?? 'EGP';
        $label = __('menuqr.currency.'.strtolower($currency));

        return number_format($minor / 100, 2, '.', ',').' '.$label;
    }

    /**
     * Percent stored in basis points (1400 = 14%) applied to a minor amount.
     */
    public static function percentOf(int $minor, int $basisPoints): int
    {
        return (int) round($minor * $basisPoints / 10000);
    }

    public static function percentLabel(int $basisPoints): string
    {
        return rtrim(rtrim(number_format($basisPoints / 100, 2, '.', ''), '0'), '.').'%';
    }
}
