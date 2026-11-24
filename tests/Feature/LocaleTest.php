<?php

/*
 * MenuQR - LocaleTest.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MenuFixtures;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use MenuFixtures, RefreshDatabase;

    private function flatten(array $array, string $prefix = ''): array
    {
        $keys = [];

        foreach ($array as $key => $value) {
            $full = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value)) {
                $keys = array_merge($keys, $this->flatten($value, $full));
            } else {
                $keys[] = $full;
            }
        }

        return $keys;
    }

    public function test_arabic_is_the_default_locale_with_rtl_direction(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('منيو رقمي');
    }

    public function test_language_switch_changes_direction_and_strings(): void
    {
        $this->get(route('locale.switch', 'en'))
            ->assertRedirect(route('home'));

        $this->assertSame('en', session('locale'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('dir="ltr"', false)
            ->assertSee('The menu on the table. The order in the kitchen.');

        $this->get(route('locale.switch', 'ar'))->assertRedirect(route('home'));
        $this->get(route('home'))->assertSee('dir="rtl"', false);
    }

    public function test_unsupported_locales_are_rejected(): void
    {
        $this->get(route('locale.switch', 'fr'))->assertNotFound();
        $this->get(route('locale.switch', 'de'))->assertNotFound();
    }

    public function test_locale_switch_cannot_redirect_to_an_external_referer(): void
    {
        $this->withHeader('referer', 'https://evil.example/phish')
            ->get(route('locale.switch', 'en'))
            ->assertRedirect(route('home'));
    }

    public function test_switching_locale_persists_the_preference_on_the_user(): void
    {
        $user = User::factory()->admin()->create(['locale' => 'ar']);

        $this->actingAs($user)
            ->withHeader('referer', route('admin.dashboard'))
            ->get(route('locale.switch', 'en'))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertSame('en', $user->refresh()->locale);
    }

    public function test_model_names_follow_the_active_locale(): void
    {
        $item = $this->makeItem(['name_ar' => 'كفتة', 'name_en' => 'Kofta', 'description_ar' => 'وصف', 'description_en' => 'Desc']);

        app()->setLocale('ar');
        $this->assertSame('كفتة', $item->name());
        $this->assertSame('وصف', $item->description());

        app()->setLocale('en');
        $this->assertSame('Kofta', $item->name());
        $this->assertSame('Desc', $item->description());
    }

    public function test_arabic_validation_messages_are_used(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.categories.store'), ['name_ar' => '', 'name_en' => ''])
            ->assertSessionHasErrors('name_ar');

        $message = session('errors')->first('name_ar');
        $this->assertStringContainsString('مطلوب', $message);
    }

    public function test_every_translation_key_exists_in_both_languages(): void
    {
        $ar = $this->flatten(require lang_path('ar/menuqr.php'));
        $en = $this->flatten(require lang_path('en/menuqr.php'));

        sort($ar);
        sort($en);

        $this->assertSame([], array_values(array_diff($ar, $en)), 'Keys missing from English translation file.');
        $this->assertSame([], array_values(array_diff($en, $ar)), 'Keys missing from Arabic translation file.');
    }
}
