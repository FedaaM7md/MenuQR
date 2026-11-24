<?php

/*
 * MenuQR — password reset link request.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:120'],
        ]);

        $email = strtolower((string) $request->input('email'));

        // Demo accounts are shared and must never be resettable by visitors.
        if (! str_ends_with($email, '@'.User::DEMO_EMAIL_DOMAIN)) {
            Password::sendResetLink(['email' => $email]);
        }

        // Always answer the same way to avoid account enumeration.
        return back()->with('status', __('menuqr.auth.reset_sent'));
    }
}
