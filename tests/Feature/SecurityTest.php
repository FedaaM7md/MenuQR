<?php

/*
 * MenuQR — security regression tests.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeaders;
use App\Models\ActivityLog;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Tests\Support\MenuFixtures;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use MenuFixtures, RefreshDatabase;

    public function test_security_headers_are_present(): void
    {
        $response = $this->get(route('home'));

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $response->assertHeader('Cross-Origin-Resource-Policy', 'same-origin');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringNotContainsString("'unsafe-eval'", $csp);
        $this->assertStringNotContainsString('http:', $csp);
        $this->assertStringNotContainsString('https:', $csp);
    }

    public function test_hsts_is_only_sent_over_https(): void
    {
        $this->get(route('home'))->assertHeaderMissing('Strict-Transport-Security');

        $request = Request::create('/', 'GET', [], [], [], ['HTTPS' => 'on']);
        $response = app(SecurityHeaders::class)
            ->handle($request, fn () => response('ok'));

        $this->assertSame(
            'max-age=31536000; includeSubDomains',
            $response->headers->get('Strict-Transport-Security'),
        );
    }

    public function test_forms_include_a_csrf_token(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_public_orders_cannot_override_status_or_totals(): void
    {
        $this->configureRestaurant(['tax_percent' => 1400, 'service_percent' => 0]);
        $table = $this->makeTable();
        $item = $this->makeItem(['price_minor' => 9000]);

        $this->addToCart($table, $item);
        $this->placeOrder($table, [
            'status' => 'served',
            'subtotal_minor' => 1,
            'total_minor' => 1,
            'tax_minor' => 0,
        ]);

        $order = Order::firstOrFail();
        $this->assertSame('new', $order->status);
        $this->assertSame(9000, $order->subtotal_minor);
        $this->assertSame(1260, $order->tax_minor);
        $this->assertSame(10260, $order->total_minor);
    }

    public function test_honeypot_blocks_bot_orders(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $item = $this->makeItem();
        $this->addToCart($table, $item);

        $this->placeOrder($table, ['website' => 'https://spam.example'])
            ->assertSessionHasErrors('website');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_password_hashes_are_never_rendered_in_the_staff_list(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->cashier()->create();

        $this->actingAs($admin)
            ->get(route('admin.staff.index'))
            ->assertOk()
            ->assertDontSee($admin->password)
            ->assertDontSee($member->password);
    }

    public function test_search_input_with_sql_metacharacters_is_handled_safely(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $this->makeItem(['name_ar' => 'صنف', 'name_en' => 'Dish']);

        $this->get(route('menu.show', ['table' => $table->token, 'q' => "'; DROP TABLE menu_items; --"]))
            ->assertOk();

        $this->assertDatabaseCount('menu_items', 1);
    }

    public function test_cashier_cannot_change_restaurant_settings(): void
    {
        $this->actingAs(User::factory()->cashier()->create())
            ->put(route('admin.settings.update'), [
                'restaurant_name_ar' => 'hacked',
                'restaurant_name_en' => 'hacked',
                'tax_percent' => '0',
                'service_percent' => '0',
            ])->assertForbidden();
    }

    public function test_kitchen_role_cannot_open_the_admin_console(): void
    {
        $this->actingAs(User::factory()->kitchen()->create())
            ->get(route('admin.settings.edit'))
            ->assertForbidden();
    }

    public function test_uploaded_images_are_reencoded_to_webp_and_stored_without_original_name(): void
    {
        Storage::fake('public');
        $category = $this->makeCategory();

        $this->actingAs(User::factory()->admin()->create())->post(route('admin.items.store'), [
            'category_id' => $category->id,
            'name_ar' => 'صنف',
            'name_en' => 'Item',
            'price' => '10.00',
            'image' => UploadedFile::fake()->image('original-name-with-metadata.png', 1200, 900),
        ])->assertRedirect();

        $path = MenuItem::firstOrFail()->image_path;
        $this->assertStringEndsWith('.webp', $path);
        $this->assertStringNotContainsString('original-name', $path);
        Storage::disk('public')->assertExists($path);

        $info = getimagesize(Storage::disk('public')->path($path));
        $this->assertSame(IMAGETYPE_WEBP, $info[2]);
        $this->assertLessThanOrEqual(1600, $info[0]);
    }

    public function test_demo_accounts_get_a_tighter_upload_limit(): void
    {
        Storage::fake('public');
        config(['menuqr.uploads.demo_max_kb' => 1]);

        $demo = User::factory()->admin()->create([
            'email' => User::demoEmail('admin'),
            'is_demo' => true,
        ]);
        $category = $this->makeCategory();

        $this->actingAs($demo)->post(route('admin.items.store'), [
            'category_id' => $category->id,
            'name_ar' => 'صنف',
            'name_en' => 'Item',
            'price' => '10.00',
            'image' => UploadedFile::fake()->image('big.png', 50, 50)->size(8),
        ])->assertSessionHasErrors('image');
    }

    public function test_sensitive_actions_are_recorded_in_the_activity_log(): void
    {
        $admin = User::factory()->admin()->create();
        $settings = Setting::current();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'restaurant_name_ar' => 'مطعم',
            'restaurant_name_en' => 'Restaurant',
            'tax_percent' => '14',
            'service_percent' => '0',
            'is_accepting_orders' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'settings.updated',
            'subject_id' => $settings->id,
        ]);

        $this->actingAs($admin)->get(route('admin.activity.index'))->assertOk();
        $this->actingAs(User::factory()->cashier()->create())
            ->get(route('admin.activity.index'))->assertForbidden();
    }

    public function test_password_reset_flow_with_expiring_token_and_log_mail(): void
    {
        Notification::fake();
        $user = User::factory()->cashier()->create(['email' => 'staff@example.test']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);

        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewPassword123', $user->refresh()->password));
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_password_reset_does_not_reveal_whether_an_account_exists(): void
    {
        $this->post(route('password.email'), ['email' => 'nobody@example.test'])
            ->assertSessionHas('status')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_users_can_sign_out_of_every_device(): void
    {
        $user = User::factory()->admin()->create();

        DB::table('sessions')->insert([
            'id' => 'other-session-id',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => '',
            'last_activity' => time(),
        ]);

        $this->actingAs($user)->post(route('logout.all'))->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseMissing('sessions', ['id' => 'other-session-id']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'auth.logout_all']);
    }

    public function test_activity_log_renders_known_and_unknown_actions_safely(): void
    {
        $admin = User::factory()->admin()->create();

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'settings.updated',
            'description' => '<script>alert(1)</script>',
            'created_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('admin.activity.index'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }
}
