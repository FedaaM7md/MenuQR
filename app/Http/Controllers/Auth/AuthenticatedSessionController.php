<?php

/*
 * MenuQR — staff authentication.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\MenuItem;
use App\Services\Audit;
use App\Support\RoleHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to(RoleHome::for(Auth::user()));
        }

        return view('auth.login', [
            'previewItems' => MenuItem::query()->available()->ordered()->take(4)->get(),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => __('menuqr.auth.inactive'),
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('locale', $user->locale ?: app()->getLocale());

        Audit::log('auth.login', $user, __('menuqr.activity.descriptions.login'));

        return redirect()->intended(RoleHome::for($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Sign out of every device by removing this user's stored sessions.
     */
    public function destroyAll(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user && Schema::hasTable(config('session.table', 'sessions'))) {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->delete();
        }

        Audit::log('auth.logout_all', $user, __('menuqr.activity.descriptions.logout_all'));

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', __('menuqr.auth.logged_out_all'));
    }
}
