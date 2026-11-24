<?php

/*
 * MenuQR — user model.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_CASHIER = 'cashier';

    public const ROLE_KITCHEN = 'kitchen';

    public const ROLES = [self::ROLE_ADMIN, self::ROLE_CASHIER, self::ROLE_KITCHEN];

    /** Demo accounts always use this domain so they can be identified safely. */
    public const DEMO_EMAIL_DOMAIN = 'demo.menuqr.test';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'is_demo',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_demo' => 'boolean',
        ];
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isCashier(): bool
    {
        return $this->role === self::ROLE_CASHIER;
    }

    public function isKitchen(): bool
    {
        return $this->role === self::ROLE_KITCHEN;
    }

    public function isDemo(): bool
    {
        return (bool) $this->is_demo
            || str_ends_with((string) $this->email, '@'.self::DEMO_EMAIL_DOMAIN);
    }

    public function roleLabel(): string
    {
        return __('menuqr.roles.'.$this->role);
    }

    public static function demoEmail(string $role): string
    {
        return $role.'@'.self::DEMO_EMAIL_DOMAIN;
    }

    public static function demoUser(string $role): ?self
    {
        if (! in_array($role, self::ROLES, true)) {
            return null;
        }

        return static::query()
            ->where('email', self::demoEmail($role))
            ->where('is_active', true)
            ->first();
    }
}
