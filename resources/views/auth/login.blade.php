{{--
    MenuQR sign-in + one-click demo entry.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.auth')

@section('title', __('menuqr.auth.title'))
@section('heading', __('menuqr.auth.title'))
@section('subheading', __('menuqr.auth.subtitle'))

@section('content')
    @if (\App\Support\DemoMode::enabled())
        <div id="demo" class="rounded-sm border border-saffron/40 bg-saffron-50 p-4">
            <p class="font-display text-base font-bold text-charcoal">{{ __('menuqr.demo.try') }}</p>
            <p class="mt-1 text-xs leading-5 text-ink-soft">{{ __('menuqr.demo.role_hint') }}</p>

            <div class="mt-3 grid gap-2 sm:grid-cols-3">
                @foreach (['admin', 'cashier', 'kitchen'] as $role)
                    <form method="POST" action="{{ route('demo.login') }}">
                        @csrf
                        <input type="hidden" name="role" value="{{ $role }}">
                        <button type="submit" class="btn-secondary w-full flex-col gap-0.5 py-2 text-xs">
                            <span class="font-bold">{{ __('menuqr.demo.role_'.$role) }}</span>
                            <span class="text-[0.62rem] font-semibold text-muted">{{ __('menuqr.roles.'.$role) }}</span>
                        </button>
                    </form>
                @endforeach
            </div>
            <p class="mt-3 text-[0.68rem] text-muted">{{ __('menuqr.demo.banner') }}</p>
        </div>

        <div class="relative my-7">
            <div class="absolute inset-0 flex items-center" aria-hidden="true"><span class="w-full border-t border-line"></span></div>
            <div class="relative flex justify-center"><span class="bg-cream px-3 text-xs font-semibold text-muted">{{ __('menuqr.auth.demo_or') }}</span></div>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="label">{{ __('menuqr.auth.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="input @error('email') input-invalid @enderror" placeholder="{{ __('menuqr.auth.email_placeholder') }}" dir="ltr">
            @error('email')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-baseline justify-between gap-2">
                <label for="password" class="label">{{ __('menuqr.auth.password') }}</label>
                <a href="{{ route('password.request') }}" class="mb-1.5 text-xs font-semibold text-saffron-deep hover:underline">{{ __('menuqr.auth.forgot') }}</a>
            </div>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="input @error('password') input-invalid @enderror" dir="ltr">
            @error('password')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2.5 text-sm font-medium text-ink-soft">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded-sm border-line-strong text-charcoal focus:ring-saffron">
            {{ __('menuqr.auth.remember') }}
        </label>

        <button type="submit" class="btn-primary w-full py-3">
            {{ __('menuqr.auth.submit') }}
            <x-icon name="arrow" class="h-4 w-4 rtl:rotate-180" />
        </button>
    </form>
@endsection
