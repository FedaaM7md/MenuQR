{{--
    MenuQR - index.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', __('menuqr.categories.title'))

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-charcoal">{{ __('menuqr.categories.title') }}</h2>
            <p class="mt-1 text-sm text-muted">{{ __('menuqr.categories.subtitle') }}</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn-primary">
            <x-icon name="plus" class="h-4 w-4" />
            {{ __('menuqr.categories.create') }}
        </a>
    </div>

    <div class="card overflow-hidden">
        @if ($categories->isEmpty())
            <div class="px-6 py-14 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-sand text-muted">
                    <x-icon name="tag" class="h-6 w-6" />
                </span>
                <p class="mt-4 text-sm font-semibold text-ink-soft">{{ __('menuqr.categories.empty') }}</p>
                <a href="{{ route('admin.categories.create') }}" class="btn-primary mt-5">{{ __('menuqr.categories.create') }}</a>
            </div>
        @else
            <div class="divide-y divide-line xl:hidden">
                @foreach ($categories as $category)
                    <article class="flex items-start gap-3 p-4">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-charcoal">{{ $category->name_ar }}</p>
                            <p class="truncate text-xs text-muted" dir="ltr">{{ $category->name_en }}</p>
                            <p class="mt-1 text-xs text-muted">{{ $category->items_count }} {{ __('menuqr.categories.items_count') }} · {{ $category->sort_order }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-1">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn-ghost btn-sm" aria-label="{{ __('menuqr.common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm-submit data-confirm="{{ __('menuqr.common.confirm_delete') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-ghost btn-sm text-tomato" aria-label="{{ __('menuqr.common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="table-wrap hidden xl:block">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('menuqr.categories.name_ar') }}</th>
                            <th>{{ __('menuqr.categories.name_en') }}</th>
                            <th>{{ __('menuqr.categories.items_count') }}</th>
                            <th>{{ __('menuqr.categories.sort') }}</th>
                            <th>{{ __('menuqr.categories.active') }}</th>
                            <th class="text-end">{{ __('menuqr.common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td class="font-bold text-charcoal">{{ $category->name_ar }}</td>
                                <td dir="ltr">{{ $category->name_en }}</td>
                                <td><span class="badge bg-sand text-ink-soft">{{ $category->items_count }}</span></td>
                                <td>{{ $category->sort_order }}</td>
                                <td>
                                    @if ($category->is_active)
                                        <span class="badge bg-olive-soft text-olive-deep">{{ __('menuqr.common.enabled') }}</span>
                                    @else
                                        <span class="badge bg-charcoal-50 text-ink-soft">{{ __('menuqr.common.disabled') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn-ghost btn-sm" aria-label="{{ __('menuqr.common.edit') }}">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
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
        @endif
    </div>
@endsection
