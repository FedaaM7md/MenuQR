<?php

/*
 * MenuQR - LocaleController.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, SetLocale::SUPPORTED, true), 404);

        $request->session()->put('locale', $locale);

        if ($request->user()) {
            $request->user()->forceFill(['locale' => $locale])->save();
        }

        $referer = $request->headers->get('referer');
        $fallback = route('home');

        if (is_string($referer)) {
            $host = parse_url($referer, PHP_URL_HOST);
            $scheme = parse_url($referer, PHP_URL_SCHEME);
            if ($host === $request->getHost() && in_array($scheme, ['http', 'https'], true)) {
                return redirect()->to($referer);
            }
        }

        return redirect()->to($fallback);
    }
}
