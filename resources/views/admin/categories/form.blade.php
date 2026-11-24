{{--
    MenuQR - form.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', $category->exists ? __('menuqr.categories.edit') : __('menuqr.categories.create'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-5">
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-charcoal">
                <x-icon name="chevron" class="h-4 w-4 rotate-180 rtl:rotate-0" />
                {{ __('menuqr.common.back') }}
            </a>
            <h2 class="mt-2 text-xl font-bold text-charcoal">{{ $category->exists ? __('menuqr.categories.edit') : __('menuqr.categories.create') }}</h2>
        </div>

        <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="card-pad space-y-4">
            @csrf
            @if ($category->exists)
                @method('PUT')
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name_ar" class="label">{{ __('menuqr.categories.name_ar') }}</label>
                    <input id="name_ar" name="name_ar" value="{{ old('name_ar', $category->name_ar) }}" required maxlength="80" class="input @error('name_ar') input-invalid @enderror">
                    @error('name_ar')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="name_en" class="label">{{ __('menuqr.categories.name_en') }}</label>
                    <input id="name_en" name="name_en" value="{{ old('name_en', $category->name_en) }}" required maxlength="80" class="input @error('name_en') input-invalid @enderror" dir="ltr">
                    @error('name_en')<p class="error-text">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="sort_order" class="label">{{ __('menuqr.categories.sort') }}</label>
                    <input id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="input" dir="ltr">
                </div>
                <div class="flex items-end pb-2">
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm font-semibold text-ink-soft">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))
                               class="h-4 w-4 rounded border-line-strong text-charcoal focus:ring-saffron">
                        {{ __('menuqr.categories.active') }}
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-line pt-4">
                <a href="{{ route('admin.categories.index') }}" class="btn-secondary">{{ __('menuqr.common.cancel') }}</a>
                <button type="submit" class="btn-primary">
                    <x-icon name="check" class="h-4 w-4" />
                    {{ __('menuqr.common.save') }}
                </button>
            </div>
        </form>
    </div>
@endsection
