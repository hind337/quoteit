@extends('Layout.app')

@section('title', 'Home')
@section('content')
    {{-- ============ Quote of the day, under the sun's path ============ --}}
    <header class="relative overflow-hidden">
        {{-- The sun's path: a dotted arc from sunrise (left) to sunset (right).
             resources/js/app.js moves the sun dot along it with the real clock. --}}
        <svg class="pointer-events-none absolute inset-0 h-full w-full" viewBox="0 0 1280 470"
            preserveAspectRatio="xMidYMax meet" aria-hidden="true">
            <path d="M200 400 A440 273 0 0 1 1080 400" fill="none" stroke="var(--arc)" stroke-width="1.5"
                stroke-dasharray="2 8" stroke-linecap="round" />
            <line x1="120" y1="400" x2="1160" y2="400" stroke="var(--line)" />
            <circle class="sun-dot sun-dot-glow" data-sun-dot cx="400" cy="200" r="44" />
            <circle class="sun-dot sun-dot-core" data-sun-dot cx="400" cy="200" r="16" />
        </svg>

        <div class="relative mx-auto flex min-h-[340px] max-w-3xl flex-col items-center px-6 pt-16 pb-28 text-center sm:min-h-[470px] sm:pt-44 sm:pb-24">
            <p class="text-xs font-bold tracking-[0.25em] text-gold-ink">QUOTE OF THE DAY</p>

            @if ($quoteOfTheDay)
                <blockquote class="mt-4 font-serif text-4xl leading-tight font-medium italic sm:text-5xl">
                    “{{ $quoteOfTheDay->text }}”
                </blockquote>
                <p class="mt-4 text-sm text-muted">
                    — shared by {{ $quoteOfTheDay->user->name }} ·
                    {{ $quoteOfTheDay->likes_yesterday > 0 ? 'the most liked line yesterday' : 'the most liked line so far' }}
                </p>
            @else
                <p class="mt-4 font-serif text-4xl leading-tight italic sm:text-5xl">The wall is waiting for its first line.</p>
            @endif
        </div>

        <p class="absolute bottom-6 left-0 hidden w-full text-center text-xs tracking-widest text-muted uppercase sm:block">
            <span class="sun-status-up">Sunrise 06:00 · the shadows follow the sun · Sunset 17:30</span>
            <span class="sun-status-down">The sun has set · the wall rests until 06:00</span>
        </p>
    </header>

    {{-- ============ The Wall ============ --}}
    <section id="wall" class="scroll-mt-20 border-t border-line px-4 pt-10 pb-24 sm:px-8">
        <div class="mx-auto max-w-6xl">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="font-serif text-4xl font-bold text-gold-ink">The Wall</h2>
                    <p class="mt-1 text-sm text-muted">Every like is a little warmth. The most loved lines glow.</p>
                </div>

                {{-- Topic filter (resources/js/app.js hides the cards of other topics) --}}
                <div class="flex flex-wrap gap-5 text-sm font-semibold">
                    @foreach (auth()->check() ? ['All', 'Mine', ...$topics] : ['All', ...$topics] as $topic)
                        <button type="button" data-topic-filter="{{ $topic }}"
                            aria-pressed="{{ $topic === 'All' ? 'true' : 'false' }}"
                            class="border-b-2 border-transparent pb-1 text-muted transition hover:text-ink aria-pressed:border-gold aria-pressed:text-ink">
                            {{ $topic }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- The cards: 1 column on phones, 2 on tablets, 3 on computers --}}
            <div class="grid grid-cols-1 gap-7 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($quotes as $quote)
                    @can('update', $quote)
                        @if ($editingId === $quote->id)
                            {{-- Editing one of your quotes --}}
                            <form id="quote-{{ $quote->id }}" method="POST" action="{{ route('quotes.update', $quote) }}"
                                class="quote-mine quote-card scroll-mt-28 space-y-3" data-topic="{{ $quote->topic }}" data-mine="1">
                                @csrf
                                @method('PUT')

                                <x-quote-fields :quote="$quote" bag="edit" />

                                <div class="flex gap-2">
                                    <button type="submit" class="rounded-full bg-on-ember px-5 py-2 font-semibold text-ember">Save</button>
                                    <a href="{{ route('home') }}#wall" class="px-3 py-2 text-on-ember hover:underline">Cancel</a>
                                </div>
                            </form>
                            @continue
                        @endif
                    @endcan

                    <x-quote-card :quote="$quote" />
                @empty
                    <p class="text-muted sm:col-span-2 lg:col-span-3">No quotes yet. Be the first to share one!</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Floating "+" button: opens the share pop-up, or sends visitors to log in first --}}
    @auth
        <button type="button" popovertarget="quote-popup" aria-label="Share a quote"
            class="fixed right-6 bottom-6 z-40 flex h-16 w-16 items-center justify-center rounded-full bg-gold text-dusk shadow-xl shadow-gold/40 transition hover:scale-110 hover:bg-gold-light focus:outline-none focus-visible:ring-4 focus-visible:ring-gold/40 sm:right-8 sm:bottom-8">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                class="h-8 w-8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </button>

        {{-- Pop-up form. If the server finds a mistake, data-open-now reopens it with the errors. --}}
        <div id="quote-popup" popover @if ($errors->share->any()) data-open-now @endif
            class="m-auto max-h-[90vh] w-[90%] max-w-lg overflow-y-auto rounded-3xl border border-line bg-surface p-0 text-ink shadow-2xl backdrop:bg-black/50 backdrop:backdrop-blur-sm">

            <form method="POST" action="{{ route('quotes.store') }}" class="space-y-4 p-6">
                @csrf

                <div class="flex items-center justify-between">
                    <h2 class="font-serif text-3xl font-bold text-gold-ink">Share a quote</h2>
                    <button type="button" popovertarget="quote-popup" popovertargetaction="hide" aria-label="Close"
                        class="rounded-full p-1 text-muted hover:bg-ink/10 hover:text-ink">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="h-6 w-6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <x-quote-fields bag="share" />

                <button type="submit" class="btn-primary w-full">Send</button>
            </form>
        </div>
    @else
        <a href="{{ route('login') }}" aria-label="Log in to share a quote"
            class="fixed right-6 bottom-6 z-40 flex h-16 w-16 items-center justify-center rounded-full bg-gold text-dusk shadow-xl shadow-gold/40 transition hover:scale-110 hover:bg-gold-light sm:right-8 sm:bottom-8">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"
                class="h-8 w-8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </a>
    @endauth
@endsection
