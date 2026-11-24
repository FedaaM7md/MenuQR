<?php

/*
 * MenuQR - ErrorPageTest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_pages_render_the_branded_404_page(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response->assertNotFound()
            ->assertSee('الصفحة غير موجودة')
            ->assertSee('dir="rtl"', false)
            ->assertSee('MenuQR', false);
    }

    public function test_forbidden_requests_render_the_branded_403_page(): void
    {
        $cashier = User::factory()->cashier()->create();

        $this->actingAs($cashier)
            ->get(route('admin.settings.edit'))
            ->assertForbidden()
            ->assertSee('لا يمكن الوصول')
            ->assertSee('403');
    }
}
