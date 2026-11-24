<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(2000, 30000);
        $tax = (int) round($subtotal * 1400 / 10000);
        $service = 0;

        return [
            'number' => 'ORD-'.now()->format('Y').'-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'restaurant_table_id' => RestaurantTable::factory(),
            'type' => Order::TYPE_DINE_IN,
            'status' => Order::STATUS_NEW,
            'subtotal_minor' => $subtotal,
            'tax_percent' => 1400,
            'tax_minor' => $tax,
            'service_percent' => 0,
            'service_minor' => $service,
            'total_minor' => $subtotal + $tax + $service,
            'created_by' => null,
            'placed_at' => now(),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => ['created_by' => $user->id]);
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
