{{--
    MenuQR safe search highlighting.
    Copyright (c) 2026 Fedaa Mohamed. All rights reserved.
--}}
@props(['text' => '', 'term' => '', 'class' => ''])

<span {{ $attributes->merge(['class' => $class]) }}>
    @if ($term === '')
        {{ $text }}
    @else
        @foreach (preg_split('/('.preg_quote($term, '/').')/iu', (string) $text, -1, PREG_SPLIT_DELIM_CAPTURE) as $part)
            @if (mb_strtolower($part) === mb_strtolower($term))
                <mark class="rounded-sm bg-saffron-soft px-0.5 text-charcoal">{{ $part }}</mark>
            @else
                {{ $part }}
            @endif
        @endforeach
    @endif
</span>
