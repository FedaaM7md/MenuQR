<?php

/*
 * MenuQR - RolesTest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\MenuFixtures;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use MenuFixtures, RefreshDatabase;

    public function test_role_home_pages_enforce_authorization(): void
    {
        $admin = User::factory()->admin()->create();
        $cashier = User::factory()->cashier()->create();
        $kitchen = User::factory()->kitchen()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($cashier)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($kitchen)->get(route('admin.dashboard'))->assertForbidden();

        $this->actingAs($cashier)->get(route('staff.orders.index'))->assertOk();
        $this->actingAs($kitchen)->get(route('staff.orders.index'))->assertForbidden();

        $this->actingAs($kitchen)->get(route('staff.kitchen.index'))->assertOk();
        $this->actingAs($cashier)->get(route('staff.kitchen.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('staff.kitchen.index'))->assertOk();
    }

    public function test_guests_are_redirected_to_login_from_staff_and_admin_areas(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('staff.orders.index'))->assertRedirect(route('login'));
        $this->get(route('staff.kitchen.index'))->assertRedirect(route('login'));
    }

    public function test_login_redirects_each_role_to_its_home(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'Password1']);
        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'Password1'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        $this->post(route('logout'));

        $cashier = User::factory()->cashier()->create(['password' => 'Password1']);
        $this->post(route('login.store'), ['email' => $cashier->email, 'password' => 'Password1'])
            ->assertRedirect(route('staff.orders.index'));
        $this->assertAuthenticatedAs($cashier);

        $this->post(route('logout'));

        $kitchen = User::factory()->kitchen()->create(['password' => 'Password1']);
        $this->post(route('login.store'), ['email' => $kitchen->email, 'password' => 'Password1'])
            ->assertRedirect(route('staff.kitchen.index'));
        $this->assertAuthenticatedAs($kitchen);
    }

    public function test_login_fails_with_wrong_credentials_and_regenerates_session_on_success(): void
    {
        $user = User::factory()->cashier()->create(['password' => 'Password1']);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->get(route('login'));
        $oldSession = session()->getId();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Password1'])
            ->assertRedirect(route('staff.orders.index'));

        $this->assertNotSame($oldSession, session()->getId());
    }

    public function test_login_is_throttled_after_five_failures(): void
    {
        $user = User::factory()->cashier()->create(['password' => 'Password1']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong'])
                ->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Password1'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertStringContainsString('محاولات كثيرة', session('errors')->first('email'));
    }

    public function test_inactive_accounts_cannot_log_in(): void
    {
        $user = User::factory()->cashier()->inactive()->create(['password' => 'Password1']);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Password1'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertStringContainsString('متوقف', session('errors')->first('email'));
    }

    public function test_admin_can_create_a_staff_member_with_a_hashed_password(): void
    {
        $this->actingAs(User::factory()->admin()->create())->post(route('admin.staff.store'), [
            'name' => 'New Cashier',
            'email' => 'CASHIER@EXAMPLE.TEST',
            'role' => 'cashier',
            'password' => 'Secret123',
            'is_active' => '1',
            'locale' => 'ar',
        ])->assertRedirect(route('admin.staff.index'));

        $created = User::where('email', 'cashier@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('Secret123', $created->password));
        $this->assertTrue($created->is_active);
        $this->assertSame('cashier', $created->role);
    }

    public function test_staff_password_policy_and_role_validation_are_enforced(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'Weak',
            'email' => 'weak@example.test',
            'role' => 'cashier',
            'password' => 'short',
        ])->assertSessionHasErrors('password');

        $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'Bad role',
            'email' => 'role@example.test',
            'role' => 'owner',
            'password' => 'Secret123',
        ])->assertSessionHasErrors('role');
    }

    public function test_admin_cannot_delete_or_demote_their_own_last_admin_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->delete(route('admin.staff.destroy', $admin))
            ->assertSessionHasErrors('staff');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $this->actingAs($admin)->put(route('admin.staff.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'cashier',
            'is_active' => '1',
        ])->assertSessionHasErrors('role');

        $this->assertSame('admin', $admin->refresh()->role);
    }

    public function test_inactive_users_are_blocked_even_with_a_valid_session(): void
    {
        $user = User::factory()->cashier()->inactive()->create();

        $this->actingAs($user)->get(route('staff.orders.index'))->assertForbidden();
    }

    public function test_kitchen_feed_returns_json_without_cache(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $item = $this->makeItem(['name_ar' => 'شوربة']);
        $group = $this->addOptionGroup($item, ['min_select' => 1, 'max_select' => 1], [
            ['صغير', 'Small', 0],
        ]);
        $this->addToCart($table, $item, 2, [$group->options->first()->id]);
        $this->placeOrder($table);

        $response = $this->actingAs(User::factory()->kitchen()->create())
            ->getJson(route('staff.kitchen.feed'));

        $response->assertOk()
            ->assertJsonStructure([
                'orders' => [[
                    'id', 'number', 'table', 'type', 'status', 'status_label',
                    'minutes_waiting', 'total', 'items', 'transitions',
                ]],
                'counts' => ['new', 'preparing', 'ready'],
                'server_time',
            ])
            ->assertJsonPath('counts.new', 1);

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }
}
