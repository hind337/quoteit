@extends('Layout.app')

@section('title', 'Quote by '.$quote->user->name)
@section('content')
    @php
        $isMine = auth()->check() && $quote->user_id === auth()->id();
    @endphp

    <div class="mx-auto max-w-5xl px-4 pt-8 pb-24 sm:px-8">
        <a href="{{ route('home') }}#wall" class="text-sm font-semibold text-gold-ink hover:underline">← Back to the wall</a>

        {{-- The quote, big --}}
        <article data-warmth="{{ App\Models\Quote::warmthFor($quote->likers_count) }}"
            class="quote-other quote-card mt-6 flex flex-col gap-6 p-8 sm:p-12">
            <span class="self-start rounded-full bg-page px-3 py-1 text-xs font-bold tracking-wider text-muted uppercase">{{ $quote->topic }}</span>

            <blockquote class="font-serif text-4xl leading-tight font-medium italic sm:text-5xl">“{{ $quote->text }}”</blockquote>

            <div class="space-y-1">
                <p class="font-semibold">— {{ $isMine ? 'You' : $quote->user->name }} <span class="font-normal text-muted">· {{ $quote->created_at->format('M j, Y') }}</span></p>
                @if ($quote->source_title || $quote->source_author)
                    <p class="text-sm text-muted">
                        <span class="sr-only">Source:</span>
                        From {{ collect([$quote->source_title, $quote->source_author])->filter()->implode(' · ') }}
                    </p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2 border-t border-line pt-5">
                <x-like-button :quote="$quote" class="text-muted aria-pressed:text-gold-ink" />
                @unless ($isMine)
                    <x-repost-button :quote="$quote" class="text-muted aria-pressed:text-gold-ink" />
                @endunless
                <span class="flex-1"></span>
                <a href="{{ route('quotes.wallpaper', $quote) }}" class="btn-primary py-2">
                    <svg class="mr-2 h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="6" y="2" width="12" height="20" rx="2.5" /><path d="M10.5 18.5h3" /></svg>
                    Make it a wallpaper
                </a>
            </div>
        </article>

        {{-- Related quotes: same book, same author, or same topic --}}
        <section class="mt-14">
            <h2 class="font-serif text-3xl font-bold text-gold-ink">Related quotes</h2>
            <p class="mt-1 text-sm text-muted">From the same book or author first, then the same topic.</p>

            <div class="mt-6 grid grid-cols-1 gap-7 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($relatedQuotes as $related)
                    <x-quote-card :quote="$related" />
                @empty
                    <p class="text-muted sm:col-span-2 lg:col-span-3">No related quotes yet. Share one about {{ strtolower($quote->topic) }}!</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
