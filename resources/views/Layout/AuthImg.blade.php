{{--
    Shared layout for the Log In and Sign Up pages:
    the sun on the left, the form on the right.
    The sun reacts to the form — see "Sun on the log in / sign up pages" in resources/js/app.js.
--}}
@extends('Layout.app')

@section('content')
    <div class="grid min-h-[calc(100vh-5rem)] grid-cols-1 md:grid-cols-2">

        {{-- Left: the sun panel (always dark, like the painting it comes from) --}}
        <div class="relative hidden flex-col items-center justify-center overflow-hidden bg-[#2b2008] p-10 md:flex">

            {{-- Background: soft glows, handwriting lines and twinkling stars --}}
            <svg class="pointer-events-none absolute inset-0 h-full w-full" viewBox="0 0 600 800"
                preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                <circle cx="300" cy="360" r="320" fill="#4a3810" opacity=".55" />
                <circle cx="470" cy="640" r="90" fill="#6f6a22" opacity=".35" />
                <circle cx="110" cy="200" r="60" fill="#6f6a22" opacity=".3" />
                <g fill="none" stroke="#c9b27a" stroke-width="1.6" stroke-linecap="round" opacity=".28">
                    <path d="M40 600c20-14 34 10 52-2s28-16 44 0 30-8 46-4" />
                    <path d="M60 640c18-10 30 8 48-4s30-12 46 2 26-10 40-6 22 6 36-2" />
                    <path d="M380 130c16-10 28 6 44-4s24-10 38 2 22-6 34-2" />
                    <path d="M400 170c14-8 26 6 40-2s22-8 36 2" />
                    <path d="M440 700c10-24 40-30 54-12s-6 40-28 34-20-30 4-36 34 10 30 26" />
                </g>
                <g fill="#e8c35a">
                    <path class="star" d="M110 110c2 12 8 18 20 20-12 2-18 8-20 20-2-12-8-18-20-20 12-2 18-8 20-20Z" />
                    <path class="star [animation-delay:1.1s]" d="M500 90c1.5 9 6 13.5 15 15-9 1.5-13.5 6-15 15-1.5-9-6-13.5-15-15 9-1.5 13.5-6 15-15Z" />
                    <path class="star [animation-delay:2s]" d="M70 460c1.5 9 6 13.5 15 15-9 1.5-13.5 6-15 15-1.5-9-6-13.5-15-15 9-1.5 13.5-6 15-15Z" />
                    <path class="star [animation-delay:.6s]" d="M530 470c1 6 4 9 10 10-6 1-9 4-10 10-1-6-4-9-10-10 6-1 9-4 10-10Z" />
                    <path class="star [animation-delay:1.6s]" d="M200 700c1 6 4 9 10 10-6 1-9 4-10 10-1-6-4-9-10-10 6-1 9-4 10-10Z" />
                </g>
            </svg>

            <x-sun data-auth-sun class="relative w-full max-w-md" />

            {{-- What the sun "says" — changes with the mood --}}
            <p data-sun-line aria-live="polite"
                class="relative mt-6 min-h-[2.5em] text-center font-serif text-3xl leading-tight text-on-ember italic">
                “Every line you keep is a little light.”
            </p>
        </div>

        {{-- Right: the form --}}
        <div class="flex flex-col items-center justify-center p-6 sm:p-10">
            {{-- On phones the big panel is hidden, so show a small sun above the form --}}
            <x-sun data-auth-sun class="mb-2 w-36 md:hidden" />

            @yield('form')

            <p class="mt-10 font-serif text-lg text-muted italic">Collected under the same sun.</p>
        </div>
    </div>
@endsection
