<?php

/*
 * MenuQR - Order.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_READY = 'ready';

    public const STATUS_SERVED = 'served';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_PREPARING,
        self::STATUS_READY,
        self::STATUS_SERVED,
        self::STATUS_CANCELLED,
    ];

    public const TYPE_DINE_IN = 'dine_in';

    public const TYPE_TAKEAWAY = 'takeaway';

    public const TYPES = [self::TYPE_DINE_IN, self::TYPE_TAKEAWAY];

    /** Allowed workflow transitions, independent of role. */
    public const TRANSITIONS = [
        self::STATUS_NEW => [self::STATUS_PREPARING, self::STATUS_CANCELLED],
        self::STATUS_PREPARING => [self::STATUS_READY, self::STATUS_CANCELLED],
        self::STATUS_READY => [self::STATUS_SERVED, self::STATUS_CANCELLED],
        self::STATUS_SERVED => [],
        self::STATUS_CANCELLED => [],
    ];

    protected $fillable = [
        'number',
        'restaurant_table_id',
        'type',
        'status',
        'customer_name',
        'customer_phone',
        'notes',
        'subtotal_minor',
        'tax_percent',
        'tax_minor',
        'service_percent',
        'service_minor',
        'total_minor',
        'created_by',
        'placed_at',
        'preparing_at',
        'ready_at',
        'served_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_minor' => 'integer',
            'tax_percent' => 'integer',
            'tax_minor' => 'integer',
            'service_percent' => 'integer',
            'service_minor' => 'integer',
            'total_minor' => 'integer',
            'placed_at' => 'datetime',
            'preparing_at' => 'datetime',
            'ready_at' => 'datetime',
            'served_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    public function isFinal(): bool
    {
        return in_array($this->status, [self::STATUS_SERVED, self::STATUS_CANCELLED], true);
    }

    public function statusLabel(): string
    {
        return __('menuqr.statuses.'.$this->status);
    }

    public function typeLabel(): string
    {
        return __('menuqr.order_types.'.$this->type);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_NEW, self::STATUS_PREPARING, self::STATUS_READY]);
    }

    public function scopeNotCancelled($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    public function scopePaidLike($query)
    {
        return $query->whereIn('status', [self::STATUS_PREPARING, self::STATUS_READY, self::STATUS_SERVED]);
    }

    public static function formatNumber(int $year, int $sequence): string
    {
        return sprintf('ORD-%d-%04d', $year, $sequence);
    }
}
