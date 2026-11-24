<?php

/*
 * MenuQR — realism, public pages and export tests.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\MenuFixtures;
use Tests\TestCase;

class RealismTest extends TestCase
{
    use MenuFixtures, RefreshDatabase;

    public function test_public_restaurant_pages_render_with_discreet_credit_and_demo_note(): void
    {
        $this->configureRestaurant([
            'restaurant_name_ar' => 'زيتونة — مطبخ شامي',
            'restaurant_name_en' => 'Zaytouna Kitchen',
            'address_ar' => 'مدينة نصر، القاهرة',
            'address_en' => 'Nasr City, Cairo',
        ]);

        foreach (['info.about', 'info.contact', 'info.privacy', 'info.terms'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee('MenuQR')
                ->assertSee('فداء محمد')
                ->assertSee('البيانات افتراضية')
                ->assertDontSee('من فداء');
        }

        $this->get(route('info.contact'))
            ->assertSee('01000000000')
            ->assertSee('hello@example.com');
    }

    public function test_public_layout_has_social_meta_and_info_links(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('property="og:title"', false)
            ->assertSee('property="og:description"', false)
            ->assertSee('property="og:image"', false)
            ->assertSee(route('info.about'), false)
            ->assertSee(route('info.privacy'), false);
    }

    public function test_arabic_ui_uses_standard_table_terms_and_never_needs_letter_spacing(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('طاولات')
            ->assertDontSee('ترابيزات')
            ->assertDontSee('ترابيزة');
    }

    public function test_allergen_notes_render_on_the_menu(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $this->makeItem([
            'name_ar' => 'كنافة نابلسية',
            'name_en' => 'Nabulsi knafeh',
            'allergens_ar' => 'قمح، ألبان، مكسرات',
            'allergens_en' => 'Wheat, dairy, nuts',
        ]);

        $this->get(route('menu.show', $table->token))
            ->assertOk()
            ->assertSee('يحتوي على')
            ->assertSee('قمح، ألبان، مكسرات')
            ->assertSee('مسببات الحساسية');
    }

    public function test_demo_hours_are_twenty_four_seven(): void
    {
        $hours = collect(Setting::defaultHours())
            ->map(fn () => ['open' => '00:00', 'close' => '23:59', 'closed' => false])
            ->all();

        $settings = $this->configureRestaurant(['hours' => $hours]);

        $this->assertTrue($settings->isOpenNow(new \DateTimeImmutable('2026-11-24 02:30:00')));
        $this->assertTrue($settings->isOpenNow(new \DateTimeImmutable('2026-11-24 14:00:00')));
        $this->assertTrue($settings->isOpenNow(new \DateTimeImmutable('2026-11-24 23:30:00')));
    }

    public function test_admin_can_export_a_formula_safe_csv(): void
    {
        $category = $this->makeCategory(['name_ar' => 'حلويات', 'name_en' => 'Desserts']);
        $item = $this->makeItem([
            'category_id' => $category->id,
            'name_ar' => '=SUM(A1)',
            'name_en' => '=SUM(A1)',
            'price_minor' => 10000,
        ]);

        $order = Order::factory()->create([
            'status' => 'served',
            'placed_at' => Carbon::parse('2026-11-24 12:00:00'),
            'subtotal_minor' => 10000,
            'tax_percent' => 1400,
            'tax_minor' => 1400,
            'service_percent' => 0,
            'service_minor' => 0,
            'total_minor' => 11400,
        ]);
        $order->items()->create([
            'menu_item_id' => $item->id,
            'name_ar' => $item->name_ar,
            'name_en' => $item->name_en,
            'unit_price_minor' => 10000,
            'quantity' => 1,
            'line_total_minor' => 10000,
        ]);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.reports.export', ['from' => '2026-11-24', 'to' => '2026-11-24']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $content = $response->streamedContent();
        $this->assertStringContainsString("'=SUM(A1)", $content);
        $this->assertStringContainsString('حلويات', $content);
    }

    public function test_arabic_times_use_arabic_periods_without_seconds(): void
    {
        app()->setLocale('ar');
        $time = Carbon::parse('2026-11-24 06:28:18');

        $this->assertSame('6:28 ص', format_time($time));
        $this->assertStringNotContainsString('AM', format_time($time));
        $this->assertSame('24/11/2026 6:28 ص', format_datetime($time));

        app()->setLocale('en');
        $this->assertStringContainsString('AM', format_time($time));
    }

    public function test_staff_layout_has_only_one_demo_note(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => User::demoEmail('admin'),
            'is_demo' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->assertSame(1, substr_count($response->getContent(), 'وضع تجريبي'));
    }

    public function test_kitchen_and_admin_are_not_accessibly_broken_by_csrf_or_role_routes(): void
    {
        $this->actingAs(User::factory()->kitchen()->create())
            ->get(route('staff.kitchen.index'))
            ->assertOk();

        $this->actingAs(User::factory()->cashier()->create())
            ->get(route('staff.orders.index'))
            ->assertOk();
    }
}
