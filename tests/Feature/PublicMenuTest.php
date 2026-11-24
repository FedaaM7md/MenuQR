<?php

/*
 * MenuQR - PublicMenuTest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MenuFixtures;
use Tests\TestCase;

class PublicMenuTest extends TestCase
{
    use MenuFixtures, RefreshDatabase;

    public function test_valid_table_token_shows_the_public_menu(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable(['name' => 'T7']);
        $item = $this->makeItem(['name_ar' => 'حمص', 'name_en' => 'Hummus', 'price_minor' => 8500]);

        $response = $this->get(route('menu.show', $table->token));

        $response->assertOk()
            ->assertSee('مطعم الاختبار')
            ->assertSee('حمص')
            ->assertSee('T7')
            ->assertSee('dir="rtl"', false);
    }

    public function test_invalid_table_token_returns_404(): void
    {
        $this->configureRestaurant();

        $this->get('/m/'.str_repeat('a', 48))->assertNotFound();
        $this->get('/m/short')->assertNotFound();
    }

    public function test_inactive_table_cannot_be_opened(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable(['is_active' => false]);

        $this->get(route('menu.show', $table->token))->assertNotFound();
    }

    public function test_search_filters_items(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $this->makeItem(['name_ar' => 'حمص', 'name_en' => 'Hummus']);
        $this->makeItem(['name_ar' => 'شاي', 'name_en' => 'Tea']);

        $response = $this->get(route('menu.show', ['table' => $table->token, 'q' => 'Hummus']));

        $response->assertOk()->assertSee('Hummus')->assertDontSee('Tea');
    }

    public function test_item_names_are_escaped_on_the_public_menu(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $this->makeItem(['name_ar' => '<script>alert(1)</script>', 'name_en' => '<script>alert(1)</script>']);

        $response = $this->get(route('menu.show', $table->token));

        $response->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_cart_totals_are_computed_in_minor_units_with_options(): void
    {
        $this->configureRestaurant(['tax_percent' => 1400, 'service_percent' => 1000]);
        $table = $this->makeTable();
        $item = $this->makeItem(['price_minor' => 10000]);
        $group = $this->addOptionGroup($item, ['min_select' => 1, 'max_select' => 1], [
            ['عادي', 'Regular', 0],
            ['كبير', 'Large', 2500],
        ]);
        $large = $group->options->firstWhere('name_en', 'Large');

        $this->addToCart($table, $item, 2, [$large->id])->assertRedirect();

        $preview = app(CartService::class)->preview($table);

        $this->assertSame(25000, $preview['subtotal_minor']);
        $this->assertSame(3500, $preview['tax_minor']);
        $this->assertSame(2500, $preview['service_minor']);
        $this->assertSame(31000, $preview['total_minor']);
        $this->assertCount(1, $preview['lines']);

        $this->get(route('menu.cart', $table->token))
            ->assertOk()
            ->assertSee('310.00')
            ->assertSee('250.00');
    }

    public function test_client_supplied_prices_are_ignored(): void
    {
        $this->configureRestaurant(['tax_percent' => 0, 'service_percent' => 0]);
        $table = $this->makeTable();
        $item = $this->makeItem(['price_minor' => 12345]);

        $this->post(route('menu.cart.add', $table->token), [
            'item_id' => $item->id,
            'quantity' => 1,
            'price_minor' => 1,
            'line_total_minor' => 1,
            'unit_price' => '0.01',
        ])->assertRedirect();

        $this->assertSame(12345, app(CartService::class)->preview($table)['subtotal_minor']);
    }

    public function test_required_option_group_must_be_selected(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $item = $this->makeItem();
        $this->addOptionGroup($item, ['name_ar' => 'الحجم', 'name_en' => 'Size', 'min_select' => 1, 'max_select' => 1], [
            ['صغير', 'Small', 0],
        ]);

        $this->addToCart($table, $item)->assertSessionHasErrors('cart');

        $this->assertSame(0, app(CartService::class)->count($table));
    }

    public function test_max_option_selection_is_enforced(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $item = $this->makeItem();
        $group = $this->addOptionGroup($item, ['min_select' => 0, 'max_select' => 1], [
            ['أ', 'A', 0],
            ['ب', 'B', 0],
        ]);

        $this->addToCart($table, $item, 1, $group->options->pluck('id')->all())
            ->assertSessionHasErrors('cart');
    }

    public function test_options_belonging_to_another_item_are_rejected(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $first = $this->makeItem();
        $second = $this->makeItem();
        $foreign = $this->addOptionGroup($second, ['min_select' => 0, 'max_select' => 1], [
            ['خيار', 'Option', 500],
        ]);

        $this->addToCart($table, $first, 1, [$foreign->options->first()->id])
            ->assertSessionHasErrors('cart');
    }

    public function test_unavailable_item_cannot_be_added(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $item = $this->makeItem(['is_available' => false]);

        $this->addToCart($table, $item)->assertSessionHasErrors('cart');
        $this->assertSame(0, app(CartService::class)->count($table));
    }

    public function test_cart_quantity_can_be_updated_and_line_removed(): void
    {
        $this->configureRestaurant(['tax_percent' => 0, 'service_percent' => 0]);
        $table = $this->makeTable();
        $item = $this->makeItem(['price_minor' => 5000]);

        $this->addToCart($table, $item, 1);
        $lines = session('cart.'.$table->id);
        $key = array_key_first($lines);

        $this->patch(route('menu.cart.update', [$table->token, $key]), ['quantity' => 3])->assertRedirect();
        $this->assertSame(15000, app(CartService::class)->preview($table)['subtotal_minor']);

        $this->delete(route('menu.cart.remove', [$table->token, $key]))->assertRedirect();
        $this->assertSame(0, app(CartService::class)->count($table));
    }

    public function test_menu_is_read_only_when_the_restaurant_stops_orders(): void
    {
        $this->configureRestaurant(['is_accepting_orders' => false]);
        $table = $this->makeTable();
        $item = $this->makeItem();

        $this->get(route('menu.show', $table->token))
            ->assertOk()
            ->assertSee('لا يستقبل طلبات جديدة');

        $this->post(route('menu.cart.add', $table->token), [
            'item_id' => $item->id,
            'quantity' => 1,
        ])->assertRedirect();
    }
}
