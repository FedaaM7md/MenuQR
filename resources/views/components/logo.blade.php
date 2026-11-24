{{--
    MenuQR logo: a cloche dome with a QR finder pattern inside, on a plate line.
    Original artwork. Copyright (c) 2026 Fedaa Mohamed.
--}}
@props(['class' => 'h-10 w-10', 'markOnly' => false, 'showText' => true, 'light' => false])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <svg class="{{ $class }}" viewBox="0 0 48 48" fill="none" role="img" aria-hidden="true">
        <path d="M6 36.5h36" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" class="{{ $light ? 'text-cream' : 'text-charcoal' }}"/>
        <path d="M9.5 33.5a14.5 14.5 0 0 1 29 0" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" class="{{ $light ? 'text-cream' : 'text-charcoal' }}"/>
        <path d="M24 12.5v3.6" stroke="#E3A72F" stroke-width="2.6" stroke-linecap="round"/>
        <circle cx="24" cy="11" r="2" fill="#E3A72F"/>
        <rect x="16.5" y="20.5" width="6" height="6" rx="1" stroke="#E3A72F" stroke-width="1.8"/>
        <rect x="18.3" y="22.3" width="2.4" height="2.4" rx=".4" fill="#E3A72F"/>
        <rect x="26" y="20.5" width="5.5" height="5.5" rx="1" stroke="#6B7A3A" stroke-width="1.8"/>
        <path d="M16.5 29.5h6M26 29.5h2.2M30.5 29.5h1" stroke="#6B7A3A" stroke-width="1.8" stroke-linecap="round"/>
        <path d="M9.5 33.5h29" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" class="{{ $light ? 'text-cream' : 'text-charcoal' }}"/>
    </svg>

    @if ($showText && ! $markOnly)
        <span class="font-display text-lg font-bold tracking-tight {{ $light ? 'text-cream' : 'text-charcoal' }}">MenuQR</span>
    @endif
</span>
