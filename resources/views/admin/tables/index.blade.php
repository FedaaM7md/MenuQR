{{--
    MenuQR - index.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', __('menuqr.tables.title'))

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-charcoal">{{ __('menuqr.tables.title') }}</h2>
            <p class="mt-1 text-sm text-muted">{{ __('menuqr.tables.subtitle') }}</p>
        </div>
        <a href="{{ route('admin.tables.print') }}" target="_blank" rel="noopener" class="btn-secondary">
            <x-icon name="print" class="h-4 w-4" />
            {{ __('menuqr.tables.print_sheet') }}
        </a>
    </div>

    <section class="card-pad mb-5">
        <h3 class="section-title">{{ __('menuqr.tables.create') }}</h3>
        <form method="POST" action="{{ route('admin.tables.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-[1.2fr_110px_110px_auto_auto] lg:items-end">
            @csrf
            <div>
                <label for="table-name" class="label">{{ __('menuqr.tables.name') }}</label>
                <input id="table-name" name="name" required maxlength="40" class="input @error('name') input-invalid @enderror" placeholder="T1">
                @error('name')<p class="error-text">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="table-seats" class="label">{{ __('menuqr.tables.seats') }}</label>
                <input id="table-seats" name="seats" type="number" min="1" max="30" value="2" required class="input" dir="ltr">
            </div>
            <div>
                <label for="table-sort" class="label">{{ __('menuqr.tables.sort') }}</label>
                <input id="table-sort" name="sort_order" type="number" min="0" max="9999" value="{{ $tables->count() }}" class="input" dir="ltr">
            </div>
            <label class="flex items-center gap-2 pb-3 text-sm font-semibold text-ink-soft">
                <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 rounded border-line-strong text-saffron-deep focus:ring-saffron">
                {{ __('menuqr.tables.active') }}
            </label>
            <button type="submit" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                {{ __('menuqr.common.create') }}
            </button>
        </form>
    </section>

    @if ($tables->isEmpty())
        <div class="card-pad text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-sand text-muted">
                <x-icon name="qr" class="h-6 w-6" />
            </span>
            <p class="mt-4 text-sm font-semibold text-ink-soft">{{ __('menuqr.tables.empty') }}</p>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($tables as $table)
                <article class="card overflow-hidden" data-editor data-copy-url="{{ $table->publicUrl() }}">
                    <div class="flex items-start gap-4 p-5">
                        <div class="qr-frame h-28 w-28 shrink-0">
                            <img src="{{ $qrCodes[$table->id] }}" alt="{{ __('menuqr.tables.qr').' '.$table->name }}" class="h-full w-full">
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="truncate text-lg font-bold text-charcoal">{{ $table->name }}</h3>
                                @if ($table->is_active)
                                    <span class="badge bg-olive-soft text-olive-deep">{{ __('menuqr.common.enabled') }}</span>
                                @else
                                    <span class="badge bg-charcoal-50 text-ink-soft">{{ __('menuqr.common.disabled') }}</span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-xs font-semibold text-muted">{{ __('menuqr.tables.seats') }}: {{ $table->seats }}</p>
                            <p class="mt-2 truncate rounded-sm bg-sand px-2 py-1 text-[0.7rem] text-muted" dir="ltr" title="{{ $table->publicUrl() }}">{{ $table->publicUrl() }}</p>
                            <button type="button" class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-saffron-deep hover:text-olive-deep" data-copy>
                                <x-icon name="copy" class="h-3.5 w-3.5" />
                                <span data-copy-label data-copy-default="{{ __('menuqr.tables.copy_url') }}" data-copy-copied="{{ __('menuqr.tables.copied') }}">{{ __('menuqr.tables.copy_url') }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 border-t border-line px-5 py-3">
                        <a href="{{ $table->publicUrl() }}" target="_blank" rel="noopener" class="btn-secondary btn-sm">
                            <x-icon name="external" class="h-3.5 w-3.5" />
                            {{ __('menuqr.tables.open') }}
                        </a>
                        <a href="{{ route('admin.tables.qr', $table) }}" target="_blank" rel="noopener" class="btn-ghost btn-sm">
                            <x-icon name="download" class="h-3.5 w-3.5" />
                            {{ __('menuqr.tables.svg') }}
                        </a>
                        <button type="button" class="btn-ghost btn-sm" data-editor-toggle>
                            <x-icon name="edit" class="h-3.5 w-3.5" />
                            {{ __('menuqr.common.edit') }}
                        </button>
                        <form method="POST" action="{{ route('admin.tables.regenerate', $table) }}" class="ms-auto"
                              data-confirm-submit data-confirm="{{ __('menuqr.tables.regenerate_confirm') }}">
                            @csrf
                            <button type="submit" class="btn-ghost btn-sm text-saffron-deep hover:bg-saffron-50" title="{{ __('menuqr.tables.regenerate') }}">
                                <x-icon name="refresh" class="h-3.5 w-3.5" />
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.tables.destroy', $table) }}"
                              data-confirm-submit data-confirm="{{ __('menuqr.common.confirm_delete') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-ghost btn-sm text-tomato hover:bg-tomato-soft" aria-label="{{ __('menuqr.common.delete') }}">
                                <x-icon name="trash" class="h-3.5 w-3.5" />
                            </button>
                        </form>
                    </div>

                    <div data-editor-panel class="hidden border-t border-line bg-sand p-5">
                        <form method="POST" action="{{ route('admin.tables.update', $table) }}" class="grid gap-3 sm:grid-cols-2">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="edit-{{ $table->id }}-name" class="label">{{ __('menuqr.tables.name') }}</label>
                                <input id="edit-{{ $table->id }}-name" name="name" value="{{ $table->name }}" required maxlength="40" class="input">
                            </div>
                            <div>
                                <label for="edit-{{ $table->id }}-seats" class="label">{{ __('menuqr.tables.seats') }}</label>
                                <input id="edit-{{ $table->id }}-seats" name="seats" type="number" min="1" max="30" value="{{ $table->seats }}" required class="input" dir="ltr">
                            </div>
                            <div>
                                <label for="edit-{{ $table->id }}-sort" class="label">{{ __('menuqr.tables.sort') }}</label>
                                <input id="edit-{{ $table->id }}-sort" name="sort_order" type="number" min="0" max="9999" value="{{ $table->sort_order }}" class="input" dir="ltr">
                            </div>
                            <div class="flex items-end gap-3">
                                <label class="flex items-center gap-2 pb-2 text-sm font-semibold text-ink-soft">
                                    <input type="checkbox" name="is_active" value="1" @checked($table->is_active) class="h-4 w-4 rounded border-line-strong text-saffron-deep focus:ring-saffron">
                                    {{ __('menuqr.tables.active') }}
                                </label>
                                <button type="submit" class="btn-primary btn-sm ms-auto">{{ __('menuqr.common.save') }}</button>
                            </div>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <p class="mt-5 rounded-sm bg-sand px-4 py-3 text-xs text-muted">{{ __('menuqr.tables.print_note') }}</p>
@endsection
