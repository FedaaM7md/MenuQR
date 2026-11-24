{{--
    MenuQR - show.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', $order->number)

@section('content')
    @php($allowed = $workflow->allowedFor($order, auth()->user()))

    <div class="mx-auto max-w-5xl">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('staff.orders.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-charcoal">
                    <x-icon name="chevron" class="h-4 w-4 rotate-180 rtl:rotate-0" />
                    {{ __('menuqr.common.back') }}
                </a>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-bold text-charcoal" dir="ltr">{{ $order->number }}</h2>
                    <x-status-badge :status="$order->status" />
                </div>
                <p class="mt-1 text-sm text-muted">{{ __('menuqr.order.placed_at') }}: {{ format_datetime($order->placed_at, 'Y-m-d') }}</p>
            </div>
            <div class="text-end">
                <p class="text-xs font-semibold text-muted">{{ __('menuqr.order.total') }}</p>
                <p class="text-2xl font-bold text-charcoal"><x-money :minor="$order->total_minor" /></p>
            </div>
        </div>

        <div class="grid items-start gap-5 lg:grid-cols-[1.5fr_1fr]">
            <div class="space-y-5">
                <section class="card overflow-hidden">
                    <div class="border-b border-line px-5 py-4">
                        <h3 class="section-title">{{ __('menuqr.order.items') }}</h3>
                    </div>
                    <ul class="divide-y divide-line">
                        @foreach ($order->items as $item)
                            <li class="flex items-start justify-between gap-4 px-5 py-4">
                                <div class="min-w-0">
                                    <p class="font-bold text-charcoal">
                                        <span class="me-1.5 inline-flex h-6 min-w-6 items-center justify-center rounded-sm bg-sand px-1.5 text-xs font-bold text-ink-soft">{{ $item->quantity }}×</span>
                                        {{ $item->name() }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-muted">
                                        {{ __('menuqr.order.subtotal_hint') }}
                                    </p>
                                    @if ($item->options->isNotEmpty())
                                        <ul class="mt-1.5 space-y-1">
                                            @foreach ($item->options as $option)
                                                <li class="text-xs text-muted">
                                                    <span class="font-semibold">{{ $option->groupName() }}:</span> {{ $option->optionName() }}
                                                    @if ($option->price_delta_minor !== 0)
                                                        <span class="text-muted">({{ $option->price_delta_minor > 0 ? '+' : '' }}<x-money :minor="$option->price_delta_minor" />)</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if ($item->notes)
                                        <p class="mt-2 rounded-sm bg-saffron-50 px-2.5 py-1.5 text-xs font-semibold text-charcoal">{{ $item->notes }}</p>
                                    @endif
                                </div>
                                <p class="shrink-0 font-bold text-ink-soft"><x-money :minor="$item->line_total_minor" /></p>
                            </li>
                        @endforeach
                    </ul>
                    <div class="border-t border-line bg-sand/60 px-5 py-4">
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
                </section>

                @if ($order->notes)
                    <section class="card-pad">
                        <h3 class="section-title">{{ __('menuqr.order.customer_note') }}</h3>
                        <p class="mt-2 text-sm leading-6 text-ink-soft">{{ $order->notes }}</p>
                    </section>
                @endif

                <section class="card-pad">
                    <h3 class="section-title">{{ __('menuqr.order.timeline') }}</h3>
                    <ol class="mt-4 space-y-3">
                        @foreach ([
                            ['new', $order->placed_at],
                            ['preparing', $order->preparing_at],
                            ['ready', $order->ready_at],
                            ['served', $order->served_at],
                            ['cancelled', $order->cancelled_at],
                        ] as [$state, $time])
                            @if ($state !== 'cancelled' || $order->cancelled_at)
                                <li class="flex items-center gap-3 text-sm">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full {{ $time ? 'bg-charcoal text-cream' : 'bg-sand text-muted' }}">
                                        @if ($time)
                                            <x-icon name="check" class="h-3.5 w-3.5" />
                                        @else
                                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        @endif
                                    </span>
                                    <span class="{{ $time ? 'font-semibold text-charcoal' : 'text-muted' }}">{{ __('menuqr.statuses.'.$state) }}</span>
                                    <span class="ms-auto text-xs text-muted">{{ format_time($time) }}</span>
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </section>
            </div>

            <div class="space-y-5">
                <section class="card-pad">
                    <h3 class="section-title">{{ __('menuqr.common.status') }}</h3>
                    @if (count($allowed) > 0)
                        <div class="mt-4 space-y-2">
                            @foreach ($allowed as $next)
                                <form method="POST" action="{{ route('staff.orders.status', $order) }}"
                                      @if ($next === 'cancelled') data-confirm-submit data-confirm="{{ __('menuqr.order.cancel_confirm') }}" @endif>
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $next }}">
                                    <button type="submit" class="w-full {{ $next === 'cancelled' ? 'btn-danger' : 'btn-primary' }}">
                                        {{ __('menuqr.order.move_to', ['status' => __('menuqr.statuses.'.$next)]) }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-sm text-muted">
                            {{ $order->isFinal() ? __('menuqr.statuses.'.$order->status) : __('menuqr.order.no_transitions') }}
                        </p>
                    @endif
                </section>

                <section class="card-pad">
                    <h3 class="section-title">{{ __('menuqr.order.details') }}</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">{{ __('menuqr.order.table') }}</dt>
                            <dd class="font-bold text-charcoal">{{ $order->table?->name ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">{{ __('menuqr.order.type') }}</dt>
                            <dd class="font-bold text-charcoal">{{ $order->typeLabel() }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">{{ __('menuqr.order.customer') }}</dt>
                            <dd class="font-semibold text-charcoal">{{ $order->customer_name ?: '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">{{ __('menuqr.order.phone') }}</dt>
                            <dd class="font-semibold text-charcoal" dir="ltr">{{ $order->customer_phone ?: '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">{{ __('menuqr.order.placed_by') }}</dt>
                            <dd class="font-semibold text-charcoal">{{ $order->creator?->name ?? __('menuqr.order.placed_by_customer') }}</dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>
    </div>
@endsection
