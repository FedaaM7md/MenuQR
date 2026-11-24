<?php

/*
 * MenuQR - MenuOption.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuOption extends Model
{
    use HasFactory;

    protected $table = 'options';

    protected $fillable = [
        'option_group_id',
        'name_ar',
        'name_en',
        'price_delta_minor',
        'is_available',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_delta_minor' => 'integer',
            'is_available' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(OptionGroup::class, 'option_group_id');
    }

    public function name(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar' ? $this->name_ar : ($this->name_en ?: $this->name_ar);
    }
}
