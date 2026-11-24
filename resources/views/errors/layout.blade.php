{{--
    MenuQR error shell.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ text_dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#23201C">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') — {{ __('menuqr.brand.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-cream">
    <div class="flex min-h-screen flex-col">
        <header class="container-page flex h-20 items-center justify-between">
            <a href="{{ route('home') }}" class="rounded-sm"><x-logo class="h-10 w-10" /></a>
            <x-lang-switch />
        </header>

        <main class="container-page flex flex-1 items-center justify-center py-10">
            <div class="w-full max-w-lg">
                <div class="menu-paper px-6 py-10 text-center sm:px-10">
                    <p class="tnum font-display text-7xl font-bold text-saffron-soft sm:text-8xl">@yield('code')</p>
                    <h1 class="mt-2 font-display text-2xl font-bold text-charcoal sm:text-3xl">@yield('heading')</h1>
                    <p class="mt-3 text-sm leading-7 text-muted sm:text-base">@yield('body')</p>
                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('home') }}" class="btn-primary">
                            <x-icon name="arrow" class="h-4 w-4 rotate-180 rtl:rotate-0" />
                            {{ __('menuqr.errors.go_home') }}
                        </a>
                        <a href="{{ route('login') }}" class="btn-secondary">{{ __('menuqr.errors.go_login') }}</a>
                    </div>
                </div>
            </div>
        </main>

        <footer class="container-page py-6 text-center text-xs text-muted">
            <p>{{ __('menuqr.brand.name') }} · {{ __('menuqr.brand.descriptor') }} · {{ __('menuqr.footer.rights', ['year' => config('menuqr.brand.copyright_year'), 'product' => __('menuqr.brand.name')]) }}</p>
            <p class="mt-1"><a href="https://fedaamohamed.com" rel="noopener" class="hover:text-charcoal">{{ __('menuqr.footer.credit', ['owner' => __('menuqr.brand.owner')]) }}</a></p>
        </footer>
    </div>
</body>
</html>
