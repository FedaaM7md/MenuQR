{{--
    MenuQR - index.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', __('menuqr.staff.title'))

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-charcoal">{{ __('menuqr.staff.title') }}</h2>
            <p class="mt-1 text-sm text-muted">{{ __('menuqr.staff.subtitle') }}</p>
        </div>
        <a href="{{ route('admin.staff.create') }}" class="btn-primary">
            <x-icon name="plus" class="h-4 w-4" />
            {{ __('menuqr.staff.create') }}
        </a>
    </div>

    <div class="card overflow-hidden">
        @if ($staff->isEmpty())
            <div class="px-6 py-14 text-center text-sm font-semibold text-muted">{{ __('menuqr.staff.empty') }}</div>
        @else
            <div class="divide-y divide-line xl:hidden">
                @foreach ($staff as $member)
                    <article class="flex items-start gap-3 p-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-sm {{ $member->isAdmin() ? 'bg-charcoal text-cream' : 'bg-sand text-ink-soft' }} text-sm font-bold">
                            {{ mb_substr($member->name, 0, 1) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-charcoal">{{ $member->name }}
                                @if ($member->id === auth()->id())<span class="badge bg-sand text-muted">{{ __('menuqr.staff.you') }}</span>@endif
                                @if ($member->isDemo())<span class="badge bg-saffron-soft text-charcoal">{{ __('menuqr.common.demo') }}</span>@endif
                            </p>
                            <p class="truncate text-xs text-muted" dir="ltr">{{ $member->email }}</p>
                            <p class="mt-1 flex items-center gap-2 text-xs">
                                <span class="badge {{ $member->isAdmin() ? 'bg-sand text-ink-soft' : ($member->isKitchen() ? 'bg-saffron-soft text-charcoal' : 'bg-olive-soft text-olive-deep') }}">{{ $member->roleLabel() }}</span>
                                <span class="{{ $member->is_active ? 'text-olive-deep' : 'text-tomato' }}">{{ $member->is_active ? __('menuqr.common.enabled') : __('menuqr.common.disabled') }}</span>
                            </p>
                        </div>
                        <a href="{{ route('admin.staff.edit', $member) }}" class="btn-ghost btn-sm" aria-label="{{ __('menuqr.common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                    </article>
                @endforeach
            </div>

            <div class="table-wrap hidden xl:block">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('menuqr.staff.member') }}</th>
                            <th>{{ __('menuqr.staff.role') }}</th>
                            <th>{{ __('menuqr.common.status') }}</th>
                            <th>{{ __('menuqr.staff.email') }}</th>
                            <th class="text-end">{{ __('menuqr.common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($staff as $member)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-full {{ $member->isAdmin() ? 'bg-charcoal text-cream' : 'bg-sand text-ink-soft' }} text-sm font-bold">
                                            {{ mb_substr($member->name, 0, 1) }}
                                        </span>
                                        <div>
                                            <p class="font-bold text-charcoal">
                                                {{ $member->name }}
                                                @if ($member->id === auth()->id())
                                                    <span class="badge bg-sand text-muted">{{ __('menuqr.staff.you') }}</span>
                                                @endif
                                                @if ($member->isDemo())
                                                    <span class="badge bg-saffron-soft text-charcoal">{{ __('menuqr.common.demo') }}</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-muted" dir="ltr">{{ $member->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $member->isAdmin() ? 'bg-sand text-ink-soft' : ($member->isKitchen() ? 'bg-saffron-50 text-charcoal' : 'bg-olive-soft text-olive-deep') }}">
                                        {{ $member->roleLabel() }}
                                    </span>
                                </td>
                                <td>
                                    @if ($member->is_active)
                                        <span class="badge bg-olive-soft text-olive-deep">{{ __('menuqr.common.enabled') }}</span>
                                    @else
                                        <span class="badge bg-charcoal-50 text-ink-soft">{{ __('menuqr.common.disabled') }}</span>
                                    @endif
                                </td>
                                <td class="text-muted" dir="ltr">{{ $member->email }}</td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.staff.edit', $member) }}" class="btn-ghost btn-sm" aria-label="{{ __('menuqr.common.edit') }}">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                        @if ($member->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.staff.destroy', $member) }}"
                                                  data-confirm-submit data-confirm="{{ __('menuqr.staff.delete_confirm') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost btn-sm text-tomato hover:bg-tomato-soft" aria-label="{{ __('menuqr.common.delete') }}">
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        @foreach ([
            ['admin', 'lock', 'bg-sand text-ink-soft'],
            ['cashier', 'orders', 'bg-olive-soft text-olive-deep'],
            ['kitchen', 'kitchen', 'bg-saffron-50 text-charcoal'],
        ] as [$role, $icon, $tone])
            <div class="card flex items-start gap-3 p-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-sm {{ $tone }}"><x-icon :name="$icon" class="h-4 w-4" /></span>
                <div>
                    <p class="text-sm font-bold text-charcoal">{{ __('menuqr.roles.'.$role) }}</p>
                    <p class="mt-0.5 text-xs text-muted">
                        @if ($role === 'admin')
                            {{ __('menuqr.nav.management') }}
                        @elseif ($role === 'cashier')
                            {{ __('menuqr.nav.orders') }}
                        @else
                            {{ __('menuqr.nav.kitchen') }}
                        @endif
                    </p>
                </div>
            </div>
        @endforeach
    </div>
@endsection
