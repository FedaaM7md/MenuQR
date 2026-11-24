{{--
    MenuQR - index.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', __('menuqr.nav.orders'))

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-charcoal">{{ __('menuqr.nav.orders') }}</h2>
            <p class="mt-1 text-sm text-muted">{{ __('menuqr.order.active_orders') }}: {{ $counts->only(['new', 'preparing', 'ready'])->sum() }}</p>
        </div>
        @if (auth()->user()->can('view-kitchen'))
            <a href="{{ route('staff.kitchen.index') }}" class="btn-primary">
                <x-icon name="kitchen" class="h-4 w-4" />
                {{ __('menuqr.dashboard.open_kitchen') }}
            </a>
        @endif
    </div>

    <div class="mb-5 flex flex-wrap items-center gap-2">
        <a href="{{ route('staff.orders.index', array_filter(['q' => $search ?: null, 'date' => request('date')])) }}"
           class="chip {{ ! $status ? 'chip-active' : '' }}">
            {{ __('menuqr.order.show_all') }}
        </a>
        @foreach (\App\Models\Order::STATUSES as $state)
            <a href="{{ route('staff.orders.index', array_filter(['status' => $state, 'q' => $search ?: null, 'date' => request('date')])) }}"
               class="chip {{ $status === $state ? 'chip-active' : '' }}">
                {{ __('menuqr.statuses.'.$state) }}
                <span class="ms-1.5 rounded-full bg-sand px-1.5 text-[0.65rem] font-bold text-ink-soft">{{ $counts[$state] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div data-sheet class="mb-5">
        <button type="button" class="btn-secondary mb-3 w-full md:hidden" data-sheet-open>
            <x-icon name="search" class="h-4 w-4" />
            {{ __('menuqr.common.filters') }}
        </button>

        <form method="GET" action="{{ route('staff.orders.index') }}" class="card-pad hidden md:block">
            @include('staff.orders.filters', ['prefix' => 'desktop'])
        </form>

        <div data-sheet-panel class="hidden fixed inset-0 z-[70] md:hidden">
            <div class="absolute inset-0 bg-charcoal-deep/60" data-sheet-close></div>
            <div class="absolute inset-x-0 bottom-0 max-h-[88vh] overflow-y-auto rounded-t-lg bg-paper p-5 shadow-2xl">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-display text-lg font-bold text-charcoal">{{ __('menuqr.common.filters') }}</h2>
                    <button type="button" class="btn-ghost btn-sm" data-sheet-close aria-label="{{ __('menuqr.common.close') }}">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>
                <form method="GET" action="{{ route('staff.orders.index') }}" class="mt-4">
                    @include('staff.orders.filters', ['prefix' => 'mobile'])
                </form>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        @if ($orders->isEmpty())
            <div class="px-6 py-14 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-sand text-muted">
                    <x-icon name="orders" class="h-6 w-6" />
                </span>
                <p class="mt-4 text-sm font-semibold text-ink-soft">{{ __('menuqr.order.no_orders') }}</p>
            </div>
        @else
            <div class="divide-y divide-line xl:hidden">
                @foreach ($orders as $order)
                    <article class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <a href="{{ route('staff.orders.show', $order) }}" class="link tnum font-bold" dir="ltr">{{ $order->number }}</a>
                                <p class="mt-1 text-xs text-muted">{{ $order->table?->name ?? '—' }} · {{ $order->typeLabel() }} · <span class="tnum">{{ format_datetime($order->placed_at, 'd/m') }}</span></p>
                            </div>
                            <x-status-badge :status="$order->status" />
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-3">
                            <p class="tnum text-sm font-bold text-charcoal"><x-money :minor="$order->total_minor" /></p>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-muted">{{ $order->items_count }} {{ __('menuqr.order.items') }}</span>
                                <a href="{{ route('staff.orders.show', $order) }}" class="btn-secondary btn-sm">{{ __('menuqr.common.view') }}</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="table-wrap hidden xl:block">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('menuqr.order.number') }}</th>
                            <th>{{ __('menuqr.order.table') }}</th>
                            <th>{{ __('menuqr.order.type') }}</th>
                            <th>{{ __('menuqr.order.items') }}</th>
                            <th>{{ __('menuqr.common.status') }}</th>
                            <th>{{ __('menuqr.order.placed_at') }}</th>
                            <th class="text-end">{{ __('menuqr.order.total') }}</th>
                            <th class="text-end">{{ __('menuqr.common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('staff.orders.show', $order) }}" class="link font-bold" dir="ltr">{{ $order->number }}</a>
                                    @if ($order->customer_name)
                                        <p class="mt-0.5 text-[0.7rem] text-muted">{{ $order->customer_name }}</p>
                                    @endif
                                </td>
                                <td class="font-semibold">{{ $order->table?->name ?? '—' }}</td>
                                <td>{{ $order->typeLabel() }}</td>
                                <td>{{ $order->items_count }}</td>
                                <td><x-status-badge :status="$order->status" /></td>
                                <td class="whitespace-nowrap text-muted">{{ format_datetime($order->placed_at, 'd/m') }}</td>
                                <td class="text-end font-bold"><x-money :minor="$order->total_minor" /></td>
                                <td class="text-end">
                                    <a href="{{ route('staff.orders.show', $order) }}" class="btn-secondary btn-sm">
                                        {{ __('menuqr.common.view') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="border-t border-line px-5 py-4">{{ $orders->links() }}</div>
            @endif
        @endif
    </div>
@endsection
