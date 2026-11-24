<?php

/*
 * MenuQR - MenuFixtures.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Support;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\MenuOption;
use App\Models\OptionGroup;
use App\Models\RestaurantTable;
use App\Models\Setting;
use Illuminate\Testing\TestResponse;

trait MenuFixtures
{
    protected function configureRestaurant(array $overrides = []): Setting
    {
        $settings = Setting::current();
        $settings->update(array_merge([
            'restaurant_name_ar' => 'مطعم الاختبار',
            'restaurant_name_en' => 'Test Restaurant',
            'tagline_ar' => 'وصف تجريبي',
            'tagline_en' => 'Test tagline',
            'currency' => 'EGP',
            'tax_percent' => 1400,
            'service_percent' => 1000,
            'whatsapp_number' => null,
            'hours' => Setting::defaultHours(),
            'is_accepting_orders' => true,
        ], $overrides));

        return $settings->refresh();
    }

    protected function makeTable(array $attributes = []): RestaurantTable
    {
        return RestaurantTable::factory()->create($attributes);
    }

    protected function makeCategory(array $attributes = []): Category
    {
        return Category::factory()->create($attributes);
    }

    protected function makeItem(array $attributes = []): MenuItem
    {
        return MenuItem::factory()->create($attributes);
    }

    /**
     * @param  array<int, array{0: string, 1: string, 2: int}>  $options  [name_ar, name_en, delta]
     */
    protected function addOptionGroup(MenuItem $item, array $groupAttributes, array $options = []): OptionGroup
    {
        $group = $item->optionGroups()->create(array_merge([
            'name_ar' => 'مجموعة',
            'name_en' => 'Group',
            'min_select' => 0,
            'max_select' => 1,
            'sort_order' => 0,
        ], $groupAttributes));

        foreach ($options as $index => [$ar, $en, $delta]) {
            MenuOption::create([
                'option_group_id' => $group->id,
                'name_ar' => $ar,
                'name_en' => $en,
                'price_delta_minor' => $delta,
                'is_available' => true,
                'sort_order' => $index,
            ]);
        }

        return $group->load('options');
    }

    protected function addToCart(RestaurantTable $table, MenuItem $item, int $quantity = 1, array $optionIds = [], ?string $notes = null): TestResponse
    {
        return $this->post(route('menu.cart.add', $table->token), [
            'item_id' => $item->id,
            'quantity' => $quantity,
            'options' => $optionIds,
            'notes' => $notes,
        ]);
    }

    protected function placeOrder(RestaurantTable $table, array $payload = []): TestResponse
    {
        return $this->post(route('menu.orders.store', $table->token), array_merge([
            'type' => 'dine_in',
        ], $payload));
    }
}
