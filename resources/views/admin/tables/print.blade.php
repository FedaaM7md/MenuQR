{{--
    MenuQR - print.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.print')

@section('title', __('menuqr.tables.print_sheet'))

@section('content')
    <div class="mx-auto max-w-[190mm]">
        <header class="no-print mb-6 flex items-center justify-between gap-4 border-b border-line pb-4">
            <div class="flex items-center gap-3">
                <x-logo class="h-10 w-10" />
                <div>
                    <p class="text-sm font-bold text-charcoal">{{ $settings->name() }}</p>
                    <p class="text-xs text-muted">{{ __('menuqr.tables.print_note') }}</p>
                </div>
            </div>
            <button type="button" onclick="window.print()" class="btn-primary">
                <x-icon name="print" class="h-4 w-4" />
                {{ __('menuqr.common.print') }}
            </button>
        </header>

        <div class="print-sheet grid grid-cols-1 gap-5 sm:grid-cols-2">
            @foreach ($tables as $table)
                <div class="print-card flex flex-col items-center rounded-sm border border-line-strong bg-paper px-6 py-8 text-center">
                    <div class="flex items-center gap-2">
                        @if ($settings->logo_path)
                            <img src="{{ media_url($settings->logo_path) }}" alt="" class="h-8 w-8 rounded-sm object-cover">
                        @else
                            <x-logo class="h-8 w-8" :show-text="false" />
                        @endif
                        <span class="text-sm font-bold text-charcoal">{{ $settings->name() }}</span>
                    </div>

                    <div class="mt-5 w-52">
                        <img src="{{ $qrCodes[$table->id] }}" alt="{{ __('menuqr.tables.qr').' '.$table->name }}" class="h-52 w-52">
                    </div>

                    <p class="mt-4 text-2xl font-bold text-charcoal">{{ $table->name }}</p>
                    <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-saffron-deep">{{ __('menuqr.tables.scan_hint') }}</p>
                    <p class="mt-3 break-all text-[0.6rem] text-muted" dir="ltr">{{ $table->publicUrl() }}</p>
                </div>
            @endforeach
        </div>

        @if ($tables->isEmpty())
            <p class="py-16 text-center text-sm text-muted">{{ __('menuqr.tables.empty') }}</p>
        @endif
    </div>
@endsection
