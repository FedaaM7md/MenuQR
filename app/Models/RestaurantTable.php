<?php

/*
 * MenuQR - RestaurantTable.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RestaurantTable extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'token', 'seats', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'seats' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $table) {
            if (blank($table->token)) {
                $table->token = self::generateToken();
            }
        });
    }

    public static function generateToken(): string
    {
        return Str::random(48);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function regenerateToken(): void
    {
        $this->forceFill(['token' => self::generateToken()])->save();
    }

    public function publicUrl(): string
    {
        return route('menu.show', $this->token);
    }
}
