<?php

/*
 * MenuQR - TableQrTest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\MenuFixtures;
use Tests\TestCase;

class TableQrTest extends TestCase
{
    use MenuFixtures, RefreshDatabase;

    public function test_tokens_are_long_random_and_unique(): void
    {
        $first = $this->makeTable();
        $second = $this->makeTable();

        $this->assertGreaterThanOrEqual(40, strlen($first->token));
        $this->assertNotSame($first->token, $second->token);
        $this->assertNotSame($first->token, $first->publicUrl());
    }

    public function test_admin_can_download_the_qr_as_svg(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.tables.qr', $table));

        $response->assertOk();
        $this->assertStringContainsString('image/svg+xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<svg', $response->getContent());
    }

    public function test_qr_route_requires_authentication_and_admin_role(): void
    {
        $table = $this->makeTable();

        $this->get(route('admin.tables.qr', $table))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->cashier()->create())
            ->get(route('admin.tables.qr', $table))->assertForbidden();
    }

    public function test_regenerating_invalidates_the_old_token(): void
    {
        $this->configureRestaurant();
        $admin = User::factory()->admin()->create();
        $table = $this->makeTable();
        $oldToken = $table->token;

        $this->actingAs($admin)
            ->post(route('admin.tables.regenerate', $table))
            ->assertRedirect(route('admin.tables.index'));

        $table->refresh();
        $this->assertNotSame($oldToken, $table->token);
        $this->get(route('menu.show', $oldToken))->assertNotFound();
        $this->get(route('menu.show', $table->token))->assertOk();
    }

    public function test_forged_or_modified_tokens_are_rejected(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();
        $token = $table->token;

        $this->get(route('menu.show', $token.'x'))->assertNotFound();
        $this->get(route('menu.show', strtoupper($token)))->assertNotFound();
        $this->get(route('menu.show', substr($token, 0, -1)))->assertNotFound();
        $this->get(route('menu.show', Str::random(48)))->assertNotFound();
    }

    public function test_print_sheet_contains_all_active_table_qr_codes(): void
    {
        $this->configureRestaurant();
        $active = $this->makeTable(['name' => 'Terrace 1']);
        $inactive = $this->makeTable(['name' => 'Closed table', 'is_active' => false]);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.tables.print'));

        $response->assertOk()
            ->assertSee('Terrace 1')
            ->assertDontSee('Closed table')
            ->assertSee('data:image/svg+xml;base64,', false)
            ->assertSee('print-sheet', false);
    }

    public function test_qr_service_outputs_svg_with_the_public_url(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();

        $svg = app(QrCodeService::class)->svg($table);

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
        $this->assertStringContainsString($table->token, $table->publicUrl());
        $this->assertSame(route('menu.show', $table->token), $table->publicUrl());
    }
}
