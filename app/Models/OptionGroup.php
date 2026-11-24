<?php

/*
 * MenuQR - OptionGroup.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OptionGroup extends Model
{
    use HasFactory;

    protected $fillable = ['menu_item_id', 'name_ar', 'name_en', 'min_select', 'max_select', 'sort_order'];

    protected function casts(): array
    {
        return [
            'min_select' => 'integer',
            'max_select' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(MenuOption::class)->orderBy('sort_order')->orderBy('id');
    }

    public function name(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar' ? $this->name_ar : ($this->name_en ?: $this->name_ar);
    }

    public function isRequired(): bool
    {
        return $this->min_select > 0;
    }
}
