{{--
    MenuQR - form.blade.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@extends('layouts.app')

@section('title', $member->exists ? __('menuqr.staff.edit') : __('menuqr.staff.create'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-5">
            <a href="{{ route('admin.staff.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-charcoal">
                <x-icon name="chevron" class="h-4 w-4 rotate-180 rtl:rotate-0" />
                {{ __('menuqr.common.back') }}
            </a>
            <h2 class="mt-2 text-xl font-bold text-charcoal">{{ $member->exists ? __('menuqr.staff.edit') : __('menuqr.staff.create') }}</h2>
        </div>

        <form method="POST" action="{{ $member->exists ? route('admin.staff.update', $member) : route('admin.staff.store') }}" class="card-pad space-y-4">
            @csrf
            @if ($member->exists)
                @method('PUT')
            @endif

            @if ($member->isDemo())
                <input type="hidden" name="email" value="{{ $member->email }}">
                <input type="hidden" name="role" value="{{ $member->role }}">
                <input type="hidden" name="is_active" value="1">
                <div class="rounded-sm border border-saffron/40 bg-saffron-50 px-4 py-3 text-sm font-semibold text-charcoal">
                    {{ __('menuqr.staff.demo_locked') }}
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="label">{{ __('menuqr.staff.name') }}</label>
                    <input id="name" name="name" value="{{ old('name', $member->name) }}" required maxlength="80" class="input @error('name') input-invalid @enderror">
                    @error('name')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="label">{{ __('menuqr.staff.email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $member->email) }}" required maxlength="120" class="input @error('email') input-invalid @enderror" dir="ltr" @disabled($member->isDemo())>
                    @error('email')<p class="error-text">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="role" class="label">{{ __('menuqr.staff.role') }}</label>
                    <select id="role" name="role" required class="input @error('role') input-invalid @enderror" @disabled($member->isDemo())>
                        @foreach (\App\Models\User::ROLES as $role)
                            <option value="{{ $role }}" @selected(old('role', $member->role) === $role)>{{ __('menuqr.roles.'.$role) }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="locale" class="label">{{ __('menuqr.common.language') }}</label>
                    <select id="locale" name="locale" class="input">
                        <option value="">{{ __('menuqr.common.all') }}</option>
                        <option value="ar" @selected(old('locale', $member->locale) === 'ar')>{{ __('menuqr.common.arabic') }}</option>
                        <option value="en" @selected(old('locale', $member->locale) === 'en')>{{ __('menuqr.common.english') }}</option>
                    </select>
                </div>
            </div>

            @unless ($member->isDemo())
                <div>
                    <label for="password" class="label">{{ __('menuqr.staff.password') }}</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" class="input @error('password') input-invalid @enderror" dir="ltr">
                    <p class="hint mt-1">{{ __('menuqr.staff.password_hint') }}</p>
                    @error('password')<p class="error-text">{{ $message }}</p>@enderror
                </div>
            @endunless

            <label class="flex cursor-pointer items-center justify-between gap-3 rounded-sm border border-line px-4 py-3">
                <span class="text-sm font-bold text-ink-soft">{{ __('menuqr.staff.active') }}</span>
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $member->is_active ?? true)) @disabled($member->isDemo())
                       class="h-5 w-5 rounded-sm border-line-strong text-saffron-deep focus:ring-saffron">
            </label>

            <div class="flex items-center justify-end gap-3 border-t border-line pt-4">
                <a href="{{ route('admin.staff.index') }}" class="btn-secondary">{{ __('menuqr.common.cancel') }}</a>
                <button type="submit" class="btn-primary">
                    <x-icon name="check" class="h-4 w-4" />
                    {{ __('menuqr.common.save') }}
                </button>
            </div>
        </form>
    </div>
@endsection
