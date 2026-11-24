<?php

/*
 * MenuQR - EnsureRole.
 * Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        if (! $user->is_active) {
            abort(403, 'Account disabled.');
        }

        if ($roles !== [] && ! $user->hasRole(...$roles)) {
            abort(403);
        }

        return $next($request);
    }
}
