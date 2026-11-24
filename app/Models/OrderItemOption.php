<?php

/*
 * MenuQR - OrderItemOption.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemOption extends Model
{
    protected $fillable = [
        'order_item_id',
        'group_name_ar',
        'group_name_en',
        'option_name_ar',
        'option_name_en',
        'price_delta_minor',
    ];

    protected function casts(): array
    {
        return ['price_delta_minor' => 'integer'];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function groupName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar' ? $this->group_name_ar : ($this->group_name_en ?: $this->group_name_ar);
    }

    public function optionName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar' ? $this->option_name_ar : ($this->option_name_en ?: $this->option_name_ar);
    }
}
