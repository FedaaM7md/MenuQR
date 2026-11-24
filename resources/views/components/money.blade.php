{{--
    MenuQR - money.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@props(['minor' => 0, 'class' => ''])

<bdi {{ $attributes->merge(['class' => 'tnum whitespace-nowrap '.$class]) }}>{{ money((int) $minor) }}</bdi>
