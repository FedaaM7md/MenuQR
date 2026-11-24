<?php

/*
 * MenuQR — one-click demo sign-in.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit;
use App\Support\DemoMode;
use App\Support\RoleHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DemoLoginController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless(DemoMode::enabled(), 404);

        $validated = $request->validate([
            'role' => ['required', Rule::in(config('menuqr.demo.roles', User::ROLES))],
        ]);

        $user = User::demoUser($validated['role']);

        if (! $user instanceof User) {
            throw ValidationException::withMessages([
                'role' => __('menuqr.demo.unavailable'),
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('locale', $user->locale ?: app()->getLocale());

        Audit::log('demo.login', $user, __('menuqr.activity.descriptions.demo_login', ['role' => $user->roleLabel()]), ['role' => $user->role]);

        return redirect()
            ->to(RoleHome::for($user))
            ->with('status', __('menuqr.auth.demo_welcome', ['role' => $user->roleLabel()]));
    }
}
