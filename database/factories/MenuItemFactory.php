<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name_ar' => 'صنف '.fake()->word(),
            'name_en' => fake()->words(2, true),
            'description_ar' => fake()->sentence(6),
            'description_en' => fake()->sentence(6),
            'price_minor' => fake()->numberBetween(1500, 25000),
            'is_available' => true,
            'is_featured' => false,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn () => ['is_available' => false]);
    }
}
