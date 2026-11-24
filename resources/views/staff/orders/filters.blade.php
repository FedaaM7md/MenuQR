{{--
    MenuQR orders filters.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@php($prefix = $prefix ?? 'desktop')
@if ($status)
    <input type="hidden" name="status" value="{{ $status }}">
@endif
<div class="grid gap-3 sm:grid-cols-[1.6fr_1fr_auto_auto] sm:items-end">
    <div>
        <label for="{{ $prefix }}-q" class="label">{{ __('menuqr.common.search') }}</label>
        <div class="relative">
            <x-icon name="search" class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
            <input id="{{ $prefix }}-q" name="q" value="{{ $search }}" class="input ps-9" placeholder="{{ __('menuqr.order.search_placeholder') }}" data-search-input>
        </div>
    </div>
    <div>
        <label for="{{ $prefix }}-date" class="label">{{ __('menuqr.common.today') }}</label>
        <input id="{{ $prefix }}-date" name="date" type="date" value="{{ request('date') }}" class="input" dir="ltr">
    </div>
    <button type="submit" class="btn-secondary">
        <x-icon name="search" class="h-4 w-4" />
        {{ __('menuqr.common.search') }}
    </button>
    <a href="{{ route('staff.orders.index') }}" class="btn-ghost">{{ __('menuqr.common.reset') }}</a>
</div>
