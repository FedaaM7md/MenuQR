{{--
    MenuQR - lang-switch.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@php
    $current = app()->getLocale();
    $target = $current === 'ar' ? 'en' : 'ar';
@endphp

<a href="{{ route('locale.switch', $target) }}"
   class="lang-switch"
   hreflang="{{ $target }}"
   aria-label="{{ __('menuqr.common.switch_language') }}">
    <x-icon name="globe" class="h-3.5 w-3.5" />
    <span>{{ $current === 'ar' ? __('menuqr.common.english') : __('menuqr.common.arabic') }}</span>
</a>
