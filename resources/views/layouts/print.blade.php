{{--
    MenuQR - print.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ text_dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('menuqr.tables.print_sheet'))</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4; margin: 12mm; }
    </style>
</head>
<body class="bg-white text-charcoal">
    @yield('content')
</body>
</html>
