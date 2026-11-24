{{--
    MenuQR public shell (customer-facing).
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ text_dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#23201C">
    <title>@yield('title', __('menuqr.brand.name')) — {{ __('menuqr.brand.name') }}</title>
    <meta name="description" content="@yield('meta_description', __('menuqr.app.description'))">
    @php($metaTitle = trim($__env->yieldContent('title')) ?: __('menuqr.brand.name'))
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ __('menuqr.brand.name') }}">
    <meta property="og:title" content="{{ $metaTitle }} — {{ __('menuqr.brand.name') }}">
    <meta property="og:description" content="@yield('meta_description', __('menuqr.app.description'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('icons/icon-512.png') }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'ar' ? 'ar_EG' : 'en_US' }}">
    <meta name="twitter:card" content="summary">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-cream">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:start-3 focus:z-[100] focus:rounded-sm focus:bg-saffron focus:px-4 focus:py-2 focus:font-bold focus:text-charcoal">
        {{ __('menuqr.common.skip_to_content') }}
    </a>

    <div id="offline-banner" class="hidden bg-saffron px-4 py-2 text-center text-sm font-bold text-charcoal" role="status">
        {{ __('menuqr.pwa.offline_body') }}
    </div>

    @php($settings = $settings ?? setting())
    @php($cartCount = $cartCount ?? (isset($table) ? app(\App\Services\CartService::class)->count($table) : 0))
    @if (auth()->check() && auth()->user()->isDemo())
        <div class="demo-bar" role="status">
            <span class="inline-flex items-center gap-1.5">
                <x-icon name="alert" class="h-3.5 w-3.5" />
                {{ __('menuqr.demo.banner') }}
            </span>
            <a href="{{ \App\Support\RoleHome::for(auth()->user()) }}">{{ __('menuqr.app.staff') }}</a>
        </div>
    @endif

    <header class="no-print border-b border-line bg-cream">
        <div class="container-page flex h-16 items-center gap-3 sm:h-20">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2.5 rounded-sm" aria-label="{{ __('menuqr.brand.name') }}">
                @if (request()->routeIs('home'))
                    <x-logo class="h-10 w-10" />
                @else
                    @if ($settings->logo_path)
                        <img src="{{ media_url($settings->logo_path) }}" alt="" class="h-10 w-10 shrink-0 rounded-sm object-cover ring-1 ring-line">
                    @else
                        <x-logo class="h-10 w-10" :show-text="false" />
                    @endif
                    <span class="min-w-0">
                        <span class="block truncate font-display text-sm font-bold leading-tight text-charcoal sm:hidden">{{ \Illuminate\Support\Str::before($settings->name(), ' —') }}</span>
                        <span class="hidden truncate font-display text-base font-bold leading-tight text-charcoal sm:block">{{ $settings->name() }}</span>
                        <span class="block truncate text-[0.68rem] font-semibold text-muted">
                            @isset($table)
                                {{ __('menuqr.menu.table_label', ['name' => $table->name]) }}
                            @else
                                {{ __('menuqr.brand.descriptor') }}
                            @endisset
                        </span>
                    </span>
                @endif
            </a>

            <div class="ms-auto flex items-center gap-2">
                @isset($isOpen)
                    <span class="hidden items-center gap-1.5 rounded-sm border px-2.5 py-1 text-xs font-bold sm:inline-flex {{ $isOpen ? 'border-olive/30 bg-olive-soft text-olive-deep' : 'border-tomato/25 bg-tomato-soft text-tomato-deep' }}">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        {{ $isOpen ? __('menuqr.menu.open_now') : __('menuqr.menu.closed_now') }}
                    </span>
                @endisset

                <x-lang-switch />

                @isset($table)
                    <a href="{{ route('menu.cart', $table->token) }}" class="btn-primary relative btn-sm sm:px-4 sm:py-2.5" aria-label="{{ __('menuqr.menu.view_cart') }}">
                        <x-icon name="cart" class="h-4 w-4" />
                        <span class="hidden sm:inline">{{ __('menuqr.menu.view_cart') }}</span>
                        @if (($cartCount ?? 0) > 0)
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-sm bg-saffron px-1.5 text-[0.65rem] font-bold text-charcoal">{{ $cartCount }}</span>
                        @endif
                    </a>
                @else
                    @auth
                        <a href="{{ \App\Support\RoleHome::for(auth()->user()) }}" class="btn-secondary btn-sm">{{ __('menuqr.app.staff') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-secondary btn-sm">
                            <x-icon name="lock" class="hidden h-4 w-4 sm:block" />
                            <span class="hidden sm:inline">{{ __('menuqr.home.cta_staff') }}</span>
                            <span class="sm:hidden">{{ __('menuqr.auth.submit') }}</span>
                        </a>
                    @endauth
                @endisset
            </div>
        </div>
    </header>

    <main id="main-content">
        <div class="container-page pt-4">
            @include('partials.flash')
        </div>
        @yield('content')
    </main>

    <footer class="mt-14 border-t border-line bg-sand">
        <div class="container-page flex flex-col gap-5 py-8 text-sm text-muted sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
                <x-logo class="h-9 w-9" :show-text="false" />
                <div>
                    <p class="font-semibold text-ink-soft">{{ __('menuqr.brand.name') }} · {{ __('menuqr.brand.descriptor') }}</p>
                    <p class="mt-0.5 text-xs">{{ __('menuqr.footer.rights', ['year' => config('menuqr.brand.copyright_year'), 'product' => __('menuqr.brand.name')]) }}</p>
                    <p class="mt-0.5 text-xs">
                        <a href="https://fedaamohamed.com" rel="noopener" class="hover:text-charcoal">{{ __('menuqr.footer.credit', ['owner' => __('menuqr.brand.owner')]) }}</a>
                    </p>
                    <p class="mt-1 text-[0.68rem] text-muted/80">{{ __('menuqr.footer.demo_note') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs">
                <a href="{{ route('info.about') }}" class="hover:text-charcoal">{{ __('menuqr.info.about_title') }}</a>
                <a href="{{ route('info.contact') }}" class="hover:text-charcoal">{{ __('menuqr.info.contact_title') }}</a>
                <a href="{{ route('info.privacy') }}" class="hover:text-charcoal">{{ __('menuqr.info.privacy_title') }}</a>
                <a href="{{ route('info.terms') }}" class="hover:text-charcoal">{{ __('menuqr.info.terms_title') }}</a>
                @if ($settings->address())
                    <span class="inline-flex items-center gap-1.5"><x-icon name="tag" class="h-3.5 w-3.5" />{{ $settings->address() }}</span>
                @endif
                <a href="{{ route('login') }}" class="link">{{ __('menuqr.auth.staff_only') }}</a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
