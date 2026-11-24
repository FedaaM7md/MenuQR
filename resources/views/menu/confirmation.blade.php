{{--
    MenuQR order confirmation — printed kitchen ticket.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.public')

@section('title', __('menuqr.confirmation.title'))

@section('content')
    @php
        $steps = [
            ['status' => 'new', 'time' => $order->placed_at],
            ['status' => 'preparing', 'time' => $order->preparing_at],
            ['status' => 'ready', 'time' => $order->ready_at],
            ['status' => 'served', 'time' => $order->served_at],
        ];
        $currentIndex = match ($order->status) {
            'preparing' => 1,
            'ready' => 2,
            'served' => 3,
            default => 0,
        };
    @endphp

    <div class="container-page py-8 lg:py-12">
        <div class="mx-auto max-w-xl">
            <div class="menu-paper overflow-hidden">
                <div class="bg-charcoal px-6 py-6 text-cream sm:px-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[0.62rem] font-bold uppercase tracking-[0.2em] text-saffron">{{ __('menuqr.confirmation.title') }}</p>
                            <p class="mt-2 font-display text-2xl font-bold text-cream sm:text-3xl">{{ __('menuqr.confirmation.number') }}</p>
                        </div>
                        <span class="rounded-sm bg-saffron px-2.5 py-1 text-xs font-bold text-charcoal">{{ __('menuqr.confirmation.table') }} {{ $table->name }}</span>
                    </div>
                    <p class="tnum mt-2 font-display text-3xl font-bold tracking-wide text-cream" dir="ltr">{{ $order->number }}</p>
                    <p class="mt-2 text-xs leading-6 text-cream/60">{{ __('menuqr.confirmation.subtitle') }}</p>
                </div>

                <div class="border-b border-line px-6 py-5 sm:px-8">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-display text-base font-bold text-charcoal">{{ __('menuqr.confirmation.status_now') }}</h2>
                        <x-status-badge :status="$order->status" />
                    </div>

                    @if ($order->status === 'cancelled')
                        <div class="mt-4 rounded-sm bg-tomato-soft px-4 py-3 text-sm font-semibold text-tomato-deep">
                            {{ __('menuqr.statuses.cancelled') }}
                        </div>
                    @else
                        <ol class="mt-5 grid grid-cols-4 gap-2">
                            @foreach ($steps as $index => $step)
                                @php($done = $index <= $currentIndex)
                                <li class="flex flex-col items-center text-center">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold {{ $done ? 'bg-charcoal text-cream' : 'bg-sand text-muted' }}">
                                        @if ($done)
                                            <x-icon name="check" class="h-4 w-4" />
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </span>
                                    <span class="mt-2 text-[0.68rem] font-bold {{ $done ? 'text-charcoal' : 'text-muted' }}">
                                        {{ __('menuqr.statuses.'.$step['status']) }}
                                    </span>
                                    <span class="tnum mt-0.5 text-[0.62rem] text-muted">{{ format_time($step['time']) }}</span>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>

                <div class="px-6 py-5 sm:px-8">
                    <ul class="divide-y divide-dashed divide-line">
                        @foreach ($order->items as $item)
                            <li class="flex items-start justify-between gap-4 py-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-charcoal">
                                        <span class="tnum me-1.5 inline-flex h-5 min-w-5 items-center justify-center rounded-sm bg-sand px-1 text-xs font-bold text-ink-soft">{{ $item->quantity }}×</span>
                                        {{ $item->name() }}
                                    </p>
                                    @if ($item->options->isNotEmpty())
                                        <p class="mt-1 text-xs leading-5 text-muted">
                                            {{ $item->options->map(fn ($option) => $option->groupName().': '.$option->optionName())->implode(' · ') }}
                                        </p>
                                    @endif
                                    @if ($item->notes)
                                        <p class="mt-1 text-xs italic text-muted">“{{ $item->notes }}”</p>
                                    @endif
                                </div>
                                <p class="tnum shrink-0 text-sm font-bold text-ink-soft"><x-money :minor="$item->line_total_minor" /></p>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4 border-t border-line pt-4">
                        <x-order-totals
                            :minor="$order->subtotal_minor"
                            :tax-percent="$order->tax_percent"
                            :tax-minor="$order->tax_minor"
                            :service-percent="$order->service_percent"
                            :service-minor="$order->service_minor"
                            :total="$order->total_minor"
                            compact
                        />
                    </div>

                    @if ($order->notes)
                        <div class="mt-4 rounded-sm bg-sand px-4 py-3">
                            <p class="text-[0.65rem] font-bold uppercase tracking-wide text-muted">{{ __('menuqr.order.customer_note') }}</p>
                            <p class="mt-1 text-sm text-ink-soft">{{ $order->notes }}</p>
                        </div>
                    @endif

                    <p class="mt-4 text-center text-xs text-muted">{{ __('menuqr.confirmation.thank_you', ['restaurant' => $settings->name()]) }}</p>
                </div>

                <div class="ticket-cut flex flex-col gap-2 bg-sand/60 px-6 py-4 sm:flex-row sm:px-8">
                    @if ($whatsappLink)
                        <a href="{{ $whatsappLink }}" target="_blank" rel="noopener noreferrer" class="btn-accent flex-1 py-3">
                            <x-icon name="whatsapp" class="h-5 w-5" />
                            {{ __('menuqr.confirmation.whatsapp') }}
                        </a>
                    @endif
                    <a href="{{ route('menu.show', $table->token) }}" class="btn-secondary flex-1 py-3">
                        <x-icon name="utensils" class="h-4 w-4" />
                        {{ __('menuqr.confirmation.back') }}
                    </a>
                </div>
            </div>

            @if ($whatsappLink)
                <p class="mt-3 text-center text-xs text-muted">{{ __('menuqr.confirmation.whatsapp_hint') }}</p>
            @endif
            <p class="mt-2 text-center text-xs text-muted">{{ __('menuqr.confirmation.track_note') }}</p>
        </div>
    </div>
@endsection
