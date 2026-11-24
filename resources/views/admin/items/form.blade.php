{{--
    MenuQR - form.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', $item->exists ? __('menuqr.items.edit') : __('menuqr.items.create'))

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
            <div>
                <a href="{{ route('admin.items.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-charcoal">
                    <x-icon name="chevron" class="h-4 w-4 rotate-180 rtl:rotate-0" />
                    {{ __('menuqr.common.back') }}
                </a>
                <h2 class="mt-2 text-xl font-bold text-charcoal">{{ $item->exists ? __('menuqr.items.edit') : __('menuqr.items.create') }}</h2>
            </div>
            @if ($item->exists)
                <a href="{{ route('admin.items.options.index', $item) }}" class="btn-secondary">
                    <x-icon name="settings" class="h-4 w-4" />
                    {{ __('menuqr.items.manage_options') }}
                </a>
            @endif
        </div>

        @if ($categories->isEmpty())
            <div class="flash-error mb-5">
                <x-icon name="alert" class="h-4 w-4 shrink-0" />
                <span>{{ __('menuqr.categories.empty') }} <a href="{{ route('admin.categories.create') }}" class="link">{{ __('menuqr.categories.create') }}</a></span>
            </div>
        @endif

        <form method="POST" action="{{ $item->exists ? route('admin.items.update', $item) : route('admin.items.store') }}" enctype="multipart/form-data" class="grid items-start gap-5 lg:grid-cols-[1.5fr_1fr]">
            @csrf
            @if ($item->exists)
                @method('PUT')
            @endif

            <div class="space-y-5">
                <section class="card-pad">
                    <h3 class="section-title">{{ __('menuqr.items.item') }}</h3>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="name_ar" class="label">{{ __('menuqr.items.name_ar') }}</label>
                            <input id="name_ar" name="name_ar" value="{{ old('name_ar', $item->name_ar) }}" required maxlength="100" class="input @error('name_ar') input-invalid @enderror">
                            @error('name_ar')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="name_en" class="label">{{ __('menuqr.items.name_en') }}</label>
                            <input id="name_en" name="name_en" value="{{ old('name_en', $item->name_en) }}" required maxlength="100" class="input @error('name_en') input-invalid @enderror" dir="ltr">
                            @error('name_en')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="description_ar" class="label">{{ __('menuqr.items.description_ar') }}</label>
                            <textarea id="description_ar" name="description_ar" rows="3" maxlength="600" class="input">{{ old('description_ar', $item->description_ar) }}</textarea>
                        </div>
                        <div>
                            <label for="description_en" class="label">{{ __('menuqr.items.description_en') }}</label>
                            <textarea id="description_en" name="description_en" rows="3" maxlength="600" class="input" dir="ltr">{{ old('description_en', $item->description_en) }}</textarea>
                        </div>
                        <div>
                            <label for="allergens_ar" class="label">{{ __('menuqr.items.allergens_ar') }}</label>
                            <input id="allergens_ar" name="allergens_ar" value="{{ old('allergens_ar', $item->allergens_ar) }}" maxlength="160" class="input" placeholder="{{ __('menuqr.items.allergens_placeholder') }}">
                        </div>
                        <div>
                            <label for="allergens_en" class="label">{{ __('menuqr.items.allergens_en') }}</label>
                            <input id="allergens_en" name="allergens_en" value="{{ old('allergens_en', $item->allergens_en) }}" maxlength="160" class="input" dir="ltr" placeholder="{{ __('menuqr.items.allergens_placeholder') }}">
                        </div>
                    </div>
                </section>

                <section class="card-pad">
                    <h3 class="section-title">{{ __('menuqr.items.price') }}</h3>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="price" class="label">{{ __('menuqr.items.price') }}</label>
                            <input id="price" name="price" type="number" step="0.01" min="0" max="999999" required
                                   value="{{ old('price', $item->exists ? number_format($item->price_minor / 100, 2, '.', '') : '') }}"
                                   class="input @error('price') input-invalid @enderror" dir="ltr" placeholder="45.00">
                            <p class="hint mt-1">{{ __('menuqr.items.price_hint') }}</p>
                            @error('price')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="category_id" class="label">{{ __('menuqr.items.category') }}</label>
                            <select id="category_id" name="category_id" required class="input @error('category_id') input-invalid @enderror">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id) == $category->id)>{{ $category->name() }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>
            </div>

            <div class="space-y-5">
                <section class="card-pad">
                    <h3 class="section-title">{{ __('menuqr.items.image') }}</h3>
                    <div class="mt-4 overflow-hidden rounded-sm bg-sand ring-1 ring-line">
                        @if ($item->image_path)
                            <img src="{{ media_url($item->image_path) }}" alt="{{ __('menuqr.items.current_image') }}" class="aspect-[4/3] w-full object-cover">
                        @else
                            <div class="flex aspect-[4/3] items-center justify-center text-line-strong">
                                <x-icon name="image" class="h-10 w-10" />
                            </div>
                        @endif
                    </div>
                    <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp" class="mt-3 block w-full text-sm text-muted file:me-3 file:rounded-sm file:border-0 file:bg-sand file:px-3 file:py-2 file:text-xs file:font-bold file:text-charcoal hover:file:bg-saffron-soft">
                    <p class="hint mt-1">{{ __('menuqr.items.image_hint') }}</p>
                    @error('image')<p class="error-text">{{ $message }}</p>@enderror
                </section>

                <section class="card-pad space-y-4">
                    <h3 class="section-title">{{ __('menuqr.common.details') }}</h3>
                    <label class="flex cursor-pointer items-center justify-between gap-3 rounded-sm border border-line px-3 py-2.5">
                        <span class="text-sm font-semibold text-ink-soft">{{ __('menuqr.items.availability') }}</span>
                        <input type="checkbox" name="is_available" value="1" @checked(old('is_available', $item->is_available ?? true))
                               class="h-5 w-5 rounded border-line-strong text-saffron-deep focus:ring-saffron">
                    </label>
                    <label class="flex cursor-pointer items-center justify-between gap-3 rounded-sm border border-line px-3 py-2.5">
                        <span class="text-sm font-semibold text-ink-soft">{{ __('menuqr.items.featured') }}</span>
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $item->is_featured ?? false))
                               class="h-5 w-5 rounded border-line-strong text-charcoal focus:ring-saffron">
                    </label>
                    <div>
                        <label for="sort_order" class="label">{{ __('menuqr.items.sort') }}</label>
                        <input id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $item->sort_order ?? 0) }}" class="input" dir="ltr">
                    </div>
                    <p class="hint">{{ __('menuqr.items.currency_note') }}</p>
                </section>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('admin.items.index') }}" class="btn-secondary">{{ __('menuqr.common.cancel') }}</a>
                    <button type="submit" class="btn-primary">
                        <x-icon name="check" class="h-4 w-4" />
                        {{ __('menuqr.common.save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
