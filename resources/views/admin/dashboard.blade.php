{{--
    MenuQR admin dashboard — a working ledger, not a marketing dashboard.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', __('menuqr.dashboard.title'))

@section('content')
    @php
        $today = collect($summary['by_day'])->keyBy('day');
        $locale = app()->getLocale();
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[0.62rem] font-bold uppercase tracking-[0.18em] text-saffron-deep">{{ __('menuqr.app.admin') }}</p>
            <h2 class="mt-1 font-display text-2xl font-bold text-charcoal sm:text-3xl">{{ __('menuqr.dashboard.welcome', ['name' => auth()->user()->name]) }}</h2>
            <p class="mt-1 text-sm text-muted">{{ __('menuqr.dashboard.subtitle') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('staff.orders.index') }}" class="btn-secondary btn-sm">
                <x-icon name="orders" class="h-4 w-4" />
                {{ __('menuqr.nav.orders') }}
            </a>
            <a href="{{ route('staff.kitchen.index') }}" class="btn-primary btn-sm">
                <x-icon name="kitchen" class="h-4 w-4" />
                {{ __('menuqr.dashboard.open_kitchen') }}
            </a>
        </div>
    </div>


    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card border-s-4 border-s-charcoal">
            <span class="stat-label">{{ __('menuqr.dashboard.revenue_today') }}</span>
            <p class="stat-value"><x-money :minor="$stats['revenue_today']" /></p>
            <p class="hint">{{ __('menuqr.dashboard.avg_today') }}: <x-money :minor="$stats['average_today']" /></p>
        </div>
        <div class="stat-card border-s-4 border-s-saffron">
            <span class="stat-label">{{ __('menuqr.dashboard.orders_today') }}</span>
            <p class="stat-value">{{ $stats['orders_today'] }}</p>
            <p class="hint">{{ now()->translatedFormat('l') }}</p>
        </div>
        <div class="stat-card border-s-4 border-s-olive">
            <span class="stat-label">{{ __('menuqr.dashboard.active_orders') }}</span>
            <p class="stat-value">{{ $stats['active_orders'] }}</p>
            <p class="hint">{{ __('menuqr.dashboard.recent_active') }}</p>
        </div>
        <div class="stat-card border-s-4 border-s-tomato">
            <span class="stat-label">{{ __('menuqr.dashboard.week_revenue') }}</span>
            <p class="stat-value"><x-money :minor="$summary['totals']['revenue_minor']" /></p>
            <p class="hint">{{ $summary['totals']['orders'] }} {{ __('menuqr.reports.orders') }}</p>
        </div>
    </div>

    @if ($summary['totals']['orders'] === 0)
        <div class="mt-5 flex flex-col gap-4 rounded-sm border border-line bg-paper p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-display font-bold text-charcoal">{{ __('menuqr.dashboard.setup') }}</p>
                <p class="mt-1 text-sm text-muted">{{ __('menuqr.dashboard.setup_hint') }}</p>
            </div>
            <a href="{{ route('admin.items.create') }}" class="btn-primary shrink-0">{{ __('menuqr.dashboard.add_item') }}</a>
        </div>
    @endif

    <div class="mt-5 grid gap-5 xl:grid-cols-[1.55fr_1fr]">
        <section class="card-pad">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="section-title">{{ __('menuqr.dashboard.week_revenue') }}</h2>
                    <p class="mt-1 text-xs text-muted">{{ __('menuqr.dashboard.chart_hint') }}</p>
                </div>
                <a href="{{ route('admin.reports.index') }}" class="link text-xs">{{ __('menuqr.dashboard.view_reports') }}</a>
            </div>

            <div class="mt-8 flex h-48 items-end gap-2 sm:gap-4" role="img" aria-label="{{ __('menuqr.dashboard.chart_hint') }}">
                @for ($i = 6; $i >= 0; $i--)
                    @php
                        $date = now()->subDays($i)->format('Y-m-d');
                        $revenue = (int) (optional($today->get($date))->revenue_minor ?? 0);
                        $height = $revenue > 0 ? max(6, (int) round($revenue / $maxDay * 100)) : 3;
                    @endphp
                    <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2">
                        <span class="tnum text-[0.6rem] font-bold text-muted">{{ $revenue > 0 ? number_format($revenue / 100, 0) : '' }}</span>
                        <div class="w-full rounded-t-sm {{ $i === 0 ? 'bg-saffron' : 'bg-charcoal/85' }}" style="height: {{ $height }}%; min-height: 2px;"></div>
                        <span class="tnum text-[0.62rem] font-semibold text-muted">{{ now()->subDays($i)->format('d/m') }}</span>
                    </div>
                @endfor
            </div>
        </section>

        <section class="card-pad">
            <h2 class="section-title">{{ __('menuqr.dashboard.top_items') }}</h2>
            @if ($summary['by_item']->isEmpty())
                <p class="mt-6 text-sm text-muted">{{ __('menuqr.reports.no_data') }}</p>
            @else
                <ol class="mt-4 divide-y divide-line">
                    @foreach ($summary['by_item']->take(5) as $index => $row)
                        <li class="flex items-center gap-3 py-2.5">
                            <span class="tnum flex h-7 w-7 shrink-0 items-center justify-center rounded-sm bg-sand text-xs font-bold text-muted">{{ $index + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-charcoal">{{ $locale === 'ar' ? $row->name_ar : ($row->name_en ?: $row->name_ar) }}</p>
                                <p class="tnum text-xs text-muted">{{ $row->quantity }} ×</p>
                            </div>
                            <p class="tnum text-sm font-bold text-ink-soft"><x-money :minor="$row->revenue_minor" /></p>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1.55fr_1fr]">
        <section class="card overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-4">
                <h2 class="section-title">{{ __('menuqr.dashboard.recent_active') }}</h2>
                <a href="{{ route('staff.orders.index') }}" class="link text-xs">{{ __('menuqr.dashboard.view_all') }}</a>
            </div>

            @if ($activeOrders->isEmpty())
                <div class="px-5 py-10 text-center text-sm text-muted">{{ __('menuqr.dashboard.no_active') }}</div>
            @else
                <div class="divide-y divide-line xl:hidden">
                    @foreach ($activeOrders as $order)
                        <a href="{{ route('staff.orders.show', $order) }}" class="flex items-center justify-between gap-3 p-4">
                            <div>
                                <p class="tnum font-bold text-charcoal" dir="ltr">{{ $order->number }}</p>
                                <p class="mt-1 text-xs text-muted">{{ $order->table?->name ?? '—' }} · {{ format_time($order->placed_at) }}</p>
                            </div>
                            <div class="text-end">
                                <x-status-badge :status="$order->status" />
                                <p class="tnum mt-1 text-xs font-bold text-ink-soft"><x-money :minor="$order->total_minor" /></p>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="hidden overflow-x-auto md:block">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('menuqr.order.number') }}</th>
                                <th>{{ __('menuqr.order.table') }}</th>
                                <th>{{ __('menuqr.common.status') }}</th>
                                <th>{{ __('menuqr.order.placed_at') }}</th>
                                <th class="text-end">{{ __('menuqr.order.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activeOrders as $order)
                                <tr>
                                    <td class="font-bold">
                                        <a href="{{ route('staff.orders.show', $order) }}" class="link tnum" dir="ltr">{{ $order->number }}</a>
                                    </td>
                                    <td>{{ $order->table?->name ?? '—' }}</td>
                                    <td><x-status-badge :status="$order->status" /></td>
                                    <td class="tnum text-muted">{{ format_time($order->placed_at) }}</td>
                                    <td class="text-end font-bold"><x-money :minor="$order->total_minor" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="card-pad">
            <div class="flex items-center justify-between gap-3">
                <h2 class="section-title">{{ __('menuqr.dashboard.activity') }}</h2>
                <a href="{{ route('admin.activity.index') }}" class="link text-xs">{{ __('menuqr.activity.title') }}</a>
            </div>
            <p class="mt-1 text-xs text-muted">{{ __('menuqr.dashboard.activity_hint') }}</p>

            @if ($recentActivity->isEmpty())
                <p class="mt-5 text-sm text-muted">{{ __('menuqr.dashboard.no_activity') }}</p>
            @else
                <ol class="mt-4 divide-y divide-line">
                    @foreach ($recentActivity as $log)
                        <li class="py-2.5">
                            <p class="text-sm font-semibold text-charcoal">
                                {{ $log->label() }}
                                @if ($log->description)
                                    <span class="font-normal text-muted">— {{ $log->description }}</span>
                                @endif
                            </p>
                            <p class="mt-0.5 text-[0.68rem] text-muted">
                                {{ $log->user?->name ?? __('menuqr.activity.system') }} · <span class="tnum">{{ format_datetime($log->created_at, 'd/m') }}</span>
                            </p>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>

    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['admin.items.create', 'plus', 'dashboard.add_item'],
            ['admin.tables.index', 'qr', 'dashboard.manage_tables'],
            ['admin.reports.index', 'reports', 'dashboard.view_reports'],
            ['admin.settings.edit', 'settings', 'dashboard.edit_settings'],
        ] as [$route, $icon, $label])
            <a href="{{ route($route) }}" class="card flex items-center gap-3 p-4 transition hover:border-charcoal">
                <span class="flex h-10 w-10 items-center justify-center rounded-sm bg-sand text-ink-soft"><x-icon :name="$icon" class="h-5 w-5" /></span>
                <span class="text-sm font-bold text-ink-soft">{{ __('menuqr.'.$label) }}</span>
                <x-icon name="chevron" class="ms-auto h-4 w-4 text-line-strong rtl:rotate-180" />
            </a>
        @endforeach
    </div>
@endsection
