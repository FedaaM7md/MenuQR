{{--
    MenuQR - options.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', __('menuqr.options.title', ['item' => $item->name()]))

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
            <div>
                <a href="{{ route('admin.items.edit', $item) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-charcoal">
                    <x-icon name="chevron" class="h-4 w-4 rotate-180 rtl:rotate-0" />
                    {{ __('menuqr.options.back_to_item') }}
                </a>
                <h2 class="mt-2 text-xl font-bold text-charcoal">{{ __('menuqr.options.title', ['item' => $item->name()]) }}</h2>
                <p class="mt-1 text-sm text-muted">{{ __('menuqr.options.subtitle') }}</p>
            </div>
            <span class="badge bg-sand text-ink-soft">{{ $item->category?->name() }}</span>
        </div>

        <section class="card-pad mb-5">
            <h3 class="section-title">{{ __('menuqr.options.add_group') }}</h3>
            <form method="POST" action="{{ route('admin.option-groups.store', $item) }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_90px_90px_90px_auto] lg:items-end">
                @csrf
                <div>
                    <label for="new-group-name-ar" class="label">{{ __('menuqr.options.group_name_ar') }}</label>
                    <input id="new-group-name-ar" name="name_ar" required maxlength="80" class="input" placeholder="{{ __('menuqr.options.group') }}">
                </div>
                <div>
                    <label for="new-group-name-en" class="label">{{ __('menuqr.options.group_name_en') }}</label>
                    <input id="new-group-name-en" name="name_en" required maxlength="80" class="input" dir="ltr" placeholder="Size">
                </div>
                <div>
                    <label for="new-group-min" class="label">{{ __('menuqr.options.min') }}</label>
                    <input id="new-group-min" name="min_select" type="number" min="0" max="10" value="0" required class="input" dir="ltr">
                </div>
                <div>
                    <label for="new-group-max" class="label">{{ __('menuqr.options.max') }}</label>
                    <input id="new-group-max" name="max_select" type="number" min="1" max="10" value="1" required class="input" dir="ltr">
                </div>
                <div>
                    <label for="new-group-sort" class="label">{{ __('menuqr.options.sort') }}</label>
                    <input id="new-group-sort" name="sort_order" type="number" min="0" max="9999" value="{{ $item->optionGroups->count() }}" class="input" dir="ltr">
                </div>
                <button type="submit" class="btn-primary">
                    <x-icon name="plus" class="h-4 w-4" />
                    {{ __('menuqr.common.create') }}
                </button>
            </form>
        </section>

        @if ($item->optionGroups->isEmpty())
            <div class="card-pad text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-sand text-muted">
                    <x-icon name="settings" class="h-6 w-6" />
                </span>
                <p class="mt-4 text-sm font-semibold text-ink-soft">{{ __('menuqr.options.no_groups') }}</p>
            </div>
        @endif

        <div class="space-y-4">
            @foreach ($item->optionGroups as $group)
                <section class="card overflow-hidden" data-editor>
                    <div class="flex flex-wrap items-center gap-3 border-b border-line px-5 py-4">
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-charcoal">{{ $group->name() }}</h3>
                            <p class="mt-0.5 text-xs text-muted">
                                {{ __('menuqr.options.min') }} {{ $group->min_select }} · {{ __('menuqr.options.max') }} {{ $group->max_select }}
                                @if ($group->isRequired())
                                    · <span class="font-bold text-saffron-deep">{{ __('menuqr.menu.required_hint') }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="ms-auto flex items-center gap-1">
                            <button type="button" class="btn-secondary btn-sm" data-editor-toggle>
                                <x-icon name="edit" class="h-4 w-4" />
                                {{ __('menuqr.common.edit') }}
                            </button>
                            <form method="POST" action="{{ route('admin.option-groups.destroy', [$item, $group]) }}"
                                  data-confirm-submit data-confirm="{{ __('menuqr.options.delete_group_confirm') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ghost btn-sm text-tomato hover:bg-tomato-soft" aria-label="{{ __('menuqr.common.delete') }}">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </form>
                        </div>
                    </div>

                    <div data-editor-panel class="hidden border-b border-line bg-sand px-5 py-4">
                        <form method="POST" action="{{ route('admin.option-groups.update', [$item, $group]) }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_90px_90px_90px_auto] lg:items-end">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="group-{{ $group->id }}-name-ar" class="label">{{ __('menuqr.options.group_name_ar') }}</label>
                                <input id="group-{{ $group->id }}-name-ar" name="name_ar" value="{{ $group->name_ar }}" required maxlength="80" class="input">
                            </div>
                            <div>
                                <label for="group-{{ $group->id }}-name-en" class="label">{{ __('menuqr.options.group_name_en') }}</label>
                                <input id="group-{{ $group->id }}-name-en" name="name_en" value="{{ $group->name_en }}" required maxlength="80" class="input" dir="ltr">
                            </div>
                            <div>
                                <label for="group-{{ $group->id }}-min" class="label">{{ __('menuqr.options.min') }}</label>
                                <input id="group-{{ $group->id }}-min" name="min_select" type="number" min="0" max="10" value="{{ $group->min_select }}" required class="input" dir="ltr">
                            </div>
                            <div>
                                <label for="group-{{ $group->id }}-max" class="label">{{ __('menuqr.options.max') }}</label>
                                <input id="group-{{ $group->id }}-max" name="max_select" type="number" min="1" max="10" value="{{ $group->max_select }}" required class="input" dir="ltr">
                            </div>
                            <div>
                                <label for="group-{{ $group->id }}-sort" class="label">{{ __('menuqr.options.sort') }}</label>
                                <input id="group-{{ $group->id }}-sort" name="sort_order" type="number" min="0" max="9999" value="{{ $group->sort_order }}" class="input" dir="ltr">
                            </div>
                            <button type="submit" class="btn-primary">{{ __('menuqr.common.save') }}</button>
                        </form>
                    </div>

                    <div class="p-5">
                        @if ($group->options->isEmpty())
                            <p class="text-sm text-muted">{{ __('menuqr.options.no_options') }}</p>
                        @endif

                        <div class="space-y-2">
                            @foreach ($group->options as $option)
                                <div class="rounded-sm border border-line p-3">
                                    <form method="POST" action="{{ route('admin.options.update', [$item, $group, $option]) }}" class="grid items-end gap-2 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_120px_80px_auto_auto]">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label for="option-{{ $option->id }}-name-ar" class="sr-only">{{ __('menuqr.options.name_ar') }}</label>
                                            <input id="option-{{ $option->id }}-name-ar" name="name_ar" value="{{ $option->name_ar }}" required maxlength="80" class="input py-2 text-sm">
                                        </div>
                                        <div>
                                            <label for="option-{{ $option->id }}-name-en" class="sr-only">{{ __('menuqr.options.name_en') }}</label>
                                            <input id="option-{{ $option->id }}-name-en" name="name_en" value="{{ $option->name_en }}" required maxlength="80" class="input py-2 text-sm" dir="ltr">
                                        </div>
                                        <div>
                                            <label for="option-{{ $option->id }}-delta" class="sr-only">{{ __('menuqr.options.delta') }}</label>
                                            <input id="option-{{ $option->id }}-delta" name="price_delta" type="number" step="0.01" value="{{ number_format($option->price_delta_minor / 100, 2, '.', '') }}" required class="input py-2 text-sm" dir="ltr" title="{{ __('menuqr.options.delta_hint') }}">
                                        </div>
                                        <div>
                                            <label for="option-{{ $option->id }}-sort" class="sr-only">{{ __('menuqr.options.sort') }}</label>
                                            <input id="option-{{ $option->id }}-sort" name="sort_order" type="number" min="0" max="9999" value="{{ $option->sort_order }}" class="input py-2 text-sm" dir="ltr">
                                        </div>
                                        <label class="flex items-center gap-2 whitespace-nowrap py-2 text-xs font-semibold text-ink-soft">
                                            <input type="checkbox" name="is_available" value="1" @checked($option->is_available) class="h-4 w-4 rounded border-line-strong text-saffron-deep focus:ring-saffron">
                                            {{ __('menuqr.options.available') }}
                                        </label>
                                        <button type="submit" class="btn-secondary btn-sm py-2">{{ __('menuqr.common.save') }}</button>
                                    </form>
                                    <div class="mt-2 flex items-center justify-between">
                                        <p class="text-[0.7rem] text-muted">{{ __('menuqr.options.delta') }}: <x-money :minor="$option->price_delta_minor" /></p>
                                        <form method="POST" action="{{ route('admin.options.destroy', [$item, $group, $option]) }}"
                                              data-confirm-submit data-confirm="{{ __('menuqr.common.confirm_delete') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost btn-sm text-tomato hover:bg-tomato-soft">
                                                <x-icon name="trash" class="h-4 w-4" />
                                                {{ __('menuqr.common.delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <form method="POST" action="{{ route('admin.options.store', [$item, $group]) }}" class="mt-4 grid items-end gap-2 rounded-sm bg-sand p-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_120px_80px_auto]">
                            @csrf
                            <div>
                                <label for="new-option-{{ $group->id }}-name-ar" class="label">{{ __('menuqr.options.name_ar') }}</label>
                                <input id="new-option-{{ $group->id }}-name-ar" name="name_ar" required maxlength="80" class="input py-2 text-sm" placeholder="{{ __('menuqr.options.add_option') }}">
                            </div>
                            <div>
                                <label for="new-option-{{ $group->id }}-name-en" class="label">{{ __('menuqr.options.name_en') }}</label>
                                <input id="new-option-{{ $group->id }}-name-en" name="name_en" required maxlength="80" class="input py-2 text-sm" dir="ltr" placeholder="Large">
                            </div>
                            <div>
                                <label for="new-option-{{ $group->id }}-delta" class="label">{{ __('menuqr.options.delta') }}</label>
                                <input id="new-option-{{ $group->id }}-delta" name="price_delta" type="number" step="0.01" value="0.00" required class="input py-2 text-sm" dir="ltr">
                            </div>
                            <div>
                                <label for="new-option-{{ $group->id }}-sort" class="label">{{ __('menuqr.options.sort') }}</label>
                                <input id="new-option-{{ $group->id }}-sort" name="sort_order" type="number" min="0" max="9999" value="{{ $group->options->count() }}" class="input py-2 text-sm" dir="ltr">
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="is_available" value="1">
                                <button type="submit" class="btn-primary btn-sm py-2">
                                    <x-icon name="plus" class="h-4 w-4" />
                                    {{ __('menuqr.options.add_option') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            @endforeach
        </div>

        <p class="mt-5 rounded-sm bg-sand px-4 py-3 text-xs text-muted">{{ __('menuqr.options.public_hint') }}</p>
    </div>
@endsection
