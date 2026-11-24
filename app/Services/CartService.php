<?php

/*
 * MenuQR - CartService.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Services;

use App\Models\MenuItem;
use App\Models\RestaurantTable;
use App\Support\Money;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Session-backed cart for the public table menu. Prices are always recalculated
 * from the database when the cart is displayed or submitted.
 */
class CartService
{
    /** @var array<int, array{item_id: int, quantity: int, options: array<int, int>, notes: ?string}>|null */
    private ?array $working = null;

    public function __construct(private readonly PricingService $pricing) {}

    public function sessionKey(RestaurantTable $table): string
    {
        return 'cart.'.$table->id;
    }

    /** @return array<string, array{item_id: int, quantity: int, options: array<int, int>, notes: ?string}> */
    public function lines(RestaurantTable $table): array
    {
        $lines = session()->get($this->sessionKey($table), []);

        return is_array($lines) ? $lines : [];
    }

    public function add(RestaurantTable $table, MenuItem $item, int $quantity, array $optionIds, ?string $notes): string
    {
        $normalizedOptions = collect($optionIds)->map(fn ($id) => (int) $id)->filter()->unique()->sort()->values()->all();
        $notes = $notes !== null ? trim($notes) : null;
        $notes = $notes !== '' ? Str::limit($notes, 200, '') : null;

        // Validate and price immediately so bad selections are rejected at add time.
        $this->pricing->priceLine($item, $quantity, $normalizedOptions);

        $lines = $this->lines($table);
        $key = Str::lower(Str::random(12));
        $lines[$key] = [
            'item_id' => (int) $item->id,
            'quantity' => $quantity,
            'options' => $normalizedOptions,
            'notes' => $notes,
        ];

        session()->put($this->sessionKey($table), $lines);

        return $key;
    }

    public function updateQuantity(RestaurantTable $table, string $key, int $quantity): void
    {
        $lines = $this->lines($table);

        if (! isset($lines[$key])) {
            return;
        }

        if ($quantity < 1) {
            unset($lines[$key]);
        } else {
            $lines[$key]['quantity'] = min(99, $quantity);
        }

        session()->put($this->sessionKey($table), $lines);
    }

    public function remove(RestaurantTable $table, string $key): void
    {
        $lines = $this->lines($table);
        unset($lines[$key]);
        session()->put($this->sessionKey($table), $lines);
    }

    public function clear(RestaurantTable $table): void
    {
        session()->forget($this->sessionKey($table));
    }

    public function count(RestaurantTable $table): int
    {
        return array_sum(array_map(fn ($line) => (int) $line['quantity'], $this->lines($table)));
    }

    /**
     * Re-price the whole cart for display.
     *
     * @return array{
     *     lines: array<int, array{key: string, pricing: array, notes: ?string}>,
     *     removed: int,
     *     subtotal_minor: int,
     *     tax_percent: int,
     *     tax_minor: int,
     *     service_percent: int,
     *     service_minor: int,
     *     total_minor: int
     * }
     */
    public function preview(RestaurantTable $table): array
    {
        $config = setting();
        $result = [];
        $removed = 0;
        $subtotal = 0;
        $lines = $this->lines($table);

        $items = MenuItem::query()
            ->with('optionGroups.options')
            ->whereIn('id', array_values(array_unique(array_map(fn ($l) => (int) $l['item_id'], $lines))))
            ->get()
            ->keyBy('id');

        $validKeys = [];

        foreach ($lines as $key => $line) {
            $item = $items->get((int) $line['item_id']);

            if (! $item || ! $item->is_available) {
                $removed++;

                continue;
            }

            try {
                $priced = $this->pricing->priceLine($item, (int) $line['quantity'], $line['options'] ?? []);
            } catch (ValidationException) {
                $removed++;

                continue;
            }

            $subtotal += $priced['line_total_minor'];
            $result[] = [
                'key' => (string) $key,
                'pricing' => $priced,
                'notes' => $line['notes'] ?? null,
            ];
            $validKeys[] = $key;
        }

        if ($removed > 0) {
            foreach (array_keys($lines) as $key) {
                if (! in_array($key, $validKeys, true)) {
                    unset($lines[$key]);
                }
            }
            session()->put($this->sessionKey($table), $lines);
        }

        $tax = Money::percentOf($subtotal, $config->tax_percent);
        $service = Money::percentOf($subtotal, $config->service_percent);

        return [
            'lines' => $result,
            'removed' => $removed,
            'subtotal_minor' => $subtotal,
            'tax_percent' => $config->tax_percent,
            'tax_minor' => $tax,
            'service_percent' => $config->service_percent,
            'service_minor' => $service,
            'total_minor' => $subtotal + $tax + $service,
        ];
    }
}
