<?php

/*
 * MenuQR - PricingService.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Services;

use App\Models\MenuItem;
use App\Models\MenuOption;
use App\Models\OptionGroup;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Server-side pricing of menu items and options, always in integer minor units.
 * Client-submitted prices are never trusted or read.
 */
class PricingService
{
    /**
     * @param  array<int, int|string>  $optionIds
     * @return array{
     *     item: MenuItem,
     *     quantity: int,
     *     unit_base_minor: int,
     *     options_delta_minor: int,
     *     unit_total_minor: int,
     *     line_total_minor: int,
     *     options: array<int, array{group: OptionGroup, option: MenuOption}>
     * }
     */
    public function priceLine(MenuItem $item, int $quantity, array $optionIds): array
    {
        if ($quantity < 1 || $quantity > 99) {
            throw ValidationException::withMessages([
                'cart' => __('menuqr.errors.quantity_range'),
            ]);
        }

        $item->loadMissing('optionGroups.options');

        $requested = collect($optionIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        /** @var Collection<int, MenuOption> $selected */
        $selected = MenuOption::query()
            ->with('group')
            ->whereIn('id', $requested->all())
            ->where('is_available', true)
            ->get();

        if ($selected->count() !== $requested->count()) {
            throw ValidationException::withMessages([
                'cart' => __('menuqr.errors.option_unavailable'),
            ]);
        }

        foreach ($selected as $option) {
            if ($option->group === null || $option->group->menu_item_id !== $item->id) {
                throw ValidationException::withMessages([
                    'cart' => __('menuqr.errors.option_foreign'),
                ]);
            }
        }

        $grouped = $selected->groupBy('option_group_id');
        $resolved = [];
        $delta = 0;

        foreach ($item->optionGroups as $group) {
            /** @var Collection<int, MenuOption> $chosen */
            $chosen = $grouped->get($group->id, collect());

            if ($chosen->count() < $group->min_select) {
                throw ValidationException::withMessages([
                    'cart' => __('menuqr.errors.option_min', [
                        'group' => $group->name(),
                        'min' => $group->min_select,
                    ]),
                ]);
            }

            if ($chosen->count() > $group->max_select) {
                throw ValidationException::withMessages([
                    'cart' => __('menuqr.errors.option_max', [
                        'group' => $group->name(),
                        'max' => $group->max_select,
                    ]),
                ]);
            }

            foreach ($chosen as $option) {
                $delta += $option->price_delta_minor;
                $resolved[] = ['group' => $group, 'option' => $option];
            }
        }

        $unitTotal = (int) $item->price_minor + $delta;

        if ($unitTotal < 0) {
            throw ValidationException::withMessages([
                'cart' => __('menuqr.errors.invalid_price'),
            ]);
        }

        return [
            'item' => $item,
            'quantity' => $quantity,
            'unit_base_minor' => (int) $item->price_minor,
            'options_delta_minor' => $delta,
            'unit_total_minor' => $unitTotal,
            'line_total_minor' => $unitTotal * $quantity,
            'options' => $resolved,
        ];
    }
}
