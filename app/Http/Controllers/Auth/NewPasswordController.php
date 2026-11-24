<?php

/*
 * MenuQR — complete a password reset.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:120'],
            'password' => ['required', 'confirmed', PasswordRule::min(10)->letters()->numbers()],
        ]);

        $email = strtolower((string) $request->input('email'));

        if (str_ends_with($email, '@'.User::DEMO_EMAIL_DOMAIN)) {
            return back()->withErrors(['email' => __('menuqr.demo.account_locked')]);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                Audit::log('auth.password_reset', $user, __('menuqr.activity.descriptions.password_reset'));
            },
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __('menuqr.auth.reset_done'))
            : back()->withInput($request->only('email'))->withErrors(['email' => __('menuqr.auth.reset_failed')]);
    }
}
