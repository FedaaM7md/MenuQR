<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\OptionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OptionGroup>
 */
class OptionGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'menu_item_id' => MenuItem::factory(),
            'name_ar' => 'مجموعة',
            'name_en' => 'Group',
            'min_select' => 0,
            'max_select' => 1,
            'sort_order' => 0,
        ];
    }
}
