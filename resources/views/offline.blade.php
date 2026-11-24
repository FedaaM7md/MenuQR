{{--
    MenuQR - offline.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.public')

@section('title', __('menuqr.pwa.offline_title'))

@section('content')
    <div class="container-page py-16 sm:py-24">
        <div class="mx-auto max-w-lg text-center">
            <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-sand text-charcoal">
                <x-icon name="alert" class="h-9 w-9" />
            </span>
            <h1 class="mt-6 text-3xl font-bold text-charcoal">{{ __('menuqr.pwa.offline_title') }}</h1>
            <p class="mt-3 text-base leading-7 text-muted">{{ __('menuqr.pwa.offline_body') }}</p>
            <button type="button" onclick="window.location.reload()" class="btn-primary mt-8">
                <x-icon name="refresh" class="h-4 w-4" />
                {{ __('menuqr.pwa.offline_retry') }}
            </button>
            <p class="mt-4 text-xs text-muted">{{ __('menuqr.pwa.offline_menu_hint') }}</p>
        </div>
    </div>
@endsection
