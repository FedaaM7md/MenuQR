<?php

/*
 * MenuQR - OrderFlowTest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MenuFixtures;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use MenuFixtures, RefreshDatabase;

    private function placeOrderFor(RestaurantTable $table): Order
    {
        $item = $this->makeItem(['price_minor' => 8000]);
        $this->addToCart($table, $item);
        $this->placeOrder($table)->assertRedirect();

        return Order::orderByDesc('id')->firstOrFail();
    }

    public function test_cashier_can_move_an_order_through_the_full_workflow(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $order = $this->placeOrderFor($table);
        $cashier = User::factory()->cashier()->create();

        $this->actingAs($cashier)
            ->patch(route('staff.orders.status', $order), ['status' => 'preparing'])
            ->assertSessionHasNoErrors();
        $this->assertSame('preparing', $order->refresh()->status);
        $this->assertNotNull($order->preparing_at);

        $this->actingAs($cashier)
            ->patch(route('staff.orders.status', $order), ['status' => 'ready'])
            ->assertSessionHasNoErrors();
        $this->assertSame('ready', $order->refresh()->status);
        $this->assertNotNull($order->ready_at);

        $this->actingAs($cashier)
            ->patch(route('staff.orders.status', $order), ['status' => 'served'])
            ->assertSessionHasNoErrors();
        $this->assertSame('served', $order->refresh()->status);
        $this->assertNotNull($order->served_at);
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $this->configureRestaurant();
        $order = $this->placeOrderFor($this->makeTable());
        $cashier = User::factory()->cashier()->create();

        $this->actingAs($cashier)
            ->patch(route('staff.orders.status', $order), ['status' => 'served'])
            ->assertSessionHasErrors('status');

        $this->assertSame('new', $order->refresh()->status);
    }

    public function test_unknown_status_is_rejected(): void
    {
        $this->configureRestaurant();
        $order = $this->placeOrderFor($this->makeTable());
        $cashier = User::factory()->cashier()->create();

        $this->actingAs($cashier)
            ->patch(route('staff.orders.status', $order), ['status' => 'paid'])
            ->assertSessionHasErrors('status');

        $this->assertSame('new', $order->refresh()->status);
    }

    public function test_kitchen_can_start_and_finish_preparing_but_cannot_serve_or_cancel(): void
    {
        $this->configureRestaurant();
        $order = $this->placeOrderFor($this->makeTable());
        $kitchen = User::factory()->kitchen()->create();

        $this->actingAs($kitchen)
            ->patch(route('staff.orders.status', $order), ['status' => 'preparing'])
            ->assertSessionHasNoErrors();
        $this->assertSame('preparing', $order->refresh()->status);

        $this->actingAs($kitchen)
            ->patch(route('staff.orders.status', $order), ['status' => 'ready'])
            ->assertSessionHasNoErrors();
        $this->assertSame('ready', $order->refresh()->status);

        $this->actingAs($kitchen)
            ->patch(route('staff.orders.status', $order), ['status' => 'served'])
            ->assertSessionHasErrors('status');
        $this->assertSame('ready', $order->refresh()->status);

        $this->actingAs($kitchen)
            ->patch(route('staff.orders.status', $order), ['status' => 'cancelled'])
            ->assertSessionHasErrors('status');
        $this->assertSame('ready', $order->refresh()->status);
    }

    public function test_cashier_can_cancel_a_new_order(): void
    {
        $this->configureRestaurant();
        $order = $this->placeOrderFor($this->makeTable());

        $this->actingAs(User::factory()->cashier()->create())
            ->patch(route('staff.orders.status', $order), ['status' => 'cancelled'])
            ->assertSessionHasNoErrors();

        $this->assertSame('cancelled', $order->refresh()->status);
        $this->assertNotNull($order->cancelled_at);
    }

    public function test_served_orders_are_final(): void
    {
        $this->configureRestaurant();
        $order = $this->placeOrderFor($this->makeTable());
        $order->update(['status' => Order::STATUS_SERVED]);

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('staff.orders.status', $order), ['status' => 'cancelled'])
            ->assertSessionHasErrors('status');

        $this->assertSame('served', $order->refresh()->status);
    }

    public function test_guest_cannot_change_order_status(): void
    {
        $this->configureRestaurant();
        $order = $this->placeOrderFor($this->makeTable());

        $this->patch(route('staff.orders.status', $order), ['status' => 'preparing'])
            ->assertRedirect(route('login'));

        $this->assertSame('new', $order->refresh()->status);
    }

    public function test_cashier_and_admin_can_view_the_order_but_kitchen_cannot_use_the_staff_order_screen(): void
    {
        $this->configureRestaurant();
        $order = $this->placeOrderFor($this->makeTable());

        $this->actingAs(User::factory()->cashier()->create())
            ->get(route('staff.orders.show', $order))->assertOk();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('staff.orders.show', $order))->assertOk();

        $this->actingAs(User::factory()->kitchen()->create())
            ->get(route('staff.orders.show', $order))->assertForbidden();
    }
}
