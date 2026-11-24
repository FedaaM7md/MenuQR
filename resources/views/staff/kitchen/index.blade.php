{{--
    MenuQR kitchen display — high-contrast tickets read from a distance.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', __('menuqr.nav.kitchen'))
@section('main_class', 'kitchen-shell min-h-[calc(100vh-4rem)] px-4 py-6 sm:px-6 lg:px-8')

@section('content')
    <div x-data="kitchenBoard" data-feed-url="{{ route('staff.kitchen.feed') }}" data-locale="{{ app()->getLocale() }}" data-today-label="{{ __('menuqr.common.today') }}" data-loading-label="{{ __('menuqr.common.loading') }}" class="text-cream">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="kitchen-title">{{ __('menuqr.app.staff') }}</p>
                <h2 class="mt-1 font-display text-2xl font-bold text-cream">{{ __('menuqr.kitchen.board') }}</h2>
                <p class="mt-1 text-sm text-cream/55">{{ __('menuqr.order.active_orders') }}: <span class="tnum font-bold text-saffron" x-text="orders.length">0</span></p>
            </div>
            <div class="flex items-center gap-2">
                <span class="hidden text-xs text-cream/45 sm:inline" x-text="lastUpdatedLabel"></span>
                <button type="button" class="btn-secondary btn-sm border-cream/25 bg-charcoal-soft text-cream hover:border-saffron hover:bg-charcoal hover:text-cream" @click="refresh">
                    <x-icon name="refresh" class="h-4 w-4" />
                    {{ __('menuqr.common.refresh') }}
                </button>
                @if (auth()->user()->can('view-orders'))
                    <a href="{{ route('staff.orders.index') }}" class="btn-accent btn-sm">
                        <x-icon name="orders" class="h-4 w-4" />
                        {{ __('menuqr.nav.orders') }}
                    </a>
                @endif
            </div>
        </div>

        <div x-show="error" x-cloak class="mb-4 flex items-center gap-2 rounded-sm border border-tomato/50 bg-tomato/15 px-4 py-3 text-sm font-semibold text-cream" role="alert">
            <x-icon name="alert" class="h-4 w-4 shrink-0 text-tomato" />
            <span>{{ __('menuqr.pwa.offline_body') }}</span>
        </div>

        <div class="grid items-start gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ([
                ['new', 'bg-saffron', 'border-t-saffron'],
                ['preparing', 'bg-olive', 'border-t-olive'],
                ['ready', 'bg-cream', 'border-t-cream'],
            ] as [$state, $dot, $topBorder])
                <section class="kitchen-column">
                    <header class="mb-3 flex items-center justify-between gap-2 px-1.5">
                        <h3 class="kitchen-title flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full {{ $dot }}"></span>
                            {{ __('menuqr.statuses.'.$state) }}
                        </h3>
                        <span class="tnum inline-flex min-w-7 items-center justify-center rounded-sm bg-cream/10 px-2 py-0.5 text-sm font-bold text-cream" x-text="counts['{{ $state }}']">0</span>
                    </header>

                    <div class="space-y-3">
                        <template x-for="order in filtered('{{ $state }}')" :key="order.id">
                            <article class="ticket-kitchen border-t-4 {{ $topBorder }}">
                                <div class="flex items-start justify-between gap-2 border-b border-line px-4 py-3">
                                    <div class="min-w-0">
                                        <p class="tnum whitespace-nowrap font-display text-2xl font-bold leading-none text-charcoal" dir="ltr" x-text="order.number"></p>
                                        <p class="mt-1 text-sm font-bold text-ink-soft">
                                            <span x-text="order.table"></span> · <span x-text="order.type"></span>
                                        </p>
                                    </div>
                                    <div class="shrink-0 text-end">
                                        <span class="tnum inline-flex items-center gap-1 rounded-sm bg-sand px-2 py-1 text-sm font-bold text-charcoal">
                                            <x-icon name="clock" class="h-3.5 w-3.5" />
                                            <span x-text="order.minutes_waiting"></span> {{ __('menuqr.common.minutes') }}
                                        </span>
                                    </div>
                                </div>

                                <ul class="divide-y divide-line/70 px-4 py-2">
                                    <template x-for="(item, index) in order.items" :key="index">
                                        <li class="py-2">
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="min-w-0 text-base font-bold text-charcoal">
                                                    <span class="tnum me-1 text-saffron-deep" x-text="item.quantity + '×'"></span>
                                                    <span x-text="item.name"></span>
                                                </p>
                                            </div>
                                            <template x-if="item.options.length > 0">
                                                <ul class="mt-1 ms-6 space-y-0.5">
                                                    <template x-for="(option, optionIndex) in item.options" :key="optionIndex">
                                                        <li class="text-xs text-muted">
                                                            <span class="font-bold" x-text="option.group + ':'"></span>
                                                            <span x-text="option.name"></span>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </template>
                                            <template x-if="item.notes">
                                                <p class="ms-6 mt-1 rounded-sm bg-saffron-soft px-2 py-1 text-xs font-bold text-charcoal" x-text="item.notes"></p>
                                            </template>
                                        </li>
                                    </template>
                                </ul>

                                <template x-if="order.notes">
                                    <div class="mx-4 mb-2 rounded-sm border border-saffron/50 bg-saffron-50 px-3 py-2 text-sm font-semibold text-charcoal">
                                        <span class="font-bold">{{ __('menuqr.order.notes') }}:</span>
                                        <span x-text="order.notes"></span>
                                    </div>
                                </template>

                                <div class="flex flex-wrap gap-2 border-t border-line bg-sand/70 px-4 py-3">
                                    <template x-for="transition in order.transitions" :key="transition.value">
                                        <form method="POST" :action="'{{ url('/staff/orders') }}/' + order.id + '/status'" @submit.prevent="updateStatus($event)">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" :value="transition.value">
                                            <button type="submit" class="btn-primary btn-sm" :disabled="busy">
                                                <x-icon name="check" class="h-3.5 w-3.5" />
                                                <span x-text="transition.label"></span>
                                            </button>
                                        </form>
                                    </template>
                                    <span class="tnum ms-auto self-center text-sm font-bold text-ink-soft" x-text="order.total"></span>
                                </div>
                            </article>
                        </template>

                        <div class="rounded-sm border border-dashed border-cream/15 px-4 py-8 text-center text-xs font-semibold text-cream/40"
                             x-show="!loading && filtered('{{ $state }}').length === 0">
                            {{ __('menuqr.dashboard.no_active') }}
                        </div>

                        <div class="space-y-2" x-show="loading">
                            <div class="h-24 animate-pulse rounded-sm bg-cream/5"></div>
                            <div class="h-24 animate-pulse rounded-sm bg-cream/5"></div>
                        </div>
                    </div>
                </section>
            @endforeach
        </div>

        <noscript>
            <div class="mt-6 space-y-3">
                <p class="rounded-sm border border-tomato/50 bg-tomato/15 px-4 py-3 text-sm text-cream">{{ __('menuqr.common.loading') }}</p>
                @foreach ($orders as $order)
                    <div class="rounded-sm border border-cream/15 bg-cream p-4 text-charcoal">
                        <div class="flex items-center justify-between">
                            <span class="tnum font-display font-bold" dir="ltr">{{ $order->number }}</span>
                            <x-status-badge :status="$order->status" />
                        </div>
                        <p class="mt-1 text-sm text-muted">{{ $order->table?->name }} · {{ $order->typeLabel() }} · {{ format_time($order->placed_at) }}</p>
                        <ul class="mt-2 text-sm">
                            @foreach ($order->items as $item)
                                <li>{{ $item->quantity }}× {{ $item->name() }}</li>
                            @endforeach
                        </ul>
                        @foreach ($workflow->allowedFor($order, auth()->user()) as $next)
                            <form method="POST" action="{{ route('staff.orders.status', $order) }}" class="mt-2 inline-block">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $next }}">
                                <button type="submit" class="btn-primary btn-sm">{{ __('menuqr.order.move_to', ['status' => __('menuqr.statuses.'.$next)]) }}</button>
                            </form>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </noscript>
    </div>
@endsection
