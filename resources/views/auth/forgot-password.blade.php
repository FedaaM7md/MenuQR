{{-- MenuQR password reset request. Copyright (c) 2026 Fedaa Mohamed. --}}
@extends('layouts.auth')

@section('title', __('menuqr.auth.forgot_title'))
@section('heading', __('menuqr.auth.forgot_title'))
@section('subheading', __('menuqr.auth.forgot_subtitle'))

@section('content')
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="label">{{ __('menuqr.auth.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="input @error('email') input-invalid @enderror" dir="ltr">
            @error('email')<p class="error-text">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="btn-primary w-full py-3">{{ __('menuqr.auth.send_link') }}</button>
    </form>

    <p class="mt-6 text-sm">
        <a href="{{ route('login') }}" class="link">{{ __('menuqr.auth.back_login') }}</a>
    </p>
@endsection
