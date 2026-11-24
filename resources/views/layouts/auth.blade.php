{{--
    MenuQR authentication shell — a real printed menu preview, not a marketing hero.
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
    <title>@yield('title', __('menuqr.auth.title')) — {{ __('menuqr.brand.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream">
    <main class="grid min-h-screen lg:grid-cols-[1.08fr_1fr]">
        <section class="relative hidden overflow-hidden bg-sand p-10 lg:flex lg:flex-col lg:justify-between xl:p-14">
            <div class="absolute inset-0 opacity-60" aria-hidden="true"
                 style="background-image: radial-gradient(#23201C 0.55px, transparent 0.55px); background-size: 7px 7px;"></div>

            <a href="{{ route('home') }}" class="relative inline-flex w-fit">
                <x-logo class="h-11 w-11" />
            </a>

            <div class="relative mx-auto w-full max-w-md py-8">
                <div class="menu-paper px-7 pb-16 pt-7">
                    <div class="text-center">
                        <p class="ornament justify-center text-[0.65rem] font-bold uppercase tracking-[0.2em]">{{ __('menuqr.menu.today_menu') }}</p>
                        <h1 class="mt-2 font-display text-2xl font-bold text-charcoal">{{ __('menuqr.home.sample.title') }}</h1>
                        <p class="mt-1 text-xs text-muted">{{ __('menuqr.menu.sample_note') }}</p>
                    </div>

                    <div class="mt-5 space-y-0">
                        @foreach (($previewItems ?? collect())->take(4) as $previewItem)
                            <div class="menu-row">
                                <div class="flex items-baseline gap-2">
                                    <span class="font-display text-sm font-bold text-charcoal">{{ $previewItem->name() }}</span>
                                    <span class="leader-line"></span>
                                    <span class="price text-sm"><x-money :minor="$previewItem->price_minor" /></span>
                                </div>
                                @if ($previewItem->description())
                                    <p class="mt-0.5 max-w-[80%] text-[0.68rem] leading-5 text-muted">{{ $previewItem->description() }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="ticket-cut mt-4 pt-4 text-center">
                        <p class="text-[0.65rem] font-semibold text-muted">{{ __('menuqr.home.scan_hint') }}</p>
                    </div>
                </div>

                <div class="ticket absolute -bottom-10 -start-16 w-56 rotate-[-2deg] p-3">
                    <div class="flex items-center justify-between border-b border-line pb-2">
                        <span class="font-display text-xs font-bold text-charcoal">{{ __('menuqr.order.ticket') }}</span>
                        <span class="tnum text-[0.65rem] font-bold text-tomato">ORD-2026-0002</span>
                    </div>
                    <p class="mt-2 text-[0.7rem] font-bold text-charcoal">{{ __('menuqr.menu.table_label', ['name' => '07']) }}</p>
                    <ul class="mt-1 space-y-1 text-[0.65rem] text-ink-soft">
                        @foreach (array_slice(__('menuqr.home.sample.items'), 0, 2) as $dish)
                            <li class="flex justify-between gap-2"><span>1× {{ $dish['name'] }}</span><span class="tnum">{{ $dish['price'] }}</span></li>
                        @endforeach
                    </ul>
                    <p class="mt-2 border-t border-dashed border-line-strong pt-1.5 text-end text-[0.65rem] font-bold text-charcoal">270.00 {{ __('menuqr.currency.egp') }}</p>
                </div>
            </div>

            <div class="relative text-xs text-muted">
                <p>{{ __('menuqr.footer.rights', ['year' => config('menuqr.brand.copyright_year'), 'product' => __('menuqr.brand.name')]) }}</p>
                <p class="mt-1"><a href="https://fedaamohamed.com" rel="noopener" class="hover:text-charcoal">{{ __('menuqr.footer.credit', ['owner' => __('menuqr.brand.owner')]) }}</a></p>
            </div>
        </section>

        <section class="flex flex-col justify-center bg-cream px-5 py-12 sm:px-10 lg:px-14">
            <div class="mx-auto w-full max-w-md">
                <div class="mb-8 flex items-center justify-between lg:hidden">
                    <a href="{{ route('home') }}"><x-logo class="h-10 w-10" /></a>
                    <x-lang-switch />
                </div>

                <div class="hidden justify-end lg:flex">
                    <x-lang-switch />
                </div>

                <div class="mt-6 lg:mt-0">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-saffron-deep">{{ __('menuqr.app.staff') }}</p>
                    <h2 class="mt-2 font-display text-3xl font-bold text-charcoal">@yield('heading', __('menuqr.auth.title'))</h2>
                    <p class="mt-2 text-sm text-muted">@yield('subheading', __('menuqr.auth.subtitle'))</p>
                </div>

                <div class="mt-8">
                    @include('partials.flash')
                    @yield('content')
                </div>

                <div class="mt-8 flex flex-wrap items-center gap-x-3 gap-y-2 text-xs text-muted">
                    <a href="{{ route('home') }}" class="link">{{ __('menuqr.auth.back_home') }}</a>
                    <span aria-hidden="true">·</span>
                    <span class="inline-flex items-center gap-1.5"><x-icon name="lock" class="h-3.5 w-3.5" />{{ __('menuqr.auth.secure_note') }}</span>
                </div>
                <div class="mt-6 text-[0.68rem] text-muted lg:hidden">
                    <p>{{ __('menuqr.footer.rights', ['year' => config('menuqr.brand.copyright_year'), 'product' => __('menuqr.brand.name')]) }}</p>
                    <p class="mt-1"><a href="https://fedaamohamed.com" rel="noopener" class="hover:text-charcoal">{{ __('menuqr.footer.credit', ['owner' => __('menuqr.brand.owner')]) }}</a></p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
