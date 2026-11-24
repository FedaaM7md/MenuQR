<?php

/*
 * MenuQR - MenuManagementTest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\MenuOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MenuFixtures;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use MenuFixtures, RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_create_a_category(): void
    {
        $this->actingAs($this->admin())->post(route('admin.categories.store'), [
            'name_ar' => 'مشروبات',
            'name_en' => 'Drinks',
            'sort_order' => 3,
            'is_active' => '1',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name_ar' => 'مشروبات',
            'name_en' => 'Drinks',
            'is_active' => true,
        ]);
    }

    public function test_category_validation_requires_both_names(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), ['name_ar' => '', 'name_en' => ''])
            ->assertSessionHasErrors(['name_ar', 'name_en']);

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_item_price_is_converted_to_piasters(): void
    {
        $category = $this->makeCategory();

        $this->actingAs($this->admin())->post(route('admin.items.store'), [
            'category_id' => $category->id,
            'name_ar' => 'كفتة',
            'name_en' => 'Kofta',
            'price' => '45.75',
            'is_available' => '1',
            'sort_order' => 1,
        ])->assertRedirect();

        $item = MenuItem::firstOrFail();
        $this->assertSame(4575, $item->price_minor);
        $this->assertSame('كفتة', $item->name_ar);
    }

    public function test_item_image_upload_is_validated_and_stored(): void
    {
        Storage::fake('public');
        $category = $this->makeCategory();

        $this->actingAs($this->admin())->post(route('admin.items.store'), [
            'category_id' => $category->id,
            'name_ar' => 'صنف',
            'name_en' => 'Item',
            'price' => '10.00',
            'image' => UploadedFile::fake()->image('dish.png', 300, 200),
        ])->assertRedirect();

        $item = MenuItem::firstOrFail();
        $this->assertNotNull($item->image_path);
        Storage::disk('public')->assertExists($item->image_path);
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');
        $category = $this->makeCategory();

        $this->actingAs($this->admin())->post(route('admin.items.store'), [
            'category_id' => $category->id,
            'name_ar' => 'صنف',
            'name_en' => 'Item',
            'price' => '10.00',
            'image' => UploadedFile::fake()->create('evil.svg', 5, 'image/svg+xml'),
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseCount('menu_items', 0);
    }

    public function test_cashier_cannot_manage_the_menu(): void
    {
        $cashier = User::factory()->cashier()->create();
        $category = $this->makeCategory();

        $this->actingAs($cashier)->get(route('admin.items.index'))->assertForbidden();
        $this->actingAs($cashier)->post(route('admin.items.store'), [
            'category_id' => $category->id,
            'name_ar' => 'x',
            'name_en' => 'x',
            'price' => '1.00',
        ])->assertForbidden();
    }

    public function test_category_with_items_cannot_be_deleted(): void
    {
        $category = $this->makeCategory();
        $this->makeItem(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_option_groups_and_negative_price_deltas_are_supported(): void
    {
        $admin = $this->admin();
        $item = $this->makeItem();

        $this->actingAs($admin)->post(route('admin.option-groups.store', $item), [
            'name_ar' => 'الحجم',
            'name_en' => 'Size',
            'min_select' => 1,
            'max_select' => 1,
        ])->assertRedirect();

        $group = $item->optionGroups()->firstOrFail();

        $this->actingAs($admin)->post(route('admin.options.store', [$item, $group]), [
            'name_ar' => 'صغير',
            'name_en' => 'Small',
            'price_delta' => '-5.00',
            'is_available' => '1',
        ])->assertRedirect();

        $option = MenuOption::firstOrFail();
        $this->assertSame(-500, $option->price_delta_minor);
    }

    public function test_option_group_min_cannot_exceed_max(): void
    {
        $item = $this->makeItem();

        $this->actingAs($this->admin())->post(route('admin.option-groups.store', $item), [
            'name_ar' => 'مجموعة',
            'name_en' => 'Group',
            'min_select' => 3,
            'max_select' => 1,
        ])->assertSessionHasErrors('max_select');
    }

    public function test_availability_toggle_flips_the_item(): void
    {
        $item = $this->makeItem(['is_available' => true]);

        $this->actingAs($this->admin())
            ->patch(route('admin.items.toggle', $item))
            ->assertRedirect();

        $this->assertFalse($item->refresh()->is_available);
    }

    public function test_options_of_another_item_cannot_be_edited_through_the_wrong_item(): void
    {
        $first = $this->makeItem();
        $second = $this->makeItem();
        $group = $this->addOptionGroup($second, ['name_ar' => 'حجم', 'name_en' => 'Size'], [['صغير', 'Small', 0]]);

        $this->actingAs($this->admin())
            ->put(route('admin.option-groups.update', [$first, $group]), [
                'name_ar' => 'hacked',
                'name_en' => 'hacked',
                'min_select' => 0,
                'max_select' => 1,
            ])
            ->assertNotFound();
    }
}
