<?php

/*
 * MenuQR - MenuItem.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'allergens_ar',
        'allergens_en',
        'price_minor',
        'image_path',
        'is_available',
        'is_featured',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_minor' => 'integer',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function optionGroups(): HasMany
    {
        return $this->hasMany(OptionGroup::class)->orderBy('sort_order')->orderBy('id');
    }

    public function name(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar' ? $this->name_ar : ($this->name_en ?: $this->name_ar);
    }

    public function description(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar'
            ? $this->description_ar
            : ($this->description_en ?: $this->description_ar);
    }

    public function allergens(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar'
            ? $this->allergens_ar
            : ($this->allergens_en ?: $this->allergens_ar);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
