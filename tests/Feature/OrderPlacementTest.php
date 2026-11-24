<?php

/*
 * MenuQR - OrderPlacementTest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use App\Models\Order;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\MenuFixtures;
use Tests\TestCase;

class OrderPlacementTest extends TestCase
{
    use MenuFixtures, RefreshDatabase;

    public function test_public_order_totals_options_and_snapshots_are_correct(): void
    {
        $this->travelTo(Carbon::parse('2026-05-01 12:30:00'));
        $this->configureRestaurant(['tax_percent' => 1400, 'service_percent' => 1000]);
        $table = $this->makeTable(['name' => 'T3']);
        $item = $this->makeItem(['name_ar' => 'كفتة', 'name_en' => 'Kofta', 'price_minor' => 10000]);
        $group = $this->addOptionGroup($item, ['min_select' => 1, 'max_select' => 1], [
            ['عادي', 'Regular', 0],
            ['كبير', 'Large', 2500],
        ]);
        $large = $group->options->firstWhere('name_en', 'Large');

        $this->addToCart($table, $item, 2, [$large->id], 'بدون بصل');
        $response = $this->placeOrder($table, ['type' => 'dine_in', 'customer_name' => 'منى']);

        $order = Order::firstOrFail();
        $response->assertRedirect(route('menu.orders.show', [$table->token, $order->number]));

        $this->assertSame('ORD-2026-0001', $order->number);
        $this->assertSame(Order::STATUS_NEW, $order->status);
        $this->assertSame($table->id, $order->restaurant_table_id);
        $this->assertSame(25000, $order->subtotal_minor);
        $this->assertSame(1400, $order->tax_percent);
        $this->assertSame(3500, $order->tax_minor);
        $this->assertSame(1000, $order->service_percent);
        $this->assertSame(2500, $order->service_minor);
        $this->assertSame(31000, $order->total_minor);
        $this->assertNull($order->created_by);

        $line = $order->items()->firstOrFail();
        $this->assertSame(2, $line->quantity);
        $this->assertSame(10000, $line->unit_price_minor);
        $this->assertSame(25000, $line->line_total_minor);
        $this->assertSame('بدون بصل', $line->notes);
        $this->assertSame(2500, $line->options()->firstOrFail()->price_delta_minor);

        $this->assertSame(0, app(CartService::class)->count($table));
    }

    public function test_order_numbers_increment_per_year(): void
    {
        $this->travelTo(Carbon::parse('2026-02-01 10:00:00'));
        $this->configureRestaurant();
        $table = $this->makeTable();
        $item = $this->makeItem(['price_minor' => 5000]);

        $this->addToCart($table, $item);
        $this->placeOrder($table)->assertRedirect();
        $this->addToCart($table, $item);
        $this->placeOrder($table)->assertRedirect();

        $this->assertSame(
            ['ORD-2026-0001', 'ORD-2026-0002'],
            Order::orderBy('id')->pluck('number')->all(),
        );

        $this->travelTo(Carbon::parse('2027-01-01 09:00:00'));
        $this->addToCart($table, $item);
        $this->placeOrder($table)->assertRedirect();

        $this->assertSame('ORD-2027-0001', Order::orderByDesc('id')->first()->number);
    }

    public function test_table_is_taken_from_the_url_token_not_the_payload(): void
    {
        $this->configureRestaurant();
        $real = $this->makeTable(['name' => 'Real']);
        $other = $this->makeTable(['name' => 'Other']);
        $item = $this->makeItem();

        $this->addToCart($real, $item);
        $this->placeOrder($real, [
            'type' => 'takeaway',
            'restaurant_table_id' => $other->id,
            'table_id' => $other->id,
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame($real->id, $order->restaurant_table_id);
        $this->assertSame('takeaway', $order->type);
    }

    public function test_empty_cart_cannot_be_ordered(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();

        $this->placeOrder($table)->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_orders_are_blocked_when_restaurant_is_not_accepting(): void
    {
        $this->configureRestaurant(['is_accepting_orders' => false]);
        $table = $this->makeTable();
        $item = $this->makeItem();

        $this->addToCart($table, $item);
        $this->placeOrder($table)->assertSessionHasErrors('cart');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_validation_rejects_invalid_type_phone_and_notes(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $item = $this->makeItem();
        $this->addToCart($table, $item);

        $this->placeOrder($table, [
            'type' => 'delivery',
            'customer_phone' => 'abc',
            'notes' => str_repeat('x', 501),
        ])->assertSessionHasErrors(['type', 'customer_phone', 'notes']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_confirmation_page_requires_the_placing_session(): void
    {
        $this->travelTo(Carbon::parse('2026-05-02 12:00:00'));
        $this->configureRestaurant();
        $table = $this->makeTable();
        $item = $this->makeItem();

        $this->addToCart($table, $item);
        $this->placeOrder($table);
        $order = Order::firstOrFail();

        $this->get(route('menu.orders.show', [$table->token, $order->number]))->assertOk();

        $this->flushSession();
        $this->get(route('menu.orders.show', [$table->token, $order->number]))->assertNotFound();
    }

    public function test_confirmation_cannot_be_viewed_through_another_table_token(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $other = $this->makeTable();
        $item = $this->makeItem();

        $this->addToCart($table, $item);
        $this->placeOrder($table);
        $order = Order::firstOrFail();

        $this->get(route('menu.orders.show', [$other->token, $order->number]))->assertNotFound();
    }

    public function test_whatsapp_link_is_rendered_when_configured(): void
    {
        $this->travelTo(Carbon::parse('2026-05-03 12:00:00'));
        $this->configureRestaurant(['whatsapp_number' => '+201000000000']);
        $table = $this->makeTable();
        $item = $this->makeItem(['name_ar' => 'حمص']);

        $this->addToCart($table, $item);
        $this->placeOrder($table);
        $order = Order::firstOrFail();

        $this->get(route('menu.orders.show', [$table->token, $order->number]))
            ->assertOk()
            ->assertSee('https://wa.me/201000000000?text=', false)
            ->assertSee('ORD-2026-0001');
    }

    public function test_no_whatsapp_link_without_a_configured_number(): void
    {
        $this->configureRestaurant(['whatsapp_number' => null]);
        $table = $this->makeTable();
        $item = $this->makeItem();

        $this->addToCart($table, $item);
        $this->placeOrder($table);
        $order = Order::firstOrFail();

        $this->get(route('menu.orders.show', [$table->token, $order->number]))
            ->assertOk()
            ->assertDontSee('wa.me', false);
    }

    public function test_order_numbers_are_unique_and_formatted(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $item = $this->makeItem();

        for ($i = 0; $i < 3; $i++) {
            $this->addToCart($table, $item);
            $this->placeOrder($table);
        }

        $numbers = Order::pluck('number')->all();
        $this->assertCount(3, array_unique($numbers));

        foreach ($numbers as $number) {
            $this->assertMatchesRegularExpression('/^ORD-\d{4}-\d{4,}$/', $number);
        }
    }
}
