<?php

namespace Database\Factories;

use App\Models\MenuOption;
use App\Models\OptionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuOption>
 */
class MenuOptionFactory extends Factory
{
    protected $model = MenuOption::class;

    public function definition(): array
    {
        return [
            'option_group_id' => OptionGroup::factory(),
            'name_ar' => 'خيار',
            'name_en' => 'Option',
            'price_delta_minor' => 0,
            'is_available' => true,
            'sort_order' => 0,
        ];
    }
}
