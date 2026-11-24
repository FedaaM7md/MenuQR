<?php

/*
 * MenuQR - PwaTest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MenuFixtures;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use MenuFixtures, RefreshDatabase;

    public function test_manifest_is_served_with_the_right_content_type_and_fields(): void
    {
        $response = $this->get(route('pwa.manifest'));

        $response->assertOk();
        $this->assertStringContainsString('application/manifest+json', $response->headers->get('Content-Type'));

        $manifest = json_decode($response->getContent(), true);
        $this->assertSame('MenuQR', $manifest['name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('/', $manifest['start_url']);
        $this->assertSame('#23201C', $manifest['theme_color']);
        $this->assertCount(3, $manifest['icons']);
        $this->assertSame('maskable', $manifest['icons'][2]['purpose']);
    }

    public function test_service_worker_is_served_from_the_root_scope(): void
    {
        $response = $this->get(route('pwa.service-worker'));

        $response->assertOk();
        $this->assertStringContainsString('text/javascript', $response->headers->get('Content-Type'));
        $this->assertSame('/', $response->headers->get('Service-Worker-Allowed'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));

        $body = $response->getContent();
        $this->assertStringContainsString('menuqr-v2', $body);
        $this->assertStringContainsString("request.method !== 'GET'", $body);
        $this->assertStringContainsString("'/offline'", $body);
        $this->assertStringContainsString("url.pathname.startsWith('/m/')", $body);
    }

    public function test_offline_page_renders(): void
    {
        $this->get(route('offline'))
            ->assertOk()
            ->assertSee('أنت غير متصل')
            ->assertSee('إعادة المحاولة');
    }

    public function test_public_layouts_link_the_manifest_and_theme(): void
    {
        $this->configureRestaurant();
        $table = $this->makeTable();

        $response = $this->get(route('menu.show', $table->token));

        $response->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('name="theme-color" content="#23201C"', false)
            ->assertSee('apple-touch-icon', false);
    }
}
