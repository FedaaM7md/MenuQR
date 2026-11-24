{{--
    MenuQR item filters (desktop inline / mobile sheet).
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@php($prefix = $prefix ?? 'desktop')

<div class="grid gap-3 sm:grid-cols-[1.4fr_1fr_1fr_auto_auto] sm:items-end">
    <div>
        <label for="{{ $prefix }}-q" class="label">{{ __('menuqr.common.search') }}</label>
        <div class="relative">
            <x-icon name="search" class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
            <input id="{{ $prefix }}-q" name="q" value="{{ $search }}" class="input ps-9" placeholder="{{ __('menuqr.items.search_placeholder') }}" data-search-input>
        </div>
    </div>
    <div>
        <label for="{{ $prefix }}-category" class="label">{{ __('menuqr.items.category') }}</label>
        <select id="{{ $prefix }}-category" name="category" class="input">
            <option value="">{{ __('menuqr.common.all') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="{{ $prefix }}-availability" class="label">{{ __('menuqr.items.filter_availability') }}</label>
        <select id="{{ $prefix }}-availability" name="availability" class="input">
            <option value="">{{ __('menuqr.common.all') }}</option>
            <option value="on" @selected(request('availability') === 'on')>{{ __('menuqr.items.available_only') }}</option>
            <option value="off" @selected(request('availability') === 'off')>{{ __('menuqr.items.unavailable_only') }}</option>
        </select>
    </div>
    <button type="submit" class="btn-secondary">
        <x-icon name="search" class="h-4 w-4" />
        {{ __('menuqr.common.search') }}
    </button>
    <a href="{{ route('admin.items.index') }}" class="btn-ghost">{{ __('menuqr.common.reset') }}</a>
</div>
