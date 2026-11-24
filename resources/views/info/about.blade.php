{{-- MenuQR about page. Copyright (c) 2026 Fedaa Mohamed. --}}
@extends('layouts.public')

@section('title', __('menuqr.info.about_title'))
@section('meta_description', __('menuqr.info.about_lead'))

@section('content')
    <div class="container-menu py-10 sm:py-14">
        <article class="menu-paper px-6 py-8 sm:px-10 sm:py-10">
            <p class="ornament text-[0.65rem] font-bold uppercase tracking-[0.2em]">{{ __('menuqr.info.eyebrow') }}</p>
            <h1 class="mt-3 font-display text-3xl font-bold text-charcoal sm:text-4xl">{{ __('menuqr.info.about_title') }}</h1>
            <p class="mt-3 text-base leading-8 text-ink-soft">{{ __('menuqr.info.about_lead') }}</p>

            <div class="mt-6 space-y-4 border-t border-line pt-6 text-sm leading-7 text-ink-soft">
                @foreach (__('menuqr.info.about_body') as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>

            <div class="mt-6 rounded-sm bg-sand p-4">
                <h2 class="font-display text-base font-bold text-charcoal">{{ __('menuqr.info.allergens_title') }}</h2>
                <p class="mt-1 text-sm leading-6 text-ink-soft">{{ __('menuqr.info.allergens_body') }}</p>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('home') }}" class="btn-secondary">{{ __('menuqr.info.back_menu') }}</a>
                <a href="{{ route('info.contact') }}" class="btn-ghost">{{ __('menuqr.info.contact_title') }}</a>
            </div>
        </article>
    </div>
@endsection
