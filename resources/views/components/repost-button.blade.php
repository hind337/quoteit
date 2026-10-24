{{--
    Repost button: two arrows going round, with the number of reposts.
    Use it inside a quote card:  <x-repost-button :quote="$quote" />
    Logged in → a button; resources/js/app.js sends the repost ("Reposting a quote").
    Visitor   → a link to the log in page.
    Not shown on your own quotes (you can't repost yourself).
--}}
@props(['quote'])

@php
    $classes = 'flex h-9 items-center gap-1.5 rounded-full px-2 text-sm font-bold transition hover:bg-gold/20';
    $icon = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M4 10V9a3 3 0 0 1 3-3h11l-3-3M20 14v1a3 3 0 0 1-3 3H6l3 3" />
    </svg>';
@endphp

@auth
    <button type="button" data-repost-button data-repost-url="{{ route('quotes.repost', $quote) }}"
        aria-pressed="{{ $quote->reposted_by_visitor ? 'true' : 'false' }}"
        aria-label="{{ $quote->reposted_by_visitor ? 'Undo repost' : 'Repost this quote' }}"
        {{ $attributes->merge(['class' => $classes]) }}>
        {!! $icon !!}
        <span data-repost-count>{{ $quote->reposters_count }}</span>
    </button>
@else
    <a href="{{ route('login') }}" aria-label="Log in to repost this quote" {{ $attributes->merge(['class' => $classes]) }}>
        {!! $icon !!}
        <span>{{ $quote->reposters_count }}</span>
    </a>
@endauth
