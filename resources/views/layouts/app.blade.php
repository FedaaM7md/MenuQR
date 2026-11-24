{{--
    MenuQR staff/admin shell.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ text_dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#23201C">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', __('menuqr.app.admin')) — {{ __('menuqr.brand.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:start-3 focus:z-[100] focus:rounded-sm focus:bg-saffron focus:px-4 focus:py-2 focus:font-bold focus:text-charcoal">
        {{ __('menuqr.common.skip_to_content') }}
    </a>

    @php
        $user = auth()->user();
        $links = [];

        if ($user?->isAdmin()) {
            $links = [
                ['label' => 'nav.operations', 'type' => 'heading'],
                ['route' => 'admin.dashboard', 'match' => ['admin.dashboard'], 'icon' => 'dashboard', 'label' => 'nav.dashboard'],
                ['route' => 'staff.orders.index', 'match' => ['staff.orders.*'], 'icon' => 'orders', 'label' => 'nav.orders'],
                ['route' => 'staff.kitchen.index', 'match' => ['staff.kitchen.*'], 'icon' => 'kitchen', 'label' => 'nav.kitchen'],
                ['label' => 'nav.management', 'type' => 'heading'],
                ['route' => 'admin.categories.index', 'match' => ['admin.categories.*'], 'icon' => 'tag', 'label' => 'nav.categories'],
                ['route' => 'admin.items.index', 'match' => ['admin.items.*', 'admin.option-groups.*', 'admin.options.*'], 'icon' => 'utensils', 'label' => 'nav.items'],
                ['route' => 'admin.tables.index', 'match' => ['admin.tables.*'], 'icon' => 'qr', 'label' => 'nav.tables'],
                ['route' => 'admin.reports.index', 'match' => ['admin.reports.*'], 'icon' => 'reports', 'label' => 'nav.reports'],
                ['route' => 'admin.activity.index', 'match' => ['admin.activity.*'], 'icon' => 'shield', 'label' => 'nav.activity'],
                ['route' => 'admin.staff.index', 'match' => ['admin.staff.*'], 'icon' => 'staff', 'label' => 'nav.staff'],
                ['route' => 'admin.settings.edit', 'match' => ['admin.settings.*'], 'icon' => 'settings', 'label' => 'nav.settings'],
            ];
        } elseif ($user?->isCashier()) {
            $links = [
                ['label' => 'nav.operations', 'type' => 'heading'],
                ['route' => 'staff.orders.index', 'match' => ['staff.orders.*'], 'icon' => 'orders', 'label' => 'nav.orders'],
            ];
        } else {
            $links = [
                ['label' => 'nav.operations', 'type' => 'heading'],
                ['route' => 'staff.kitchen.index', 'match' => ['staff.kitchen.*'], 'icon' => 'kitchen', 'label' => 'nav.kitchen'],
            ];
        }

        $home = $user ? \App\Support\RoleHome::for($user) : route('login');
    @endphp

    <div x-data="shell" class="min-h-screen lg:flex" @keydown.escape.window="closeSidebar">
        <div x-show="sidebar" x-cloak @click="closeSidebar" class="fixed inset-0 z-40 bg-charcoal-deep/60 lg:hidden"></div>

        <aside
            class="sidebar-shell fixed inset-y-0 start-0 z-50 flex w-72 flex-col bg-charcoal text-cream transition-transform duration-200"
            :class="sidebarClass"
            aria-label="{{ __('menuqr.nav.main') }}"
        >
            <div class="flex h-20 items-center justify-between border-b border-cream/10 px-5">
                <a href="{{ $home }}" class="rounded-sm">
                    <x-logo class="h-10 w-10" light />
                </a>
                <button type="button" class="btn-ghost btn-sm text-cream/70 hover:bg-charcoal-soft hover:text-cream lg:hidden" @click="closeSidebar" aria-label="{{ __('menuqr.common.close') }}">
                    <x-icon name="x" class="h-5 w-5" />
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                @foreach ($links as $link)
                    @if (($link['type'] ?? null) === 'heading')
                        <p class="px-3 pb-1 pt-4 text-[0.62rem] font-bold uppercase tracking-[0.2em] text-cream/40 first:pt-1">
                            {{ __('menuqr.'.$link['label']) }}
                        </p>
                    @else
                        <a href="{{ route($link['route']) }}"
                           class="nav-link {{ request()->routeIs(...$link['match']) ? 'nav-link-active' : '' }}"
                           @if (request()->routeIs(...$link['match'])) aria-current="page" @endif>
                            <x-icon :name="$link['icon']" class="h-5 w-5 shrink-0" />
                            <span>{{ __('menuqr.'.$link['label']) }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="border-t border-cream/10 p-3">
                <a href="{{ route('home') }}" class="nav-link mb-1">
                    <x-icon name="external" class="h-5 w-5" />
                    <span>{{ __('menuqr.nav.public_menu') }}</span>
                </a>
                <div class="mt-2 rounded-sm bg-charcoal-soft p-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-sm bg-saffron font-display text-sm font-bold text-charcoal">
                            {{ mb_substr($user->name ?? '?', 0, 1) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-cream">{{ $user->name ?? '' }}</p>
                            <p class="text-xs text-cream/50">{{ $user?->roleLabel() }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-end gap-1 border-t border-cream/10 pt-2">
                        <form method="POST" action="{{ route('logout.all') }}">
                            @csrf
                            <button type="submit" class="btn-ghost btn-sm text-cream/60 hover:bg-charcoal hover:text-cream" title="{{ __('menuqr.common.logout_all') }}" aria-label="{{ __('menuqr.common.logout_all') }}">
                                <x-icon name="shield" class="h-4 w-4" />
                            </button>
                        </form>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-ghost btn-sm text-cream/60 hover:bg-charcoal hover:text-cream" title="{{ __('menuqr.common.logout') }}" aria-label="{{ __('menuqr.common.logout') }}">
                                <x-icon name="logout" class="h-4 w-4" />
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <div class="min-w-0 flex-1 lg:ps-72">
            @if ($user?->isDemo())
                <div class="demo-bar" role="status">
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="alert" class="h-3.5 w-3.5" />
                        {{ __('menuqr.demo.banner') }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="font-bold underline decoration-charcoal/40 underline-offset-2">{{ __('menuqr.demo.exit') }}</button>
                    </form>
                </div>
            @endif

            <header class="sticky top-0 z-30 border-b border-line bg-cream/90 backdrop-blur">
                <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                    <button type="button" class="btn-ghost btn-sm lg:hidden" @click="openSidebar" aria-label="{{ __('menuqr.nav.main') }}">
                        <x-icon name="menu" class="h-5 w-5" />
                    </button>
                    <h1 class="truncate font-display text-base font-bold text-charcoal sm:text-lg">@yield('title', __('menuqr.app.admin'))</h1>
                    <div class="ms-auto flex items-center gap-2">
                        <x-lang-switch />
                        @if ($user)
                            <span class="hidden rounded-sm border border-line-strong bg-paper px-2.5 py-1.5 text-xs font-bold text-ink-soft sm:inline-flex">
                                {{ $user->roleLabel() }}
                            </span>
                        @endif
                    </div>
                </div>
            </header>

            <main id="main-content" class="@yield('main_class', 'mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8')">
                @include('partials.flash')
                @yield('content')

                <footer class="mt-10 border-t border-line pt-5 text-xs text-muted">
                    <p>{{ __('menuqr.footer.rights', ['year' => config('menuqr.brand.copyright_year'), 'product' => __('menuqr.brand.name')]) }}</p>
                    <p class="mt-1">
                        <a href="https://fedaamohamed.com" rel="noopener" class="hover:text-charcoal">{{ __('menuqr.footer.credit', ['owner' => __('menuqr.brand.owner')]) }}</a>
                    </p>
                    <p class="mt-1">{{ __('menuqr.brand.name') }} · {{ __('menuqr.brand.descriptor') }}</p>
                </footer>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
