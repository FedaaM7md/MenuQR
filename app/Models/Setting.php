<?php

/*
 * MenuQR - Setting.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    private const CACHE_KEY = 'menuqr.settings.current';

    protected $fillable = [
        'restaurant_name_ar',
        'restaurant_name_en',
        'tagline_ar',
        'tagline_en',
        'logo_path',
        'currency',
        'tax_percent',
        'service_percent',
        'whatsapp_number',
        'hours',
        'address_ar',
        'address_en',
        'is_accepting_orders',
    ];

    protected function casts(): array
    {
        return [
            'hours' => 'array',
            'is_accepting_orders' => 'boolean',
            'tax_percent' => 'integer',
            'service_percent' => 'integer',
        ];
    }

    public static function current(): self
    {
        $attributes = Cache::remember(self::CACHE_KEY, 3600, function () {
            return static::query()->firstOrCreate(['id' => 1], [
                'restaurant_name_ar' => 'مطعم جديد',
                'restaurant_name_en' => 'New Restaurant',
                'currency' => 'EGP',
                'tax_percent' => 1400,
                'service_percent' => 0,
                'hours' => self::defaultHours(),
                'is_accepting_orders' => true,
            ])->getAttributes();
        });

        return (new static)->newFromBuilder($attributes);
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * @return array<string, array{open: string, close: string, closed: bool}>
     */
    public static function defaultHours(): array
    {
        $hours = [];
        foreach (['sat', 'sun', 'mon', 'tue', 'wed', 'thu', 'fri'] as $day) {
            $hours[$day] = ['open' => '10:00', 'close' => '23:00', 'closed' => false];
        }

        return $hours;
    }

    public function name(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar'
            ? $this->restaurant_name_ar
            : ($this->restaurant_name_en ?: $this->restaurant_name_ar);
    }

    public function tagline(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar'
            ? $this->tagline_ar
            : ($this->tagline_en ?: $this->tagline_ar);
    }

    public function address(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar'
            ? $this->address_ar
            : ($this->address_en ?: $this->address_ar);
    }

    /**
     * Build a WhatsApp deep link with a prefilled message (no API).
     */
    public function whatsappLink(string $message): ?string
    {
        if (blank($this->whatsapp_number)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $this->whatsapp_number);

        if ($digits === '') {
            return null;
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }

    public function isOpenNow(?\DateTimeInterface $now = null): bool
    {
        $now ??= now();
        $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        $todayIndex = (int) $now->format('w');
        $current = $now->format('H:i');

        $today = $this->hours[$days[$todayIndex]] ?? null;

        if (is_array($today) && ! ($today['closed'] ?? false)) {
            $open = $today['open'] ?? null;
            $close = $today['close'] ?? null;

            if ($open && $close) {
                // Support windows that cross midnight (e.g. 18:00 → 02:00).
                if ($close <= $open) {
                    if ($current >= $open || $current < $close) {
                        return true;
                    }
                } elseif ($current >= $open && $current < $close) {
                    return true;
                }
            }
        }

        // Spillover from yesterday's overnight window (e.g. Tuesday 01:00 after Monday 18:00–02:00).
        $yesterday = $this->hours[$days[($todayIndex + 6) % 7]] ?? null;

        if (is_array($yesterday) && ! ($yesterday['closed'] ?? false)) {
            $open = $yesterday['open'] ?? null;
            $close = $yesterday['close'] ?? null;

            if ($open && $close && $close <= $open && $current < $close) {
                return true;
            }
        }

        return false;
    }
}
