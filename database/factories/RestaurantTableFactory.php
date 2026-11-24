<?php

namespace Database\Factories;

use App\Models\RestaurantTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantTable>
 */
class RestaurantTableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'T'.fake()->unique()->numberBetween(1, 999),
            'seats' => fake()->numberBetween(2, 8),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
