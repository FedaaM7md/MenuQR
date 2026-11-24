{{--
    MenuQR activity log.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', __('menuqr.activity.title'))

@section('content')
    <div class="mb-5">
        <p class="text-[0.62rem] font-bold uppercase tracking-[0.18em] text-saffron-deep">{{ __('menuqr.nav.management') }}</p>
        <h2 class="mt-1 font-display text-xl font-bold text-charcoal">{{ __('menuqr.activity.title') }}</h2>
        <p class="mt-1 text-sm text-muted">{{ __('menuqr.activity.subtitle') }}</p>
    </div>

    <div class="card overflow-hidden">
        @if ($logs->isEmpty())
            <div class="px-6 py-14 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-sm bg-sand text-muted">
                    <x-icon name="shield" class="h-6 w-6" />
                </span>
                <p class="mt-4 text-sm font-semibold text-muted">{{ __('menuqr.activity.empty') }}</p>
            </div>
        @else
            <div class="divide-y divide-line xl:hidden">
                @foreach ($logs as $log)
                    <article class="p-4">
                        <p class="text-sm font-semibold text-charcoal">{{ $log->label() }}</p>
                        <p class="mt-1 text-xs text-muted">{{ $log->description ?: '—' }}</p>
                        <p class="mt-2 text-[0.68rem] text-muted">{{ $log->user?->name ?? __('menuqr.activity.system') }} · <span class="tnum">{{ format_datetime($log->created_at) }}</span></p>
                    </article>
                @endforeach
            </div>

            <div class="table-wrap hidden xl:block">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('menuqr.activity.when') }}</th>
                            <th>{{ __('menuqr.activity.who') }}</th>
                            <th>{{ __('menuqr.activity.action') }}</th>
                            <th>{{ __('menuqr.activity.target') }}</th>
                            <th>{{ __('menuqr.activity.ip') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td class="tnum whitespace-nowrap text-muted">{{ format_datetime($log->created_at) }}</td>
                                <td class="font-semibold">{{ $log->user?->name ?? __('menuqr.activity.system') }}</td>
                                <td>
                                    <span class="badge bg-sand text-ink-soft">{{ $log->label() }}</span>
                                </td>
                                <td class="text-muted">{{ $log->description ?: '—' }}</td>
                                <td class="tnum text-xs text-muted" dir="ltr">{{ $log->ip_address ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="border-t border-line px-5 py-4">{{ $logs->links() }}</div>
            @endif
        @endif
    </div>
@endsection
