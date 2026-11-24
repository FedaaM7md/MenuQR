{{--
    MenuQR - edit.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', __('menuqr.settings.title'))

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mx-auto max-w-4xl space-y-5">
        @csrf
        @method('PUT')

        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-charcoal">{{ __('menuqr.settings.title') }}</h2>
                <p class="mt-1 text-sm text-muted">{{ __('menuqr.settings.subtitle') }}</p>
            </div>
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="h-4 w-4" />
                {{ __('menuqr.common.save') }}
            </button>
        </div>

        <section class="card-pad">
            <h3 class="section-title">{{ __('menuqr.settings.identity') }}</h3>
            <p class="mt-1 text-xs text-muted">{{ __('menuqr.settings.saved_hint') }}</p>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="restaurant_name_ar" class="label">{{ __('menuqr.settings.name_ar') }}</label>
                    <input id="restaurant_name_ar" name="restaurant_name_ar" value="{{ old('restaurant_name_ar', $settings->restaurant_name_ar) }}" required maxlength="80" class="input @error('restaurant_name_ar') input-invalid @enderror">
                    @error('restaurant_name_ar')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="restaurant_name_en" class="label">{{ __('menuqr.settings.name_en') }}</label>
                    <input id="restaurant_name_en" name="restaurant_name_en" value="{{ old('restaurant_name_en', $settings->restaurant_name_en) }}" required maxlength="80" class="input @error('restaurant_name_en') input-invalid @enderror" dir="ltr">
                    @error('restaurant_name_en')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tagline_ar" class="label">{{ __('menuqr.settings.tagline_ar') }}</label>
                    <input id="tagline_ar" name="tagline_ar" value="{{ old('tagline_ar', $settings->tagline_ar) }}" maxlength="120" class="input">
                </div>
                <div>
                    <label for="tagline_en" class="label">{{ __('menuqr.settings.tagline_en') }}</label>
                    <input id="tagline_en" name="tagline_en" value="{{ old('tagline_en', $settings->tagline_en) }}" maxlength="120" class="input" dir="ltr">
                </div>
                <div>
                    <label for="address_ar" class="label">{{ __('menuqr.settings.address_ar') }}</label>
                    <input id="address_ar" name="address_ar" value="{{ old('address_ar', $settings->address_ar) }}" maxlength="160" class="input">
                </div>
                <div>
                    <label for="address_en" class="label">{{ __('menuqr.settings.address_en') }}</label>
                    <input id="address_en" name="address_en" value="{{ old('address_en', $settings->address_en) }}" maxlength="160" class="input" dir="ltr">
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-4 rounded-sm border border-line p-4 sm:flex-row sm:items-center">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-sm bg-sand ring-1 ring-line">
                    @if ($settings->logo_path)
                        <img src="{{ media_url($settings->logo_path) }}" alt="{{ __('menuqr.settings.current_logo') }}" class="h-full w-full object-cover">
                    @else
                        <x-logo class="h-12 w-12" :show-text="false" />
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <label for="logo" class="label">{{ __('menuqr.settings.logo') }}</label>
                    <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm text-muted file:me-3 file:rounded-sm file:border-0 file:bg-sand file:px-3 file:py-2 file:text-xs file:font-bold file:text-charcoal hover:file:bg-saffron-soft">
                    <p class="hint mt-1">{{ __('menuqr.settings.logo_hint') }}</p>
                    @error('logo')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                @if ($settings->logo_path)
                    <label class="flex shrink-0 items-center gap-2 text-xs font-semibold text-tomato">
                        <input type="checkbox" name="remove_logo" value="1" class="h-4 w-4 rounded border-line-strong text-tomato focus:ring-tomato">
                        {{ __('menuqr.settings.remove_logo') }}
                    </label>
                @endif
            </div>
        </section>

        <section class="card-pad">
            <h3 class="section-title">{{ __('menuqr.settings.fees') }}</h3>
            <p class="mt-1 text-xs text-muted">{{ __('menuqr.settings.fees_hint') }}</p>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="tax_percent" class="label">{{ __('menuqr.settings.tax') }}</label>
                    <input id="tax_percent" name="tax_percent" type="number" step="0.01" min="0" max="100" required
                           value="{{ old('tax_percent', number_format($settings->tax_percent / 100, 2, '.', '')) }}"
                           class="input @error('tax_percent') input-invalid @enderror" dir="ltr">
                    @error('tax_percent')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="service_percent" class="label">{{ __('menuqr.settings.service') }}</label>
                    <input id="service_percent" name="service_percent" type="number" step="0.01" min="0" max="100" required
                           value="{{ old('service_percent', number_format($settings->service_percent / 100, 2, '.', '.')) }}"
                           class="input @error('service_percent') input-invalid @enderror" dir="ltr">
                    @error('service_percent')<p class="error-text">{{ $message }}</p>@enderror
                </div>
            </div>
            <p class="mt-3 rounded-sm bg-sand px-3 py-2 text-xs text-muted">{{ __('menuqr.settings.currency_note') }}</p>
        </section>

        <section class="card-pad">
            <h3 class="section-title">{{ __('menuqr.settings.contact') }}</h3>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="whatsapp_number" class="label">{{ __('menuqr.settings.whatsapp') }}</label>
                    <input id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number', $settings->whatsapp_number) }}" maxlength="25"
                           class="input @error('whatsapp_number') input-invalid @enderror" dir="ltr" placeholder="+201000000000">
                    <p class="hint mt-1">{{ __('menuqr.settings.whatsapp_hint') }}</p>
                    @error('whatsapp_number')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-end">
                    <label class="flex w-full cursor-pointer items-center justify-between gap-3 rounded-sm border border-line px-4 py-3">
                        <span>
                            <span class="block text-sm font-bold text-charcoal">{{ __('menuqr.settings.accepting_orders') }}</span>
                            <span class="block text-xs text-muted">{{ __('menuqr.settings.accepting_hint') }}</span>
                        </span>
                        <input type="checkbox" name="is_accepting_orders" value="1" @checked(old('is_accepting_orders', $settings->is_accepting_orders))
                               class="h-5 w-5 rounded border-line-strong text-saffron-deep focus:ring-saffron">
                    </label>
                </div>
            </div>
        </section>

        <section class="card-pad">
            <h3 class="section-title">{{ __('menuqr.settings.hours') }}</h3>
            <p class="mt-1 text-xs text-muted">{{ __('menuqr.settings.fees_hint') }}</p>

            <div class="mt-5 space-y-2">
                @foreach (__('menuqr.days') as $day => $label)
                    @php
                        $row = old('hours.'.$day, $settings->hours[$day] ?? ['open' => '10:00', 'close' => '23:00', 'closed' => false]);
                        $closed = filter_var($row['closed'] ?? false, FILTER_VALIDATE_BOOL);
                    @endphp
                    <div class="grid grid-cols-1 items-center gap-2 rounded-sm border border-line px-3 py-2.5 sm:grid-cols-[1fr_auto_auto_auto]">
                        <span class="text-sm font-bold text-ink-soft">{{ $label }}</span>
                        <div class="flex items-center gap-1.5">
                            <label for="hours-{{ $day }}-open" class="sr-only">{{ __('menuqr.settings.open') }}</label>
                            <input id="hours-{{ $day }}-open" name="hours[{{ $day }}][open]" type="time" value="{{ $row['open'] ?? '10:00' }}"
                                   class="input w-auto py-1.5 text-xs" dir="ltr">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <label for="hours-{{ $day }}-close" class="sr-only">{{ __('menuqr.settings.close') }}</label>
                            <input id="hours-{{ $day }}-close" name="hours[{{ $day }}][close]" type="time" value="{{ $row['close'] ?? '23:00' }}"
                                   class="input w-auto py-1.5 text-xs" dir="ltr">
                        </div>
                        <label class="flex items-center gap-2 text-xs font-semibold text-muted">
                            <input type="checkbox" name="hours[{{ $day }}][closed]" value="1" @checked($closed)
                                   class="h-4 w-4 rounded border-line-strong text-charcoal focus:ring-saffron">
                            {{ __('menuqr.settings.closed') }}
                        </label>
                    </div>
                @endforeach
            </div>
            @error('hours.*.open')<p class="error-text">{{ $message }}</p>@enderror
            @error('hours.*.close')<p class="error-text">{{ $message }}</p>@enderror
        </section>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">{{ __('menuqr.common.cancel') }}</a>
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="h-4 w-4" />
                {{ __('menuqr.common.save') }}
            </button>
        </div>
    </form>
@endsection
