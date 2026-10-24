{{--
    One quote on the wall.
    Your own quotes are ember orange and have edit / delete buttons;
    everyone else's are paper cards that warm up with likes.
    Clicking the quote opens its own page (with related quotes).
--}}
@props(['quote'])

@php
    $isMine = auth()->check() && $quote->user_id === auth()->id();
    $latestReposter = $quote->reposters->first();
@endphp

<article data-topic="{{ $quote->topic }}" data-mine="{{ $isMine ? '1' : '0' }}"
    data-warmth="{{ App\Models\Quote::warmthFor($quote->likers_count) }}"
    class="{{ $isMine ? 'quote-mine' : 'quote-other' }} quote-card flex min-h-44 flex-col gap-4">

    {{-- Why this card is here: a repost, or (on a quote's page) why it's related --}}
    @if ($quote->related_because)
        <span class="self-start rounded-full bg-gold px-2.5 py-0.5 text-[11px] font-bold tracking-wider text-dusk uppercase">
            {{ $quote->related_because }}
        </span>
    @elseif ($latestReposter)
        <p class="flex items-center gap-1.5 text-xs font-semibold {{ $isMine ? 'text-on-ember/90' : 'text-gold-ink' }}">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10V9a3 3 0 0 1 3-3h11l-3-3M20 14v1a3 3 0 0 1-3 3H6l3 3" /></svg>
            {{ $latestReposter->is(auth()->user()) ? 'You' : $latestReposter->name }} reposted{{ $quote->reposters_count > 1 ? ' · and '.($quote->reposters_count - 1).' more' : '' }}
        </p>
    @endif

    <div class="flex-1">
        {{-- Topic and the date it was shared --}}
        <p class="mb-2 text-[11px] font-bold tracking-wider uppercase {{ $isMine ? 'text-on-ember/85' : 'text-muted' }}">
            {{ $quote->topic }} · <time datetime="{{ $quote->created_at->toDateString() }}">{{ $quote->created_at->format('M j, Y') }}</time>
        </p>

        <a href="{{ route('quotes.show', $quote) }}" class="quote-text block hover:underline hover:decoration-gold hover:decoration-1 hover:underline-offset-4">
            “{{ $quote->text }}”
        </a>

        {{-- Source: only shown when the person added a book or an author --}}
        @if ($quote->source_title || $quote->source_author)
            <p class="mt-3 flex items-center gap-1.5 text-xs {{ $isMine ? 'text-on-ember/85' : 'text-muted' }}">
                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 6.04A8.97 8.97 0 0 0 6 3.75c-1.05 0-2.06.18-3 .51v14.25A8.99 8.99 0 0 1 6 18c2.3 0 4.4.87 6 2.29m0-14.25a8.97 8.97 0 0 1 6-2.29c1.05 0 2.06.18 3 .51v14.25A8.99 8.99 0 0 0 18 18a8.97 8.97 0 0 0-6 2.29m0-14.25v14.25" />
                </svg>
                <span class="sr-only">Source:</span>
                {{ collect([$quote->source_title, $quote->source_author])->filter()->implode(' · ') }}
            </p>
        @endif
    </div>

    <div class="flex flex-wrap items-center gap-1 text-sm">
        <x-like-button :quote="$quote" class="{{ $isMine ? 'aria-pressed:text-gold-light' : 'text-muted aria-pressed:text-gold-ink' }}" />

        @unless ($isMine)
            <x-repost-button :quote="$quote" class="text-muted aria-pressed:text-gold-ink" />
        @endunless

        <a href="{{ route('quotes.wallpaper', $quote) }}" aria-label="Make a phone wallpaper from this quote"
            class="flex h-9 w-9 items-center justify-center rounded-full transition hover:bg-gold/20 {{ $isMine ? '' : 'text-muted' }}">
            <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="6" y="2" width="12" height="20" rx="2.5" /><path d="M10.5 18.5h3" /></svg>
        </a>

        <span class="flex-1"></span>

        @if ($isMine)
            <span class="font-semibold">— You</span>
            <a href="{{ route('home', ['edit' => $quote->id]) }}#quote-{{ $quote->id }}" aria-label="Edit quote"
                class="ml-1 rounded-full p-2 hover:bg-on-ember/15">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16.9 4.5 2.6 2.6M18.2 3.2a1.9 1.9 0 0 1 2.6 2.6L7.5 19.2l-3.8 1.1 1.1-3.8L18.2 3.2Z" /></svg>
            </a>
            <form method="POST" action="{{ route('quotes.destroy', $quote) }}">
                @csrf
                @method('DELETE')
                <button type="submit" aria-label="Delete quote" class="rounded-full p-2 hover:bg-on-ember/15">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M9 7V4.5h6V7M6.5 7l1 13h9l1-13" /></svg>
                </button>
            </form>
        @else
            <span class="font-semibold">— {{ $quote->user->name }}</span>
        @endif
    </div>
</article>
