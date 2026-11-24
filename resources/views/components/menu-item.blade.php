{{--
    MenuQR printed-menu row + add dialog.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@props(['item', 'table'])

@php
    $deltas = $item->optionGroups->flatMap(fn ($group) => $group->options)->mapWithKeys(
        fn ($option) => [(string) $option->id => (int) $option->price_delta_minor]
    );
    $hasPositiveDelta = $deltas->contains(fn ($delta) => $delta > 0);
    $accepting = setting()->is_accepting_orders;
    $image = media_url($item->image_path);
    $requiresChoice = $item->optionGroups->contains(fn ($group) => $group->options->where('is_available', true)->isNotEmpty());
@endphp

<div
    class="group"
    x-data="itemDialog"
    data-base-price="{{ (int) $item->price_minor }}"
    data-deltas="{{ json_encode($deltas) }}"
    data-currency="{{ __('menuqr.currency.egp') }}"
    data-limit-message="{{ __('menuqr.menu.max_reached') }}"
    @keydown.escape.window="closeDialog"
>
    <article class="menu-row">
        <div class="flex items-start gap-3 sm:gap-4">
            @if ($image)
                <img src="{{ $image }}" alt="" class="h-16 w-16 shrink-0 rounded-sm object-cover ring-1 ring-line sm:h-20 sm:w-20" loading="lazy">
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex items-baseline gap-2">
                    <h3 class="font-display text-base font-bold leading-snug text-charcoal">{{ $item->name() }}</h3>
                    @if ($item->is_featured)
                        <span class="rounded-sm bg-saffron-soft px-1.5 py-0.5 text-[0.55rem] font-bold text-charcoal">{{ __('menuqr.menu.featured') }}</span>
                    @endif
                    @unless ($item->is_available)
                        <span class="rounded-sm bg-charcoal-50 px-1.5 py-0.5 text-[0.55rem] font-bold text-muted">{{ __('menuqr.menu.unavailable') }}</span>
                    @endunless
                    <span class="leader-line"></span>
                    <span class="price">
                        @if ($hasPositiveDelta)
                            <span class="me-1 align-middle text-[0.55rem] font-semibold text-muted">{{ __('menuqr.menu.from') }}</span>
                        @endif
                        <x-money :minor="$item->price_minor" />
                    </span>
                </div>

                @if ($item->description())
                    <p class="mt-0.5 max-w-lg text-[0.8rem] leading-6 text-muted">{{ $item->description() }}</p>
                @endif

                @if ($item->allergens())
                    <p class="mt-1 text-[0.68rem] leading-5 text-tomato-deep">
                        <span class="font-bold">{{ __('menuqr.menu.allergens') }}:</span> {{ $item->allergens() }}
                    </p>
                @endif

                <div class="mt-2 flex items-center gap-2">
                    @if ($item->is_available && $accepting)
                        <button type="button" class="btn-secondary btn-sm no-print" @click="openDialog" aria-haspopup="dialog">
                            <x-icon name="plus" class="h-3.5 w-3.5" />
                            {{ $requiresChoice ? __('menuqr.menu.add') : __('menuqr.menu.quick_add') }}
                        </button>
                    @else
                        <span class="text-[0.7rem] font-semibold text-muted">
                            {{ $item->is_available ? __('menuqr.menu.not_accepting') : __('menuqr.menu.unavailable') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </article>

    <template x-teleport="body">
        <div x-show="open" x-cloak
             class="fixed inset-0 z-[70] flex items-end justify-center sm:items-center sm:p-4"
             role="dialog" aria-modal="true" aria-labelledby="item-dialog-title-{{ $item->id }}">
            <div class="absolute inset-0 bg-charcoal-deep/60" @click="closeDialog"></div>

            <div class="relative flex max-h-[92vh] w-full max-w-lg flex-col overflow-hidden rounded-t-lg bg-paper shadow-2xl sm:rounded-sm" x-ref="dialog">
                <div class="flex items-start justify-between gap-4 border-b border-line p-5">
                    <div>
                        <p class="text-[0.62rem] font-bold uppercase tracking-[0.16em] text-saffron-deep">{{ __('menuqr.menu.choose_options') }}</p>
                        <h2 id="item-dialog-title-{{ $item->id }}" class="mt-1 font-display text-xl font-bold text-charcoal">{{ $item->name() }}</h2>
                        <p class="mt-1 text-sm text-muted"><x-money :minor="$item->price_minor" /></p>
                    </div>
                    <button type="button" class="btn-ghost btn-sm" @click="closeDialog" aria-label="{{ __('menuqr.common.close') }}">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('menu.cart.add', $table->token) }}" class="flex-1 overflow-y-auto p-5">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $item->id }}">

                    <div class="mb-5 flex items-center justify-between gap-4 rounded-sm bg-sand p-3">
                        <span class="text-sm font-bold text-ink-soft">{{ __('menuqr.menu.quantity') }}</span>
                        <div class="flex items-center gap-2">
                            <button type="button" class="flex h-9 w-9 items-center justify-center rounded-sm border border-line-strong bg-paper text-charcoal hover:bg-sand" @click="decrease" aria-label="{{ __('menuqr.common.decrease') }}">
                                <x-icon name="minus" class="h-4 w-4" />
                            </button>
                            <input type="number" name="quantity" min="1" max="99" x-model.number="qty" required
                                   class="tnum w-16 rounded-sm border border-line-strong bg-paper px-2 py-1.5 text-center text-sm font-bold text-charcoal focus:border-saffron-deep focus:outline-none"
                                   aria-label="{{ __('menuqr.menu.quantity') }}">
                            <button type="button" class="flex h-9 w-9 items-center justify-center rounded-sm border border-line-strong bg-paper text-charcoal hover:bg-sand" @click="increase" aria-label="{{ __('menuqr.common.increase') }}">
                                <x-icon name="plus" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    @foreach ($item->optionGroups as $group)
                        @php($availableOptions = $group->options->where('is_available', true))
                        @if ($availableOptions->isNotEmpty())
                            <fieldset class="mb-5">
                                <legend class="flex w-full items-center justify-between gap-2 text-sm font-bold text-charcoal">
                                    <span>{{ $group->name() }}</span>
                                    <span class="rounded-sm px-2 py-0.5 text-[0.6rem] font-bold {{ $group->isRequired() ? 'bg-saffron-soft text-charcoal' : 'bg-charcoal-50 text-muted' }}">
                                        {{ $group->isRequired() ? __('menuqr.menu.required_hint') : __('menuqr.menu.optional_hint') }}
                                    </span>
                                </legend>
                                <p class="mb-2 text-xs text-muted">
                                    @if ($group->max_select === 1)
                                        {{ __('menuqr.menu.select_up_to', ['max' => 1]) }}
                                    @else
                                        {{ __('menuqr.menu.select_between', ['min' => $group->min_select, 'max' => $group->max_select]) }}
                                    @endif
                                </p>

                                <div class="space-y-1.5">
                                    @foreach ($availableOptions as $option)
                                        <label class="flex cursor-pointer items-center justify-between gap-3 rounded-sm border border-line px-3 py-2.5 transition hover:border-charcoal hover:bg-saffron-50/50">
                                            <span class="flex min-w-0 items-center gap-2.5">
                                                <input type="checkbox" name="options[]" value="{{ $option->id }}"
                                                       data-group="{{ $group->id }}"
                                                       x-model="selected"
                                                       data-group="{{ $group->id }}"
                                                       data-max="{{ $group->max_select }}"
                                                       @change="enforce"
                                                       class="h-4 w-4 shrink-0 rounded-sm border-line-strong text-charcoal focus:ring-saffron">
                                                <span class="truncate text-sm font-semibold text-ink-soft">{{ $option->name() }}</span>
                                            </span>
                                            @if ($option->price_delta_minor !== 0)
                                                <span class="tnum shrink-0 text-xs font-bold {{ $option->price_delta_minor > 0 ? 'text-ink-soft' : 'text-olive-deep' }}">
                                                    {{ $option->price_delta_minor > 0 ? '+' : '' }}<x-money :minor="$option->price_delta_minor" />
                                                </span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endif
                    @endforeach

                    <div class="mb-4">
                        <label for="notes-{{ $item->id }}" class="label">{{ __('menuqr.order.notes') }}</label>
                        <textarea id="notes-{{ $item->id }}" name="notes" rows="2" maxlength="200" class="input" placeholder="{{ __('menuqr.menu.notes_placeholder') }}"></textarea>
                    </div>

                    <p x-show="warning" x-text="warning" x-cloak class="mb-3 rounded-sm bg-saffron-soft px-3 py-2 text-xs font-bold text-charcoal"></p>

                    <div class="sticky bottom-0 flex items-center justify-between gap-4 border-t border-line bg-paper pt-4">
                        <div>
                            <p class="text-[0.65rem] font-semibold text-muted">{{ __('menuqr.order.total') }}</p>
                            <p class="tnum font-display text-lg font-bold text-charcoal" x-text="totalLabel"></p>
                        </div>
                        <button type="submit" class="btn-primary">
                            <x-icon name="cart" class="h-4 w-4" />
                            {{ __('menuqr.menu.add_to_cart') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
