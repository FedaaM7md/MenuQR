<?php

/*
 * MenuQR product configuration.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    | The product name stands alone. "by Fedaa" is only used as a quiet
    | attribution line near the logo and in the footer.
    */
    'brand' => [
        'name' => 'MenuQR',
        'owner' => 'Fedaa Mohamed',
        'owner_ar' => 'فداء محمد',
        'attribution_en' => 'by Fedaa',
        'attribution_ar' => 'من فداء',
        'copyright_year' => 2026,
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo mode
    |--------------------------------------------------------------------------
    | Demo entry points are removed (404) when this is false. The shared
    | demo dataset is reseeded by `php artisan demo:reset` and hourly.
    */
    'demo' => [
        'enabled' => (bool) env('DEMO_MODE', true),
        'password' => env('DEMO_PASSWORD', 'Demo1234!'),
        'roles' => ['admin', 'cashier', 'kitchen'],
        'reset_enabled' => (bool) env('DEMO_RESET_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    | Files are re-encoded server-side to WebP with metadata stripped.
    | Demo accounts get a tighter limit than real staff accounts.
    */
    'uploads' => [
        'max_kb' => (int) env('UPLOAD_MAX_KB', 2048),
        'demo_max_kb' => (int) env('DEMO_UPLOAD_MAX_KB', 512),
        'max_dimension' => 1600,
        'quality' => 82,
    ],

];
