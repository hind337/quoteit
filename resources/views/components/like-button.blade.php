{{--
    Like button: a small sun with the number of likes.
    Use it inside a quote card:  <x-like-button :quote="$quote" />
    Logged in → a button; resources/js/app.js sends the like ("Warmth: liking a quote").
    Visitor   → a link to the log in page.
--}}
@props(['quote'])

@php
    $classes = 'like-button -ml-2 flex h-9 items-center gap-1.5 rounded-full px-2 text-sm font-bold transition hover:bg-gold/20';
    $icon = '<svg class="h-5 w-5" viewBox="0 0 40 40" aria-hidden="true">
        <circle class="like-sun-core" cx="20" cy="20" r="8" stroke="currentColor" stroke-width="2.5" />
        <g stroke="currentColor" stroke-width="3" stroke-linecap="round">
            <path d="M20 3v5M20 32v5M3 20h5M32 20h5M8 8l3.5 3.5M28.5 28.5 32 32M8 32l3.5-3.5M28.5 11.5 32 8" />
        </g>
    </svg>';
@endphp

@auth
    <button type="button" data-like-button data-like-url="{{ route('quotes.like', $quote) }}"
        aria-pressed="{{ $quote->liked_by_visitor ? 'true' : 'false' }}"
        aria-label="{{ $quote->liked_by_visitor ? 'Remove like' : 'Like this quote' }}"
        {{ $attributes->merge(['class' => $classes]) }}>
        {!! $icon !!}
        <span data-like-count>{{ $quote->likers_count }}</span>
    </button>
@else
    <a href="{{ route('login') }}" aria-label="Log in to like this quote" {{ $attributes->merge(['class' => $classes]) }}>
        {!! $icon !!}
        <span>{{ $quote->likers_count }}</span>
    </a>
@endauth
