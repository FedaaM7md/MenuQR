<?php

/*
 * MenuQR — naming, attribution and ownership copy.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_product_name_stands_alone_and_owner_credit_is_footer_only(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('<title>'.__('menuqr.app.tagline').' — MenuQR</title>', false)
            ->assertDontSee('Fedaa MenuQR', false)
            ->assertDontSee('فداء MenuQR', false)
            ->assertDontSee('by Fedaa', false)
            ->assertDontSee('من فداء', false)
            ->assertSee(__('menuqr.footer.rights', [
                'year' => config('menuqr.brand.copyright_year'),
                'product' => __('menuqr.brand.name'),
            ]))
            ->assertSee(__('menuqr.footer.credit', ['owner' => __('menuqr.brand.owner')]))
            ->assertSee('https://fedaamohamed.com', false);
    }

    public function test_the_default_locale_uses_the_arabic_credit_line(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('© 2026 MenuQR. جميع الحقوق محفوظة.')
            ->assertSee('تصميم وتطوير: فداء محمد')
            ->assertDontSee('من فداء');
    }

    public function test_translation_keys_stay_identical_between_arabic_and_english(): void
    {
        $this->assertSame(
            $this->flatten(require lang_path('ar/menuqr.php')),
            $this->flatten(require lang_path('en/menuqr.php')),
            'Arabic and English key sets must be identical.',
        );
    }

    /** @return array<int, string> */
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

        sort($keys);

        return $keys;
    }
}
