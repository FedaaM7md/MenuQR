<?php

/*
 * MenuQR — public landing.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Support\RoleHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->to(RoleHome::for($request->user()));
        }

        $featured = MenuItem::query()
            ->available()
            ->with('category')
            ->orderByDesc('is_featured')
            ->ordered()
            ->take(5)
            ->get();

        return view('home', [
            'featured' => $featured,
            'settings' => setting(),
        ]);
    }
}
