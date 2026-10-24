@extends('Layout.app')

@section('title', 'Make a wallpaper')
@section('content')
    @php
        // Colour sets for the wallpaper: background, text, and accent (frame / small details)
        $backgrounds = [
            'Parchment' => ['#f4e7c9', '#3d2b08', '#b5520f'],
            'Ember' => ['#b5520f', '#fff4e0', '#f4c64e'],
            'Night' => ['#1c1405', '#f5e9cf', '#e8a80d'],
            'Gold' => ['#e8a80d', '#241906', '#fff4e0'],
            'Olive' => ['#4a3810', '#f5e9cf', '#e8a80d'],
            'Paper' => ['#fffaf0', '#3d2b08', '#e8a80d'],
        ];
        $frames = ['None', 'Line', 'Double', 'Card'];
        $fonts = ['Serif', 'Sans'];
        $source = collect([$quote->source_title, $quote->source_author])->filter()->implode(' · ');
    @endphp

    <div class="mx-auto max-w-6xl px-4 pt-8 pb-24 sm:px-8">
        <a href="{{ route('quotes.show', $quote) }}" class="text-sm font-semibold text-gold-ink hover:underline">← Back to the quote</a>

        {{-- resources/js/app.js ("Phone wallpaper maker") draws the picture from these data-… values --}}
        <div class="mt-6 grid items-center gap-10 md:grid-cols-[auto_1fr] md:gap-16" data-wallpaper-maker
            data-text="{{ $quote->text }}" data-author="{{ $quote->user->name }}" data-source="{{ $source }}">

            {{-- Preview inside a phone shape --}}
            <div class="mx-auto w-[280px] rounded-[44px] border-[10px] border-black bg-black shadow-2xl shadow-black/40">
                <canvas data-wallpaper-canvas width="1170" height="2532" class="block h-auto w-full rounded-[34px]"
                    role="img" aria-label="Preview of the wallpaper"></canvas>
            </div>

            {{-- Choices --}}
            <form data-wallpaper-options class="space-y-7">
                <div>
                    <h1 class="font-serif text-5xl leading-none font-bold text-gold-ink">Make it your wallpaper</h1>
                    <p class="mt-3 text-muted">Pick a colour, a frame and a font. The picture is sized for a phone screen.</p>
                </div>

                <fieldset>
                    <legend class="label">Background</legend>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($backgrounds as $name => [$background, $text, $accent])
                            <label class="cursor-pointer" title="{{ $name }}">
                                <input type="radio" name="background" value="{{ $name }}" class="peer sr-only"
                                    data-colors="{{ json_encode([$background, $text, $accent]) }}" @checked($loop->first)>
                                <span class="block h-12 w-12 rounded-full border-2 border-line transition peer-checked:ring-4 peer-checked:ring-gold peer-checked:ring-offset-2 peer-checked:ring-offset-page peer-focus-visible:ring-4 peer-focus-visible:ring-gold/50"
                                    style="background-color: {{ $background }}"></span>
                                <span class="sr-only">{{ $name }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="label">Frame</legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($frames as $frame)
                            <label class="cursor-pointer">
                                <input type="radio" name="frame" value="{{ $frame }}" class="peer sr-only" @checked($frame === 'Line')>
                                <span class="inline-flex h-11 items-center rounded-full border border-line bg-field px-5 text-sm font-semibold transition peer-checked:border-gold peer-checked:bg-gold peer-checked:text-dusk peer-focus-visible:ring-4 peer-focus-visible:ring-gold/40">{{ $frame }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="label">Font</legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($fonts as $font)
                            <label class="cursor-pointer">
                                <input type="radio" name="font" value="{{ $font }}" class="peer sr-only" @checked($loop->first)>
                                <span class="inline-flex h-11 items-center rounded-full border border-line bg-field px-5 text-sm font-semibold transition peer-checked:border-gold peer-checked:bg-gold peer-checked:text-dusk peer-focus-visible:ring-4 peer-focus-visible:ring-gold/40 {{ $font === 'Serif' ? 'font-serif text-base italic' : '' }}">{{ $font }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                @if ($source)
                    <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold">
                        <input type="checkbox" name="show_source" checked class="h-5 w-5 accent-gold">
                        Show the source under the quote
                    </label>
                @endif

                <div class="flex flex-wrap items-center gap-4">
                    <button type="button" data-wallpaper-download class="btn-primary">
                        <svg class="mr-2 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m0 0-4.5-4.5M12 15l4.5-4.5M4 17v2.5h16V17" /></svg>
                        Download image
                    </button>
                    <p class="max-w-xs text-xs text-muted">Then open it in Photos and choose “Use as Wallpaper”.</p>
                </div>
            </form>
        </div>
    </div>
@endsection
