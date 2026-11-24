<?php

/*
 * MenuQR - SettingsTest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MenuFixtures;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use MenuFixtures, RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_is_redirected_from_settings(): void
    {
        $this->get(route('admin.settings.edit'))->assertRedirect(route('login'));
    }

    public function test_cashier_and_kitchen_cannot_manage_settings(): void
    {
        $this->actingAs(User::factory()->cashier()->create())
            ->get(route('admin.settings.edit'))
            ->assertForbidden();

        $this->actingAs(User::factory()->kitchen()->create())
            ->get(route('admin.settings.edit'))
            ->assertForbidden();
    }

    public function test_admin_can_update_settings_and_percentages_are_stored_as_basis_points(): void
    {
        $admin = $this->admin();
        $settings = Setting::current();

        $response = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'restaurant_name_ar' => 'مطعم جديد',
            'restaurant_name_en' => 'New Restaurant',
            'tax_percent' => '14.5',
            'service_percent' => '12',
            'whatsapp_number' => '+201000000000',
            'is_accepting_orders' => '1',
            'hours' => [
                'sat' => ['open' => '09:00', 'close' => '23:00'],
                'sun' => ['open' => '09:00', 'close' => '23:00', 'closed' => '1'],
            ],
        ]);

        $response->assertRedirect(route('admin.settings.edit'));
        $settings->refresh();

        $this->assertSame(1450, $settings->tax_percent);
        $this->assertSame(1200, $settings->service_percent);
        $this->assertSame('مطعم جديد', $settings->restaurant_name_ar);
        $this->assertTrue($settings->is_accepting_orders);
        $this->assertTrue($settings->hours['sun']['closed']);
        $this->assertSame('10:00', $settings->hours['mon']['open']);
    }

    public function test_unchecking_accepting_orders_disables_ordering(): void
    {
        $settings = Setting::current();
        $settings->update(['is_accepting_orders' => true]);

        $this->actingAs($this->admin())->put(route('admin.settings.update'), [
            'restaurant_name_ar' => 'مطعم',
            'restaurant_name_en' => 'Restaurant',
            'tax_percent' => '0',
            'service_percent' => '0',
        ])->assertRedirect();

        $this->assertFalse($settings->refresh()->is_accepting_orders);
    }

    public function test_tax_percentage_above_100_is_rejected(): void
    {
        $this->actingAs($this->admin())->put(route('admin.settings.update'), [
            'restaurant_name_ar' => 'مطعم',
            'restaurant_name_en' => 'Restaurant',
            'tax_percent' => '101',
            'service_percent' => '0',
        ])->assertSessionHasErrors('tax_percent');
    }

    public function test_logo_must_be_a_valid_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->put(route('admin.settings.update'), [
            'restaurant_name_ar' => 'مطعم',
            'restaurant_name_en' => 'Restaurant',
            'tax_percent' => '0',
            'service_percent' => '0',
            'logo' => UploadedFile::fake()->create('menu.pdf', 20, 'application/pdf'),
        ])->assertSessionHasErrors('logo');

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_logo_upload_is_stored_on_the_public_disk(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->put(route('admin.settings.update'), [
            'restaurant_name_ar' => 'مطعم',
            'restaurant_name_en' => 'Restaurant',
            'tax_percent' => '0',
            'service_percent' => '0',
            'logo' => UploadedFile::fake()->image('logo.png', 120, 120),
        ])->assertRedirect();

        $path = Setting::current()->logo_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_open_now_respects_days_and_overnight_windows(): void
    {
        $settings = Setting::current();
        $settings->update([
            'hours' => [
                'sat' => ['open' => '10:00', 'close' => '23:00', 'closed' => false],
                'sun' => ['open' => '10:00', 'close' => '23:00', 'closed' => true],
                'mon' => ['open' => '18:00', 'close' => '02:00', 'closed' => false],
                'tue' => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
                'wed' => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
                'thu' => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
                'fri' => ['open' => '10:00', 'close' => '22:00', 'closed' => false],
            ],
        ]);

        $this->assertTrue($settings->isOpenNow(new \DateTimeImmutable('2026-11-21 12:00:00'))); // Saturday
        $this->assertFalse($settings->isOpenNow(new \DateTimeImmutable('2026-11-22 12:00:00'))); // Sunday closed
        $this->assertTrue($settings->isOpenNow(new \DateTimeImmutable('2026-11-23 23:30:00'))); // Monday overnight
        $this->assertTrue($settings->isOpenNow(new \DateTimeImmutable('2026-11-24 01:00:00'))); // Tuesday early hours
        $this->assertFalse($settings->isOpenNow(new \DateTimeImmutable('2026-11-24 03:00:00')));
    }
}
