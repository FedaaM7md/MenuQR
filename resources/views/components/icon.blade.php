{{--
    MenuQR - icon.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@props(['name', 'class' => 'h-5 w-5'])

@php
    $paths = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'menu' => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v18H6.5A2.5 2.5 0 0 1 4 18.5v-13Z"/><path d="M8 7h8M8 11h8M8 15h5"/>',
        'tag' => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8Z"/><circle cx="8" cy="8" r="1.5"/>',
        'utensils' => '<path d="M7 3v6a2 2 0 0 0 4 0V3M9 9v12"/><path d="M17 3c-1.7 1.2-2.5 3.3-2.5 5.5S15.3 13 17 13v8"/>',
        'tables' => '<rect x="3" y="4" width="18" height="14" rx="3"/><path d="M3 12h18M12 4v14M8 21h8"/>',
        'orders' => '<rect x="5" y="4" width="14" height="17" rx="2.5"/><path d="M9 4.5V3h6v1.5M9 10h6M9 14h6M9 18h3"/>',
        'kitchen' => '<path d="M12 3c1 4 5 5 5 9.5A5 5 0 0 1 7 12.5C7 9 9 7 12 3Z"/><path d="M12 13c.6 1.7 2 2.3 2 4a2 2 0 1 1-4 0c0-1.7 1.4-2.3 2-4Z"/>',
        'reports' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'settings' => '<circle cx="12" cy="12" r="3.2"/><path d="M12 2.5v3M12 18.5v3M2.5 12h3M18.5 12h3M5.3 5.3l2.1 2.1M16.6 16.6l2.1 2.1M18.7 5.3l-2.1 2.1M7.4 16.6l-2.1 2.1"/>',
        'staff' => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><path d="M16 5a3 3 0 0 1 0 6M17.5 14.8c2.1.5 3.5 2.2 3.5 4.7"/>',
        'logout' => '<path d="M10 4H5.5A1.5 1.5 0 0 0 4 5.5v13A1.5 1.5 0 0 0 5.5 20H10"/><path d="M14 8l4 4-4 4M8 12h10"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'minus' => '<path d="M5 12h14"/>',
        'edit' => '<path d="m4 20 4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20Z"/><path d="m13.5 5.5 3 3"/>',
        'trash' => '<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/><path d="M10 11v5M14 11v5"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m20 20-4.5-4.5"/>',
        'qr' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM20 14v3M14 20h3M20 20h1"/>',
        'print' => '<path d="M7 8V3h10v5"/><rect x="4" y="8" width="16" height="8" rx="2"/><path d="M7 16h10v5H7z"/>',
        'check' => '<path d="m5 12.5 4.5 4.5L19 6.5"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'chevron' => '<path d="m9 6 6 6-6 6"/>',
        'whatsapp' => '<path d="M20.5 11.6a8.5 8.5 0 0 1-12.6 7.5L3 20.5l1.4-4.7a8.5 8.5 0 1 1 16.1-4.2Z"/><path d="M8.5 8.2c.4-.8 1-.5 1.3.1l.7 1.5c.2.4 0 .7-.3 1l-.5.5c.6 1.2 1.5 2 2.6 2.5l.5-.6c.2-.3.6-.4 1-.2l1.6.8c.7.3.7 1 .2 1.5-.7.8-1.8 1.2-2.8.9-2.7-.8-5.1-3.2-5.8-5.9-.2-.8 0-1.6.5-2.1Z"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.5 3.8 5.6 3.8 9s-1.3 6.5-3.8 9c-2.5-2.5-3.8-5.6-3.8-9S9.5 5.5 12 3Z"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5.5l3.5 2"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/>',
        'refresh' => '<path d="M20 11a8 8 0 1 0-2.3 5.7"/><path d="M20 4v7h-7"/>',
        'alert' => '<path d="M12 3 2.5 20h19L12 3Z"/><path d="M12 9v5M12 17.5v.1"/>',
        'eye' => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/>',
        'cart' => '<path d="M3 4h2.5l2.2 10.5h10.6L21 7H6"/><circle cx="9" cy="19" r="1.5"/><circle cx="18" cy="19" r="1.5"/>',
        'copy' => '<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3"/>',
        'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="1.5"/><path d="m4 18 5-5 3 3 3-4 5 6"/>',
        'sparkles' => '<path d="M12 3l1.6 4.4L18 9l-4.4 1.6L12 15l-1.6-4.4L6 9l4.4-1.6L12 3Z"/><path d="M18.5 15.5l.8 2.2 2.2.8-2.2.8-.8 2.2-.8-2.2-2.2-.8 2.2-.8.8-2.2Z"/>',
        'lock' => '<rect x="4" y="10" width="16" height="11" rx="2.5"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/>',
        'external' => '<path d="M14 4h6v6M20 4l-8 8"/><path d="M18 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4"/>',
        'flame' => '<path d="M12 3c1 4 5 5 5 9.5A5 5 0 0 1 7 12.5C7 9 9 7 12 3Z"/>',
        'bell' => '<path d="M6 9a6 6 0 1 1 12 0c0 4 1.5 5.5 2 6H4c.5-.5 2-2 2-6Z"/><path d="M10 19a2 2 0 0 0 4 0"/>',
        'download' => '<path d="M12 3v12M7 10l5 5 5-5"/><path d="M4 19h16"/>',
        'shield' => '<path d="M12 3l7 3v5.5c0 4.4-3 8-7 9.5-4-1.5-7-5.1-7-9.5V6l7-3Z"/><path d="m9 12 2 2 4-4"/>',
        'book' => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5v-15Z"/><path d="M4 20.5A2.5 2.5 0 0 1 6.5 18H20v3H6.5"/>',
        'key' => '<circle cx="8" cy="12" r="4"/><path d="M12 12h9M17 12v3M20 12v2"/>',
        'receipt' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6M9 16h3"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    {!! $paths[$name] ?? $paths['dashboard'] !!}
</svg>
