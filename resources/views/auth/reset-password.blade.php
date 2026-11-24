{{-- MenuQR password reset form. Copyright (c) 2026 Fedaa Mohamed. --}}
@extends('layouts.auth')

@section('title', __('menuqr.auth.reset_title'))
@section('heading', __('menuqr.auth.reset_title'))
@section('subheading', __('menuqr.auth.reset_subtitle'))

@section('content')
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="label">{{ __('menuqr.auth.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="username"
                   class="input @error('email') input-invalid @enderror" dir="ltr">
            @error('email')<p class="error-text">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="label">{{ __('menuqr.auth.new_password') }}</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="input @error('password') input-invalid @enderror" dir="ltr">
            @error('password')<p class="error-text">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="label">{{ __('menuqr.auth.confirm_password') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="input" dir="ltr">
        </div>

        <button type="submit" class="btn-primary w-full py-3">{{ __('menuqr.auth.reset_submit') }}</button>
    </form>
@endsection
