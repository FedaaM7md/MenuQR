<?php

/*
 * MenuQR - SetLocale.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED = ['ar', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (! is_string($locale) || ! in_array($locale, self::SUPPORTED, true)) {
            $locale = $request->user()?->locale;

            if (! is_string($locale) || ! in_array($locale, self::SUPPORTED, true)) {
                $locale = config('app.locale', 'ar');
            }
        }

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = 'ar';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
