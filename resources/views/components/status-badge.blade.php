{{--
    MenuQR - status-badge.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@props(['status' => 'new'])

@php
    $classes = match ($status) {
        'new' => 'badge-new',
        'preparing' => 'badge-preparing',
        'ready' => 'badge-ready',
        'served' => 'badge-served',
        'cancelled' => 'badge-cancelled',
        default => 'badge bg-sand text-muted',
    };
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
    {{ __('menuqr.statuses.'.$status) }}
</span>
