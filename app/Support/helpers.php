<?php

/*
 * MenuQR - helpers.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */
use App\Models\Setting;
use App\Support\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

if (! function_exists('setting')) {
    function setting(): Setting
    {
        return Setting::current();
    }
}

if (! function_exists('money')) {
    function money(?int $minor, ?string $currency = null): string
    {
        return Money::format((int) $minor, $currency);
    }
}

if (! function_exists('percent')) {
    function percent(int $basisPoints): string
    {
        return Money::percentLabel($basisPoints);
    }
}

if (! function_exists('is_rtl')) {
    function is_rtl(): bool
    {
        return app()->getLocale() === 'ar';
    }
}

if (! function_exists('text_dir')) {
    function text_dir(): string
    {
        return is_rtl() ? 'rtl' : 'ltr';
    }
}

if (! function_exists('format_time')) {
    function format_time(?Carbon $time): string
    {
        return $time ? $time->translatedFormat('g:i A') : '—';
    }
}

if (! function_exists('format_datetime')) {
    function format_datetime(?Carbon $time, string $dateFormat = 'd/m/Y'): string
    {
        return $time ? $time->translatedFormat($dateFormat.' g:i A') : '—';
    }
}

if (! function_exists('media_url')) {
    function media_url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
