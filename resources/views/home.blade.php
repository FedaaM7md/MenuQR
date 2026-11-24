{{--
    MenuQR public landing — the product UI itself is the hero.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.public')

@section('title', __('menuqr.app.tagline'))
@section('meta_description', __('menuqr.home.subheadline'))

@section('content')
    @php
        $sample = __('menuqr.home.sample.items');
        $previewRows = $featured->isNotEmpty()
            ? $featured->take(5)->map(fn ($item) => [
                'name' => $item->name(),
                'desc' => $item->description(),
                'minor' => $item->price_minor,
                'featured' => $item->is_featured,
            ])
            : collect($sample)->take(4)->map(fn ($dish) => [
                'name' => $dish['name'],
                'desc' => $dish['desc'],
                'minor' => \App\Support\Money::toMinor($dish['price']),
                'featured' => false,
            ]);
        $demoEnabled = \App\Support\DemoMode::enabled();
    @endphp

    <section class="relative overflow-hidden border-b border-line bg-cream">
        <div class="absolute inset-0 opacity-50" aria-hidden="true"
             style="background-image: radial-gradient(#23201C 0.5px, transparent 0.5px); background-size: 8px 8px;"></div>

        <div class="container-page relative grid gap-12 py-14 sm:py-20 lg:grid-cols-[1.02fr_0.98fr] lg:items-center lg:gap-16 lg:py-24">
            <div class="max-w-xl">
                <p class="ornament text-xs font-bold uppercase tracking-[0.18em]">{{ __('menuqr.home.eyebrow') }}</p>

                <h1 class="mt-5 font-display text-4xl font-bold leading-[1.12] text-charcoal sm:text-5xl lg:text-6xl">
                    {{ __('menuqr.home.headline') }}
                </h1>

                <p class="mt-6 max-w-lg text-base leading-8 text-ink-soft sm:text-lg">
                    {{ __('menuqr.home.subheadline') }}
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    @if ($demoEnabled)
                        <a href="#demo" class="btn-accent px-5 py-3">
                            {{ __('menuqr.home.cta_demo') }}
                            <x-icon name="arrow" class="h-4 w-4 rtl:rotate-180" />
                        </a>
                    @endif
                    <a href="/#menu-preview" class="btn-secondary px-5 py-3">
                        <x-icon name="book" class="h-4 w-4" />
                        {{ __('menuqr.home.cta_menu') }}
                    </a>
                </div>

                <p class="mt-6 flex items-start gap-2 text-sm leading-6 text-muted">
                    <x-icon name="qr" class="mt-0.5 h-4 w-4 shrink-0 text-saffron-deep" />
                    {{ __('menuqr.home.scan_hint') }}
                </p>
            </div>

            <div id="menu-preview" class="relative mx-auto w-full max-w-md scroll-mt-24">
                <div class="menu-paper relative z-10 px-6 py-7 sm:px-8">
                    <div class="text-center">
                        <p class="text-[0.62rem] font-bold uppercase tracking-[0.24em] text-saffron-deep">{{ __('menuqr.menu.sample_note') }}</p>
                        <h2 class="mt-1 font-display text-2xl font-bold text-charcoal">{{ __('menuqr.menu.today_menu') }}</h2>
                        <p class="mt-1 text-xs text-muted">{{ __('menuqr.home.preview.table') }} · {{ __('menuqr.menu.table_note') }}</p>
                    </div>

                    <div class="mt-4">
                        @foreach ($previewRows as $row)
                            <div class="menu-row">
                                <div class="flex items-baseline gap-2">
                                    <span class="font-display text-[0.95rem] font-bold text-charcoal">{{ $row['name'] }}</span>
                                    @if ($row['featured'])
                                        <span class="rounded-sm bg-saffron-soft px-1 text-[0.55rem] font-bold text-charcoal">{{ __('menuqr.menu.featured') }}</span>
                                    @endif
                                    <span class="leader-line"></span>
                                    <span class="price text-sm"><x-money :minor="$row['minor']" /></span>
                                </div>
                                @if ($row['desc'])
                                    <p class="mt-0.5 max-w-[85%] text-[0.72rem] leading-5 text-muted">{{ $row['desc'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 flex items-center justify-between border-t border-line pt-4">
                        <span class="text-[0.65rem] font-semibold text-muted">{{ __('menuqr.menu.sample_note') }}</span>
                        <span class="btn-primary btn-sm pointer-events-none">{{ __('menuqr.menu.order_here') }}</span>
                    </div>
                </div>

                <div class="ticket absolute -bottom-10 -start-4 z-20 hidden w-52 rotate-[2deg] p-3 sm:block">
                    <div class="flex items-center justify-between border-b border-line pb-1.5">
                        <span class="font-display text-[0.7rem] font-bold text-charcoal">{{ __('menuqr.order.ticket') }}</span>
                        <span class="tnum text-[0.62rem] font-bold text-tomato">ORD-2026-0002</span>
                    </div>
                    <p class="mt-1.5 text-[0.62rem] font-bold text-muted">{{ __('menuqr.home.preview.table') }}</p>
                    <ul class="mt-1 space-y-0.5 text-[0.62rem] text-ink-soft">
                        @foreach ($previewRows->take(2) as $row)
                            <li class="flex justify-between gap-2"><span>1× {{ $row['name'] }}</span><span class="tnum">{{ number_format($row['minor'] / 100, 2) }}</span></li>
                        @endforeach
                    </ul>
                    <p class="mt-1.5 border-t border-dashed border-line-strong pt-1 text-end text-[0.62rem] font-bold text-charcoal">{{ __('menuqr.home.preview.order') }} · {{ __('menuqr.home.preview.flow') }}</p>
                </div>

                <div class="absolute -top-5 -end-3 z-20 hidden rounded-sm bg-charcoal px-3 py-2 text-[0.62rem] font-bold text-cream shadow-lg sm:block">
                    <span class="inline-flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-saffron"></span>{{ __('menuqr.home.preview.live') }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="border-b border-line bg-sand">
        <div class="container-page grid gap-4 py-4 text-xs font-semibold text-ink-soft sm:grid-cols-3 sm:divide-x sm:divide-line">
            <p class="flex items-center gap-2 sm:justify-center"><x-icon name="lock" class="h-3.5 w-3.5 text-saffron-deep" />{{ __('menuqr.home.fact_two') }}</p>
            <p class="flex items-center gap-2 sm:justify-center"><x-icon name="reports" class="h-3.5 w-3.5 text-saffron-deep" />{{ __('menuqr.home.fact_one') }}</p>
            <p class="flex items-center gap-2 sm:justify-center"><x-icon name="refresh" class="h-3.5 w-3.5 text-saffron-deep" />{{ __('menuqr.home.fact_three') }}</p>
        </div>
    </section>

    <section class="container-page py-16 sm:py-20">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
            <div class="lg:sticky lg:top-24 lg:self-start">
                <p class="ornament text-xs font-bold uppercase tracking-[0.18em]">{{ __('menuqr.home.features_title') }}</p>
                <h2 class="mt-4 font-display text-3xl font-bold leading-tight text-charcoal sm:text-4xl">
                    {{ __('menuqr.home.admin_title') }}
                </h2>
                <p class="mt-4 max-w-sm text-sm leading-7 text-muted">{{ __('menuqr.home.admin_body') }}</p>
                <a href="{{ route('login') }}" class="link mt-6 inline-flex text-sm">{{ __('menuqr.home.cta_staff') }} <span aria-hidden="true" class="ms-1 rtl:rotate-180">→</span></a>
            </div>

            <div class="divide-y divide-line border-y border-line">
                @foreach ([
                    ['menu', 'utensils'],
                    ['qr', 'qr'],
                    ['orders', 'cart'],
                    ['kitchen', 'receipt'],
                    ['reports', 'reports'],
                    ['pwa', 'download'],
                ] as $index => [$key, $icon])
                    <article class="flex gap-4 py-5 sm:gap-6">
                        <span class="tnum w-8 shrink-0 pt-1 font-display text-sm font-bold text-saffron-deep">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="min-w-0 flex-1">
                            <h3 class="flex items-center gap-2 font-display text-lg font-bold text-charcoal">
                                <x-icon :name="$icon" class="h-4 w-4 text-muted" />
                                {{ __("menuqr.home.features.$key.title") }}
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-ink-soft">{{ __("menuqr.home.features.$key.body") }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-charcoal text-cream">
        <div class="container-page grid gap-10 py-16 sm:py-20 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-saffron">{{ __('menuqr.home.preview.ticket') }}</p>
                <h2 class="mt-4 font-display text-3xl font-bold leading-tight text-cream sm:text-4xl">{{ __('menuqr.home.kitchen_title') }}</h2>
                <p class="mt-4 max-w-lg text-sm leading-7 text-cream/70">{{ __('menuqr.home.kitchen_body') }}</p>
                <p class="mt-4 max-w-lg text-xs leading-6 text-cream/50">{{ __('menuqr.home.preview.kitchen_note') }}</p>
            </div>

            <div class="ticket-kitchen relative -rotate-1 p-5 sm:p-6">
                <div class="flex items-start justify-between gap-3 border-b border-line pb-3">
                    <div>
                        <p class="tnum font-display text-xl font-bold text-charcoal">ORD-2026-0032</p>
                        <p class="mt-0.5 text-[0.7rem] font-semibold text-muted">{{ __('menuqr.menu.table_label', ['name' => '07']) }} · {{ __('menuqr.order_types.dine_in') }}</p>
                    </div>
                    <span class="rounded-sm bg-saffron px-2 py-1 text-[0.62rem] font-bold text-charcoal">{{ __('menuqr.statuses.new') }}</span>
                </div>
                <ul class="mt-3 space-y-2">
                    @foreach (array_slice(__('menuqr.home.sample.items'), 0, 3) as $dish)
                        <li class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-bold text-charcoal">1× {{ $dish['name'] }}</p>
                                <p class="text-[0.65rem] text-muted">{{ $dish['desc'] }}</p>
                            </div>
                            <span class="tnum text-xs font-bold text-charcoal">{{ $dish['price'] }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 flex items-center justify-between border-t border-dashed border-line-strong pt-3">
                    <span class="text-[0.68rem] font-semibold text-muted">{{ __('menuqr.home.preview.admin_note') }}</span>
                    <span class="tnum font-display text-lg font-bold text-charcoal">270.00</span>
                </div>
            </div>
        </div>
    </section>

    <section class="container-page py-16 sm:py-20">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
            <div class="menu-paper order-2 p-6 lg:order-1">
                <div class="flex items-center gap-4">
                    <div class="rounded-sm bg-white p-2 ring-1 ring-line">
                        <svg viewBox="0 0 100 100" class="h-24 w-24" aria-hidden="true">
                            <rect width="100" height="100" fill="#fff"/>
                            @foreach ([[10,10],[66,10],[10,66]] as [$x,$y])
                                <rect x="{{ $x }}" y="{{ $y }}" width="24" height="24" fill="#23201C"/>
                                <rect x="{{ $x + 5 }}" y="{{ $y + 5 }}" width="14" height="14" fill="#fff"/>
                                <rect x="{{ $x + 9 }}" y="{{ $y + 9 }}" width="6" height="6" fill="#E3A72F"/>
                            @endforeach
                            <rect x="42" y="42" width="8" height="8" fill="#6B7A3A"/>
                            <rect x="54" y="42" width="8" height="8" fill="#23201C"/>
                            <rect x="42" y="54" width="8" height="8" fill="#23201C"/>
                            <rect x="66" y="54" width="8" height="8" fill="#6B7A3A"/>
                            <rect x="78" y="66" width="8" height="8" fill="#23201C"/>
                            <rect x="54" y="78" width="8" height="8" fill="#6B7A3A"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[0.62rem] font-bold uppercase tracking-[0.2em] text-saffron-deep">{{ __('menuqr.menu.table_label', ['name' => '07']) }}</p>
                        <p class="mt-1 font-display text-lg font-bold text-charcoal">{{ __('menuqr.menu.scan_table') }}</p>
                        <p class="mt-1 text-xs text-muted">T1 · 2 {{ __('menuqr.tables.seats') }}</p>
                    </div>
                </div>
                <div class="mt-5 border-t border-dashed border-line-strong pt-4">
                    <p class="text-xs leading-6 text-muted">{{ __('menuqr.tables.print_note') }}</p>
                </div>
            </div>

            <div class="order-1 lg:order-2">
                <p class="ornament text-xs font-bold uppercase tracking-[0.18em]">{{ __('menuqr.home.preview.bilingual') }}</p>
                <h2 class="mt-4 font-display text-3xl font-bold leading-tight text-charcoal sm:text-4xl">{{ __('menuqr.home.qr_title') }}</h2>
                <p class="mt-4 max-w-lg text-sm leading-7 text-muted">{{ __('menuqr.home.qr_body') }}</p>
                <ul class="mt-6 space-y-2 text-sm text-ink-soft">
                    <li class="flex items-center gap-2"><x-icon name="check" class="h-4 w-4 text-olive" />{{ __('menuqr.home.no_login') }}</li>
                    <li class="flex items-center gap-2"><x-icon name="check" class="h-4 w-4 text-olive" />{{ __('menuqr.home.one_screen') }}</li>
                    <li class="flex items-center gap-2"><x-icon name="check" class="h-4 w-4 text-olive" />{{ __('menuqr.home.egp') }}</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="border-y border-line bg-paper">
        <div class="container-page py-16 sm:py-20">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="ornament text-xs font-bold uppercase tracking-[0.18em]">{{ __('menuqr.home.how_title') }}</p>
                    <h2 class="mt-4 font-display text-3xl font-bold text-charcoal">{{ __('menuqr.home.how_title') }}</h2>
                </div>
                <p class="text-sm text-muted">{{ __('menuqr.home.eyebrow') }}</p>
            </div>

            <ol class="mt-10 grid gap-8 md:grid-cols-3 md:gap-0">
                @foreach (['one', 'two', 'three'] as $index => $step)
                    <li class="relative md:px-7 {{ $index > 0 ? 'md:border-s md:border-line' : '' }}">
                        <span class="tnum font-display text-5xl font-bold text-saffron-soft">{{ $index + 1 }}</span>
                        <h3 class="mt-2 font-display text-xl font-bold text-charcoal">{{ __("menuqr.home.how.$step.title") }}</h3>
                        <p class="mt-2 max-w-xs text-sm leading-6 text-muted">{{ __("menuqr.home.how.$step.body") }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    @if ($demoEnabled)
        <section id="demo" class="bg-saffron">
            <div class="container-page flex flex-col gap-6 py-10 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-charcoal/70">{{ __('menuqr.demo.shared') }}</p>
                    <h2 class="mt-2 font-display text-2xl font-bold text-charcoal sm:text-3xl">{{ __('menuqr.demo.try') }}</h2>
                    <p class="mt-2 text-sm leading-6 text-charcoal/80">{{ __('menuqr.home.demo_strip') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach (['admin', 'cashier', 'kitchen'] as $role)
                        <form method="POST" action="{{ route('demo.login') }}">
                            @csrf
                            <input type="hidden" name="role" value="{{ $role }}">
                            <button type="submit" class="btn-primary px-4 py-3 text-sm">
                                {{ __('menuqr.demo.role_'.$role) }}
                                <span class="text-[0.62rem] font-semibold text-cream/60">{{ __('menuqr.roles.'.$role) }}</span>
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
