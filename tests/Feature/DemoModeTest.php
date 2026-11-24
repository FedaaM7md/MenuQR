<?php

/*
 * MenuQR — demo mode behaviour.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MenuFixtures;
use Tests\TestCase;

class DemoModeTest extends TestCase
{
    use MenuFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'menuqr.demo.enabled' => true,
            'menuqr.demo.password' => 'Demo1234!',
        ]);
    }

    private function makeDemoUser(string $role): User
    {
        return User::factory()->create([
            'name' => 'Demo '.$role,
            'email' => User::demoEmail($role),
            'role' => $role,
            'password' => 'Demo1234!',
            'is_active' => true,
            'is_demo' => true,
            'locale' => 'ar',
        ]);
    }

    public function test_demo_entry_points_appear_only_when_demo_mode_is_enabled(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(__('menuqr.demo.try'))
            ->assertSee(__('menuqr.demo.role_admin'));

        config(['menuqr.demo.enabled' => false]);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee(__('menuqr.demo.try'));

        $this->post(route('demo.login'), ['role' => 'admin'])->assertNotFound();
    }

    public function test_one_click_demo_sign_in_for_each_role(): void
    {
        $this->makeDemoUser(User::ROLE_ADMIN);
        $this->makeDemoUser(User::ROLE_CASHIER);
        $this->makeDemoUser(User::ROLE_KITCHEN);

        $this->post(route('demo.login'), ['role' => 'admin'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs(User::where('email', User::demoEmail('admin'))->first());

        $this->post(route('logout'));

        $this->post(route('demo.login'), ['role' => 'cashier'])
            ->assertRedirect(route('staff.orders.index'));

        $this->post(route('logout'));

        $this->post(route('demo.login'), ['role' => 'kitchen'])
            ->assertRedirect(route('staff.kitchen.index'));
    }

    public function test_demo_sign_in_requires_a_valid_role_and_an_existing_demo_account(): void
    {
        $this->post(route('demo.login'), ['role' => 'owner'])->assertSessionHasErrors('role');
        $this->post(route('demo.login'), ['role' => 'admin'])->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    public function test_demo_banner_is_persistent_in_the_staff_shell(): void
    {
        $admin = $this->makeDemoUser(User::ROLE_ADMIN);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(__('menuqr.demo.banner'))
            ->assertDontSee(__('menuqr.demo.reset_note'));
    }

    public function test_demo_admin_cannot_create_another_admin(): void
    {
        $admin = $this->makeDemoUser(User::ROLE_ADMIN);

        $this->actingAs($admin)->post(route('admin.staff.store'), [
            'name' => 'Second admin',
            'email' => 'second@example.test',
            'role' => 'admin',
            'password' => 'Secret123',
            'is_active' => '1',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'second@example.test']);
    }

    public function test_demo_accounts_cannot_be_deleted_or_edited_by_visitors(): void
    {
        $admin = $this->makeDemoUser(User::ROLE_ADMIN);
        $cashier = $this->makeDemoUser(User::ROLE_CASHIER);

        $this->actingAs($admin)
            ->delete(route('admin.staff.destroy', $cashier))
            ->assertSessionHasErrors('staff');

        $this->assertDatabaseHas('users', ['id' => $cashier->id]);

        $this->actingAs($admin)->put(route('admin.staff.update', $cashier), [
            'name' => 'Renamed',
            'email' => 'new-email@example.test',
            'role' => 'admin',
            'password' => 'Hacked12345',
            'is_active' => '0',
        ])->assertRedirect();

        $cashier->refresh();
        $this->assertSame(User::demoEmail(User::ROLE_CASHIER), $cashier->email);
        $this->assertSame(User::ROLE_CASHIER, $cashier->role);
        $this->assertTrue($cashier->is_active);
        $this->assertSame('Renamed', $cashier->name);
        $this->assertTrue(password_verify('Demo1234!', $cashier->password));
    }

    public function test_password_reset_is_never_sent_for_demo_accounts(): void
    {
        $this->makeDemoUser(User::ROLE_ADMIN);

        $this->post(route('password.email'), ['email' => User::demoEmail(User::ROLE_ADMIN)])
            ->assertSessionHas('status');

        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_demo_reset_command_refuses_to_run_when_demo_mode_is_off(): void
    {
        config(['menuqr.demo.enabled' => false]);

        $this->artisan('demo:reset')->assertFailed();
    }
}
