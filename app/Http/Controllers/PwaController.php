<?php

/*
 * MenuQR - PwaController.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class PwaController extends Controller
{
    public function manifest(): Response
    {
        return response(file_get_contents(resource_path('pwa/manifest.webmanifest')), 200, [
            'Content-Type' => 'application/manifest+json; charset=UTF-8',
        ]);
    }

    public function serviceWorker(): Response
    {
        return response(file_get_contents(resource_path('pwa/sw.js')), 200, [
            'Content-Type' => 'text/javascript; charset=UTF-8',
            'Service-Worker-Allowed' => '/',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
