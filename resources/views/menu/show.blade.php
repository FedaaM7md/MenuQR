{{--
    MenuQR public printed-menu page.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.public')

@section('title', __('menuqr.menu.menu_for', ['name' => $settings->name()]))
@section('meta_description', $settings->tagline() ?: __('menuqr.app.description'))

@section('content')
    <section class="border-b border-line bg-sand">
        <div class="container-menu py-8 sm:py-12">
            <div class="text-center">
                <p class="ornament justify-center text-[0.65rem] font-bold uppercase tracking-[0.24em]">{{ __('menuqr.menu.today_menu') }}</p>
                <h1 class="mt-3 font-display text-3xl font-bold leading-tight text-charcoal sm:text-4xl">{{ $settings->name() }}</h1>
                @if ($settings->tagline())
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-muted">{{ $settings->tagline() }}</p>
                @endif
                <div class="mt-4 flex flex-wrap items-center justify-center gap-2 text-xs font-bold">
                    <span class="rounded-sm bg-charcoal px-2.5 py-1 text-cream">{{ __('menuqr.menu.table_label', ['name' => $table->name]) }}</span>
                    <span class="rounded-sm border px-2.5 py-1 {{ $isOpen ? 'border-olive/30 bg-olive-soft text-olive-deep' : 'border-tomato/25 bg-tomato-soft text-tomato-deep' }}">
                        {{ $isOpen ? __('menuqr.menu.open_now') : __('menuqr.menu.closed_now') }}
                    </span>
                </div>
            </div>

            <form method="GET" action="{{ route('menu.show', $table->token) }}" role="search" class="no-print mx-auto mt-6 max-w-md">
                <label for="menu-search" class="sr-only">{{ __('menuqr.common.search') }}</label>
                <div class="flex items-center gap-2 rounded-sm border border-line-strong bg-paper p-1.5">
                    <x-icon name="search" class="ms-2 h-4 w-4 shrink-0 text-muted" />
                    <input id="menu-search" type="search" name="q" value="{{ $search }}" maxlength="60"
                           class="min-w-0 flex-1 border-0 bg-transparent px-1 py-1.5 text-sm text-charcoal placeholder:text-muted focus:outline-none"
                           placeholder="{{ __('menuqr.menu.search_placeholder') }}" data-search-input>
                    <button type="submit" class="btn-primary btn-sm shrink-0 px-3">
                        {{ __('menuqr.common.search') }}
                    </button>
                </div>
            </form>
        </div>
    </section>

    @if (! $settings->is_accepting_orders)
        <div class="border-b border-saffron/50 bg-saffron-50">
            <div class="container-menu flex items-center gap-2 py-3 text-sm font-bold text-charcoal">
                <x-icon name="alert" class="h-4 w-4 shrink-0 text-tomato" />
                {{ __('menuqr.menu.not_accepting') }}
            </div>
        </div>
    @elseif (! $isOpen)
        <div class="border-b border-line bg-paper">
            <div class="container-menu flex items-center gap-2 py-3 text-sm font-semibold text-muted">
                <x-icon name="clock" class="h-4 w-4 shrink-0" />
                {{ __('menuqr.menu.closed_notice') }}
            </div>
        </div>
    @endif

    <div class="no-print sticky top-16 z-30 border-b border-line bg-cream/95 backdrop-blur sm:top-20" x-data="menuNav">
        <div class="container-page relative">
            <div class="scroll-fade pointer-events-none absolute inset-y-0 start-0 z-10 w-12" aria-hidden="true"></div>
            <div class="scroll-fade-end pointer-events-none absolute inset-y-0 end-0 z-10 w-12" aria-hidden="true"></div>
            <div x-ref="scroller" class="flex items-center gap-1.5 overflow-x-auto py-2.5">
                <a href="#top" data-nav="top" @click.prevent="go('top')" class="chip" x-bind:class="chipClass('top')">{{ __('menuqr.menu.all_categories') }}</a>
                @foreach ($categories as $category)
                    <a href="#category-{{ $category->id }}" data-nav="category-{{ $category->id }}" @click.prevent="go('category-{{ $category->id }}')"
                       class="chip" x-bind:class="chipClass('category-{{ $category->id }}')">{{ $category->name() }}</a>
                @endforeach
            </div>
        </div>
    </div>

    <div id="top" data-menu-section="top" class="container-menu scroll-mt-36 py-8 {{ $cartCount > 0 ? 'pb-28' : '' }}">
        @if ($search !== '')
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm font-semibold text-muted">
                    {{ __('menuqr.common.search') }}: <span class="text-charcoal">“{{ $search }}”</span>
                </p>
                <a href="{{ route('menu.show', $table->token) }}" class="link text-sm">{{ __('menuqr.common.reset') }}</a>
            </div>
        @endif

        @if ($categories->isEmpty())
            <div class="menu-paper px-6 py-14 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-sm bg-sand text-muted">
                    <x-icon name="utensils" class="h-6 w-6" />
                </span>
                <p class="mt-4 font-display text-lg font-bold text-charcoal">
                    {{ $search !== '' ? __('menuqr.menu.no_results') : __('menuqr.menu.empty_menu') }}
                </p>
                <p class="mt-2 text-sm text-muted">{{ __('menuqr.menu.footer_note') }}</p>
                @if ($search !== '')
                    <a href="{{ route('menu.show', $table->token) }}" class="btn-secondary mt-5">{{ __('menuqr.common.reset') }}</a>
                @endif
            </div>
        @endif

        <div class="space-y-3">
            @foreach ($categories as $category)
                <section id="category-{{ $category->id }}" data-menu-section="category-{{ $category->id }}" class="menu-paper scroll-mt-36 px-5 py-6 sm:px-7">
                    <div class="menu-section-head">
                        <span>{{ $category->name() }}</span>
                    </div>
                    <p class="mt-1 text-center text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-muted">
                        {{ trans_choice('menuqr.menu.items_count', $category->items->count(), ['count' => $category->items->count()]) }}
                    </p>

                    <div class="mt-3">
                        @foreach ($category->items as $item)
                            <x-menu-item :item="$item" :table="$table" />
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <footer class="mt-8 border-t border-line pt-6">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="max-w-sm">
                    <h2 class="font-display text-sm font-bold text-charcoal">{{ __('menuqr.menu.hours') }}</h2>
                    <div class="mt-2 grid grid-cols-2 gap-x-6 gap-y-1 text-xs text-muted">
                        @foreach (__('menuqr.days') as $key => $label)
                            @php($day = $settings->hours[$key] ?? null)
                            <span class="flex items-center justify-between gap-2">
                                <span>{{ $label }}</span>
                                <span class="tnum font-semibold text-ink-soft">
                                    {{ ($day['closed'] ?? true) ? __('menuqr.settings.closed') : (($day['open'] ?? '').'–'.($day['close'] ?? '')) }}
                                </span>
                            </span>
                        @endforeach
                    </div>
                </div>
                <div class="text-xs text-muted">
                    <p class="font-semibold text-ink-soft">{{ $settings->name() }}</p>
                    @if ($settings->address())
                        <p class="mt-1">{{ $settings->address() }}</p>
                    @endif
                    @if ($settings->whatsapp_number)
                        <p class="mt-1 tnum" dir="ltr">{{ $settings->whatsapp_number }}</p>
                    @endif
                    <p class="mt-3 max-w-xs leading-5">{{ __('menuqr.menu.allergens_note') }}</p>
                </div>
            </div>
        </footer>
    </div>

    @if ($cartCount > 0)
        <div class="no-print fixed inset-x-0 bottom-0 z-40 border-t border-charcoal-soft bg-charcoal p-3 shadow-[0_-14px_30px_-20px_rgba(35,32,28,0.7)]">
            <div class="container-menu">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 text-cream">
                        <p class="text-xs font-semibold text-cream/60">{{ __('menuqr.menu.view_cart') }} · {{ $cartCount }} {{ __('menuqr.cart.items_count') }}</p>
                        <p class="tnum font-display text-lg font-bold text-cream"><x-money :minor="$cartTotal" /></p>
                    </div>
                    <a href="{{ route('menu.cart', $table->token) }}" class="btn-accent shrink-0 py-3">
                        <x-icon name="cart" class="h-5 w-5" />
                        <span>{{ __('menuqr.menu.view_cart') }}</span>
                    </a>
                </div>
            </div>
        </div>
    @endif
@endsection
