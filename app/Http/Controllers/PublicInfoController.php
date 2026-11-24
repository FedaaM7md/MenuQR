<?php

/*
 * MenuQR - public restaurant pages.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers;

use Illuminate\View\View;

class PublicInfoController extends Controller
{
    public function about(): View
    {
        return view('info.about', ['settings' => setting()]);
    }

    public function contact(): View
    {
        return view('info.contact', ['settings' => setting()]);
    }

    public function privacy(): View
    {
        return view('info.privacy', ['settings' => setting()]);
    }

    public function terms(): View
    {
        return view('info.terms', ['settings' => setting()]);
    }
}
