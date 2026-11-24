{{--
    MenuQR - index.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', __('menuqr.items.title'))

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-charcoal">{{ __('menuqr.items.title') }}</h2>
            <p class="mt-1 text-sm text-muted">{{ __('menuqr.items.subtitle') }}</p>
        </div>
        <a href="{{ route('admin.items.create') }}" class="btn-primary">
            <x-icon name="plus" class="h-4 w-4" />
            {{ __('menuqr.items.create') }}
        </a>
    </div>

    <div data-sheet class="mb-5">
        <button type="button" class="btn-secondary mb-3 w-full md:hidden" data-sheet-open>
            <x-icon name="search" class="h-4 w-4" />
            {{ __('menuqr.common.filters') }}
        </button>

        <form method="GET" action="{{ route('admin.items.index') }}" class="card-pad hidden md:block">
            @include('admin.partials.item-filters', ['prefix' => 'desktop'])
        </form>

        <div data-sheet-panel class="hidden fixed inset-0 z-[70] md:hidden">
            <div class="absolute inset-0 bg-charcoal-deep/60" data-sheet-close></div>
            <div class="absolute inset-x-0 bottom-0 max-h-[88vh] overflow-y-auto rounded-t-lg bg-paper p-5 shadow-2xl">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-display text-lg font-bold text-charcoal">{{ __('menuqr.common.filters') }}</h2>
                    <button type="button" class="btn-ghost btn-sm" data-sheet-close aria-label="{{ __('menuqr.common.close') }}">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>
                <form method="GET" action="{{ route('admin.items.index') }}" class="mt-4">
                    @include('admin.partials.item-filters', ['prefix' => 'mobile'])
                </form>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        @if ($items->isEmpty())
            <div class="px-6 py-14 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-sand text-muted">
                    <x-icon name="utensils" class="h-6 w-6" />
                </span>
                <p class="mt-4 text-sm font-semibold text-ink-soft">{{ __('menuqr.items.empty') }}</p>
                <a href="{{ route('admin.items.create') }}" class="btn-primary mt-5">{{ __('menuqr.items.create') }}</a>
            </div>
        @else
            <div class="divide-y divide-line xl:hidden">
                @foreach ($items as $item)
                    <article class="p-4">
                        <div class="flex items-start gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-sm bg-sand ring-1 ring-line">
                                @if ($item->image_path)
                                    <img src="{{ media_url($item->image_path) }}" alt="" class="h-full w-full object-cover">
                                @else
                                    <span class="text-sm font-bold text-muted">{{ mb_substr($item->name(), 0, 1) }}</span>
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-charcoal"><x-highlight :text="$item->name_ar" :term="$search" /></p>
                                <p class="truncate text-xs text-muted" dir="ltr"><x-highlight :text="$item->name_en" :term="$search" /></p>
                                <p class="mt-1 text-xs text-muted">{{ $item->category?->name() }}</p>
                            </div>
                            <p class="tnum shrink-0 text-sm font-bold text-charcoal"><x-money :minor="$item->price_minor" /></p>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <form method="POST" action="{{ route('admin.items.toggle', $item) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="badge {{ $item->is_available ? 'bg-olive-soft text-olive-deep' : 'bg-charcoal-50 text-ink-soft' }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $item->is_available ? __('menuqr.common.enabled') : __('menuqr.common.disabled') }}
                                </button>
                            </form>
                            <a href="{{ route('admin.items.options.index', $item) }}" class="badge bg-sand text-ink-soft">{{ $item->option_groups_count }} {{ __('menuqr.options.groups') }}</a>
                            <div class="ms-auto flex items-center gap-1">
                                <a href="{{ route('admin.items.edit', $item) }}" class="btn-ghost btn-sm" aria-label="{{ __('menuqr.common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                                <form method="POST" action="{{ route('admin.items.destroy', $item) }}" data-confirm-submit data-confirm="{{ __('menuqr.common.confirm_delete') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-sm text-tomato" aria-label="{{ __('menuqr.common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="table-wrap hidden xl:block">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('menuqr.items.item') }}</th>
                            <th>{{ __('menuqr.items.category') }}</th>
                            <th>{{ __('menuqr.items.price') }}</th>
                            <th>{{ __('menuqr.items.options') }}</th>
                            <th>{{ __('menuqr.items.availability') }}</th>
                            <th class="text-end">{{ __('menuqr.common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-sm bg-sand ring-1 ring-line">
                                            @if ($item->image_path)
                                                <img src="{{ media_url($item->image_path) }}" alt="" class="h-full w-full object-cover">
                                            @else
                                                <span class="text-sm font-bold text-muted">{{ mb_substr($item->name(), 0, 1) }}</span>
                                            @endif
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-bold text-charcoal"><x-highlight :text="$item->name_ar" :term="$search" /></p>
                                            <p class="truncate text-xs text-muted" dir="ltr"><x-highlight :text="$item->name_en" :term="$search" /></p>
                                            @if ($item->is_featured)
                                                <span class="mt-1 inline-flex items-center gap-1 text-[0.65rem] font-bold text-saffron-deep">
                                                    <x-icon name="sparkles" class="h-3 w-3" />{{ __('menuqr.menu.featured') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item->category?->name() }}</td>
                                <td class="font-semibold"><x-money :minor="$item->price_minor" /></td>
                                <td>
                                    <a href="{{ route('admin.items.options.index', $item) }}" class="badge bg-sand text-ink-soft hover:bg-saffron-soft">
                                        {{ $item->option_groups_count }} {{ __('menuqr.options.groups') }}
                                    </a>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.items.toggle', $item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="badge {{ $item->is_available ? 'bg-olive-soft text-olive-deep' : 'bg-charcoal-50 text-ink-soft' }}" title="{{ __('menuqr.items.availability') }}">
                                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                            {{ $item->is_available ? __('menuqr.common.enabled') : __('menuqr.common.disabled') }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.items.options.index', $item) }}" class="btn-ghost btn-sm" title="{{ __('menuqr.items.manage_options') }}" aria-label="{{ __('menuqr.items.manage_options') }}">
                                            <x-icon name="settings" class="h-4 w-4" />
                                        </a>
                                        <a href="{{ route('admin.items.edit', $item) }}" class="btn-ghost btn-sm" aria-label="{{ __('menuqr.common.edit') }}">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                        <form method="POST" action="{{ route('admin.items.destroy', $item) }}"
                                              data-confirm-submit data-confirm="{{ __('menuqr.common.confirm_delete') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost btn-sm text-tomato hover:bg-tomato-soft" aria-label="{{ __('menuqr.common.delete') }}">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="border-t border-line px-5 py-4">
                    {{ $items->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
