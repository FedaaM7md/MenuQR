{{--
    MenuQR - order-totals.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@props(['minor' => 0, 'taxPercent' => 0, 'taxMinor' => 0, 'servicePercent' => 0, 'serviceMinor' => 0, 'total' => 0, 'compact' => false])

<dl class="space-y-2.5 text-sm">
    <div class="flex items-center justify-between gap-4">
        <dt class="text-muted">{{ __('menuqr.order.subtotal') }}</dt>
        <dd class="font-semibold text-ink-soft"><x-money :minor="$minor" /></dd>
    </div>
    <div class="flex items-center justify-between gap-4">
        <dt class="text-muted">{{ __('menuqr.order.tax') }} ({{ percent($taxPercent) }})</dt>
        <dd class="font-semibold text-ink-soft"><x-money :minor="$taxMinor" /></dd>
    </div>
    @if ($servicePercent > 0 || $serviceMinor > 0)
        <div class="flex items-center justify-between gap-4">
            <dt class="text-muted">{{ __('menuqr.order.service') }} ({{ percent($servicePercent) }})</dt>
            <dd class="font-semibold text-ink-soft"><x-money :minor="$serviceMinor" /></dd>
        </div>
    @endif
    <div class="flex items-center justify-between gap-4 border-t border-dashed border-line-strong pt-3">
        <dt class="{{ $compact ? 'text-base font-bold' : 'text-lg font-bold' }} text-charcoal">{{ __('menuqr.order.total') }}</dt>
        <dd class="{{ $compact ? 'text-base' : 'text-xl' }} font-display font-bold text-charcoal"><x-money :minor="$total" /></dd>
    </div>
</dl>
