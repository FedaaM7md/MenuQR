{{--
    MenuQR - index.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', __('menuqr.reports.title'))

@section('content')
    @php($locale = app()->getLocale())

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-charcoal">{{ __('menuqr.reports.title') }}</h2>
            <p class="mt-1 text-sm text-muted">{{ __('menuqr.reports.subtitle') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <p class="text-xs font-semibold text-muted">{{ $from->format('Y-m-d') }} — {{ $to->format('Y-m-d') }}</p>
            <a href="{{ route('admin.reports.export', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn-secondary btn-sm">
                <x-icon name="download" class="h-4 w-4" />
                {{ __('menuqr.reports.export') }}
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.reports.index') }}" class="card-pad mb-5">
        <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto_auto] sm:items-end">
            <div>
                <label for="from" class="label">{{ __('menuqr.reports.from') }}</label>
                <input id="from" name="from" type="date" value="{{ $from->format('Y-m-d') }}" class="input" dir="ltr">
            </div>
            <div>
                <label for="to" class="label">{{ __('menuqr.reports.to') }}</label>
                <input id="to" name="to" type="date" value="{{ $to->format('Y-m-d') }}" class="input" dir="ltr">
            </div>
            <button type="submit" class="btn-primary">{{ __('menuqr.reports.apply') }}</button>
            <a href="{{ route('admin.reports.index') }}" class="btn-ghost">{{ __('menuqr.common.reset') }}</a>
        </div>
    </form>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <span class="stat-label">{{ __('menuqr.reports.revenue') }}</span>
            <p class="stat-value"><x-money :minor="$summary['totals']['revenue_minor']" /></p>
        </div>
        <div class="stat-card">
            <span class="stat-label">{{ __('menuqr.reports.orders_count') }}</span>
            <p class="stat-value">{{ $summary['totals']['orders'] }}</p>
        </div>
        <div class="stat-card">
            <span class="stat-label">{{ __('menuqr.reports.items_sold') }}</span>
            <p class="stat-value">{{ $summary['totals']['items'] }}</p>
        </div>
        <div class="stat-card">
            <span class="stat-label">{{ __('menuqr.reports.avg_order') }}</span>
            <p class="stat-value"><x-money :minor="$summary['totals']['average_minor']" /></p>
        </div>
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-2">
        <section class="card-pad">
            <h3 class="section-title">{{ __('menuqr.reports.by_day') }}</h3>
            @if ($summary['by_day']->isEmpty())
                <p class="mt-4 text-sm text-muted">{{ __('menuqr.reports.no_data') }}</p>
            @else
                <div class="mt-5 space-y-3">
                    @foreach ($summary['by_day'] as $row)
                        <div>
                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="font-bold text-ink-soft">{{ \Illuminate\Support\Carbon::parse($row->day)->format('d/m/Y') }}</span>
                                <span class="font-semibold text-muted">{{ $row->orders }} {{ __('menuqr.reports.orders') }} · <x-money :minor="$row->revenue_minor" /></span>
                            </div>
                            <div class="mt-1.5 h-2.5 overflow-hidden rounded-full bg-sand">
                                <div class="h-full rounded-full bg-charcoal" style="width: {{ max(2, (int) round($row->revenue_minor / $maxDay * 100)) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="card-pad">
            <h3 class="section-title">{{ __('menuqr.reports.status_breakdown') }}</h3>
            @if ($summary['statuses']->isEmpty())
                <p class="mt-4 text-sm text-muted">{{ __('menuqr.reports.no_data') }}</p>
            @else
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($summary['statuses'] as $row)
                        <span class="badge bg-sand text-ink-soft">
                            {{ __('menuqr.statuses.'.$row->status) }}
                            <span class="font-bold">{{ $row->total }}</span>
                        </span>
                    @endforeach
                </div>
            @endif

            <h3 class="section-title mt-8">{{ __('menuqr.reports.by_category') }}</h3>
            @if ($summary['by_category']->isEmpty())
                <p class="mt-4 text-sm text-muted">{{ __('menuqr.reports.no_data') }}</p>
            @else
                <ul class="mt-3 space-y-2.5">
                    @foreach ($summary['by_category'] as $row)
                        <li class="flex items-center justify-between gap-4 text-sm">
                            <span class="font-semibold text-ink-soft">{{ $locale === 'ar' ? $row->name_ar : ($row->name_en ?: $row->name_ar) }}</span>
                            <span class="text-muted">{{ $row->quantity }} × · <span class="font-bold text-charcoal"><x-money :minor="$row->revenue_minor" /></span></span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <section class="card mt-5 overflow-hidden">
        <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-4">
            <h3 class="section-title">{{ __('menuqr.reports.by_item') }}</h3>
            <span class="text-xs text-muted">{{ __('menuqr.reports.top_items') }}</span>
        </div>

        @if ($summary['by_item']->isEmpty())
            <div class="px-5 py-10 text-center text-sm text-muted">{{ __('menuqr.reports.no_data') }}</div>
        @else
            <div class="divide-y divide-line xl:hidden">
                @foreach ($summary['by_item'] as $index => $row)
                    <article class="flex items-center justify-between gap-3 px-4 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-charcoal">{{ $locale === 'ar' ? $row->name_ar : ($row->name_en ?: $row->name_ar) }}</p>
                            <p class="tnum mt-0.5 text-xs text-muted">{{ $row->quantity }} ×</p>
                        </div>
                        <p class="tnum shrink-0 text-sm font-bold text-charcoal"><x-money :minor="$row->revenue_minor" /></p>
                    </article>
                @endforeach
            </div>
            <div class="table-wrap hidden xl:block">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('menuqr.reports.item') }}</th>
                            <th>{{ __('menuqr.reports.quantity') }}</th>
                            <th class="text-end">{{ __('menuqr.reports.revenue') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($summary['by_item'] as $index => $row)
                            <tr>
                                <td class="text-muted">{{ $index + 1 }}</td>
                                <td class="font-semibold text-charcoal">{{ $locale === 'ar' ? $row->name_ar : ($row->name_en ?: $row->name_ar) }}</td>
                                <td>{{ $row->quantity }}</td>
                                <td class="text-end font-bold"><x-money :minor="$row->revenue_minor" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <p class="mt-5 rounded-sm bg-sand px-4 py-3 text-xs text-muted">{{ __('menuqr.reports.hint') }}</p>
@endsection
