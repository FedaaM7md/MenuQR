{{-- MenuQR contact page. Copyright (c) 2026 Fedaa Mohamed. --}}
@extends('layouts.public')

@section('title', __('menuqr.info.contact_title'))
@section('meta_description', __('menuqr.info.contact_lead'))

@section('content')
    <div class="container-menu py-10 sm:py-14">
        <article class="menu-paper px-6 py-8 sm:px-10 sm:py-10">
            <p class="ornament text-[0.65rem] font-bold uppercase tracking-[0.2em]">{{ __('menuqr.info.eyebrow') }}</p>
            <h1 class="mt-3 font-display text-3xl font-bold text-charcoal sm:text-4xl">{{ __('menuqr.info.contact_title') }}</h1>
            <p class="mt-3 text-base leading-8 text-ink-soft">{{ __('menuqr.info.contact_lead') }}</p>

            <dl class="mt-6 grid gap-4 border-t border-line pt-6 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-[0.65rem] font-bold uppercase tracking-[0.16em] text-muted">{{ __('menuqr.order.phone') }}</dt>
                    <dd class="mt-1 font-bold text-charcoal"><a href="tel:{{ __('menuqr.info.phone') }}" class="tnum hover:underline" dir="ltr">{{ __('menuqr.info.phone') }}</a></dd>
                </div>
                <div>
                    <dt class="text-[0.65rem] font-bold uppercase tracking-[0.16em] text-muted">{{ __('menuqr.auth.email') }}</dt>
                    <dd class="mt-1 font-bold text-charcoal"><a href="mailto:{{ __('menuqr.info.email') }}" class="hover:underline" dir="ltr">{{ __('menuqr.info.email') }}</a></dd>
                </div>
                <div>
                    <dt class="text-[0.65rem] font-bold uppercase tracking-[0.16em] text-muted">{{ __('menuqr.menu.address') }}</dt>
                    <dd class="mt-1 text-ink-soft">{{ $settings->address() }}</dd>
                </div>
                <div>
                    <dt class="text-[0.65rem] font-bold uppercase tracking-[0.16em] text-muted">{{ __('menuqr.info.hours_title') }}</dt>
                    <dd class="mt-1 text-ink-soft">{{ __('menuqr.info.hours_note') }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('home') }}" class="btn-secondary">{{ __('menuqr.info.back_menu') }}</a>
                <a href="https://wa.me/201000000000" target="_blank" rel="noopener noreferrer" class="btn-accent">
                    <x-icon name="whatsapp" class="h-4 w-4" />
                    {{ __('menuqr.info.whatsapp') }}
                </a>
            </div>
        </article>
    </div>
@endsection
