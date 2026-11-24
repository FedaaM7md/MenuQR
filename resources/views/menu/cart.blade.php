{{--
    MenuQR cart and checkout — a printed bill.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.public')

@section('title', __('menuqr.cart.title'))

@section('content')
    <div class="container-page py-8 lg:py-10">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-[0.62rem] font-bold uppercase tracking-[0.18em] text-saffron-deep">{{ __('menuqr.menu.table_label', ['name' => $table->name]) }}</p>
                <h1 class="mt-1 font-display text-2xl font-bold text-charcoal sm:text-3xl">{{ __('menuqr.cart.title') }}</h1>
            </div>
            <a href="{{ route('menu.show', $table->token) }}" class="btn-secondary btn-sm">
                <x-icon name="chevron" class="h-4 w-4 rotate-180 rtl:rotate-0" />
                {{ __('menuqr.cart.continue') }}
            </a>
        </div>

        @if ($preview['removed'] > 0)
            <div class="mb-4 rounded-sm border border-saffron/50 bg-saffron-soft px-4 py-3 text-sm font-semibold text-charcoal" role="status">
                {{ __('menuqr.cart.removed_items', ['count' => $preview['removed']]) }}
            </div>
        @endif

        @if (empty($preview['lines']))
            <div class="menu-paper mx-auto max-w-lg px-6 py-14 text-center">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-sm bg-sand text-muted">
                    <x-icon name="cart" class="h-7 w-7" />
                </span>
                <h2 class="mt-5 font-display text-xl font-bold text-charcoal">{{ __('menuqr.cart.empty') }}</h2>
                <p class="mt-2 text-sm text-muted">{{ __('menuqr.cart.empty_hint') }}</p>
                <a href="{{ route('menu.show', $table->token) }}" class="btn-primary mt-6">
                    <x-icon name="utensils" class="h-4 w-4" />
                    {{ __('menuqr.cart.browse') }}
                </a>
            </div>
        @else
            <div class="grid items-start gap-6 lg:grid-cols-[1.4fr_1fr]">
                <div class="menu-paper overflow-hidden">
                    <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-4">
                        <h2 class="font-display text-lg font-bold text-charcoal">{{ __('menuqr.cart.your_order') }}</h2>
                        <form method="POST" action="{{ route('menu.cart.clear', $table->token) }}"
                              data-confirm-submit data-confirm="{{ __('menuqr.cart.clear_confirm') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-ghost btn-sm text-tomato hover:bg-tomato-soft">
                                <x-icon name="trash" class="h-4 w-4" />
                                {{ __('menuqr.cart.clear') }}
                            </button>
                        </form>
                    </div>

                    <ul class="divide-y divide-line">
                        @foreach ($preview['lines'] as $line)
                            @php($pricing = $line['pricing'])
                            <li class="p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="font-display font-bold text-charcoal">{{ $pricing['item']->name() }}</p>
                                        <p class="tnum mt-0.5 text-xs text-muted">
                                            <x-money :minor="$pricing['unit_total_minor']" /> × {{ $pricing['quantity'] }}
                                        </p>

                                        @if (count($pricing['options']) > 0)
                                            <ul class="mt-2 space-y-1">
                                                @foreach ($pricing['options'] as $choice)
                                                    <li class="flex flex-wrap items-center gap-1.5 text-xs text-muted">
                                                        <span class="font-semibold text-ink-soft">{{ $choice['group']->name() }}:</span>
                                                        <span>{{ $choice['option']->name() }}</span>
                                                        @if ($choice['option']->price_delta_minor !== 0)
                                                            <span class="tnum {{ $choice['option']->price_delta_minor > 0 ? 'text-muted' : 'text-olive-deep' }}">
                                                                ({{ $choice['option']->price_delta_minor > 0 ? '+' : '' }}<x-money :minor="$choice['option']->price_delta_minor" />)
                                                            </span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif

                                        @if ($line['notes'])
                                            <p class="mt-2 rounded-sm bg-sand px-2.5 py-1.5 text-xs text-ink-soft">
                                                <span class="font-bold">{{ __('menuqr.cart.added_note') }}:</span> {{ $line['notes'] }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="shrink-0 text-end">
                                        <p class="tnum font-display font-bold text-charcoal"><x-money :minor="$pricing['line_total_minor']" /></p>

                                        <div class="mt-3 flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('menu.cart.update', [$table->token, $line['key']]) }}" class="flex items-center gap-1.5">
                                                @csrf
                                                @method('PATCH')
                                                <label class="sr-only" for="qty-{{ $line['key'] }}">{{ __('menuqr.cart.quantity') }}</label>
                                                <input id="qty-{{ $line['key'] }}" name="quantity" type="number" min="1" max="99"
                                                       value="{{ $pricing['quantity'] }}"
                                                       class="tnum w-16 rounded-sm border border-line-strong bg-paper px-2 py-1.5 text-center text-sm font-bold text-charcoal focus:border-saffron-deep focus:outline-none">
                                                <button type="submit" class="btn-secondary btn-sm">{{ __('menuqr.cart.update_quantity') }}</button>
                                            </form>

                                            <form method="POST" action="{{ route('menu.cart.remove', [$table->token, $line['key']]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost btn-sm text-muted hover:text-tomato" aria-label="{{ __('menuqr.cart.remove') }}">
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="space-y-5 lg:sticky lg:top-28">
                    <div class="menu-paper p-5">
                        <h2 class="font-display text-lg font-bold text-charcoal">{{ __('menuqr.cart.summary') }}</h2>
                        <div class="mt-4">
                            <x-order-totals
                                :minor="$preview['subtotal_minor']"
                                :tax-percent="$preview['tax_percent']"
                                :tax-minor="$preview['tax_minor']"
                                :service-percent="$preview['service_percent']"
                                :service-minor="$preview['service_minor']"
                                :total="$preview['total_minor']"
                            />
                        </div>
                        <p class="mt-3 text-xs text-muted">{{ __('menuqr.cart.total_hint') }}</p>
                    </div>

                    <form method="POST" action="{{ route('menu.orders.store', $table->token) }}" class="menu-paper p-5" data-checkout-form data-submitting-label="{{ __('menuqr.checkout.submitting') }}">
                        @csrf
                        <h2 class="font-display text-lg font-bold text-charcoal">{{ __('menuqr.checkout.title') }}</h2>

                        @unless ($settings->is_accepting_orders)
                            <div class="mt-4 flex items-start gap-2 rounded-sm bg-saffron-soft px-3 py-2.5 text-xs font-semibold text-charcoal">
                                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
                                {{ __('menuqr.menu.not_accepting') }}
                            </div>
                        @endunless

                        <fieldset class="mt-4">
                            <legend class="label">{{ __('menuqr.checkout.type') }}</legend>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach (['dine_in', 'takeaway'] as $type)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-sm border border-line px-3 py-2.5 text-sm font-semibold text-ink-soft transition has-checked:border-charcoal has-checked:bg-sand has-checked:text-charcoal">
                                        <input type="radio" name="type" value="{{ $type }}" @checked(old('type', 'dine_in') === $type)
                                               class="h-4 w-4 border-line-strong text-charcoal focus:ring-saffron" required>
                                        {{ __('menuqr.order_types.'.$type) }}
                                    </label>
                                @endforeach
                            </div>
                            @error('type')
                                <p class="error-text">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="customer_name" class="label">{{ __('menuqr.checkout.name') }} <span class="font-normal text-muted">({{ __('menuqr.common.optional') }})</span></label>
                                <input id="customer_name" name="customer_name" maxlength="80" value="{{ old('customer_name') }}" class="input" placeholder="{{ __('menuqr.checkout.name_placeholder') }}">
                            </div>
                            <div>
                                <label for="customer_phone" class="label">{{ __('menuqr.checkout.phone') }} <span class="font-normal text-muted">({{ __('menuqr.common.optional') }})</span></label>
                                <input id="customer_phone" name="customer_phone" type="tel" maxlength="25" value="{{ old('customer_phone') }}" class="input" placeholder="{{ __('menuqr.checkout.phone_placeholder') }}" dir="ltr">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="order_notes" class="label">{{ __('menuqr.checkout.notes') }}</label>
                            <textarea id="order_notes" name="notes" rows="2" maxlength="500" class="input" placeholder="{{ __('menuqr.checkout.notes_placeholder') }}">{{ old('notes') }}</textarea>
                        </div>

                        {{-- Honeypot: hidden from people, tempting to bots. --}}
                        <div class="absolute -start-[9999px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                            <label for="website">{{ __('menuqr.checkout.honeypot') }}</label>
                            <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                        </div>

                        <p class="mt-3 flex items-start gap-1.5 text-xs text-muted">
                            <x-icon name="whatsapp" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                            {{ __('menuqr.checkout.whatsapp_note') }}
                        </p>

                        <div id="checkout-offline" class="hidden mt-3 rounded-sm bg-saffron-soft px-3 py-2 text-xs font-bold text-charcoal" role="status">
                            {{ __('menuqr.checkout.offline_notice') }}
                        </div>

                        <button type="submit" class="btn-primary mt-5 w-full py-3" data-checkout-submit>
                            <x-icon name="check" class="h-4 w-4" />
                            <span data-submit-label>{{ __('menuqr.checkout.submit') }}</span>
                        </button>
                        <p class="mt-3 text-center text-[0.68rem] text-muted">{{ __('menuqr.checkout.terms_note') }}</p>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
