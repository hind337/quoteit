@props(['page' => ''])

<nav class="sticky top-0 z-50 border-b border-line bg-nav backdrop-blur-md">
    <div class="mx-auto flex h-20 max-w-7xl items-center gap-4 px-4 sm:gap-6 sm:px-6">

        {{-- Logo: small sun + name --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="QuoteIt home">
            <svg aria-hidden="true" class="h-8 w-8" viewBox="0 0 40 40">
                <circle cx="20" cy="20" r="9" fill="#e8a80d" />
                <g stroke="#d9711a" stroke-width="2.4" stroke-linecap="round">
                    <path d="M20 3v5M20 32v5M3 20h5M32 20h5M8 8l3.5 3.5M28.5 28.5 32 32M8 32l3.5-3.5M28.5 11.5 32 8" />
                </g>
            </svg>
            <span class="brand">QuoteIt</span>
        </a>

        @if (! in_array($page, ['Sign Up', 'Log In']))
            <ul class="hidden items-center gap-6 text-sm font-medium sm:flex">
                <li><a href="{{ route('home') }}" class="border-b-2 border-gold pb-1">Quotes</a></li>

            </ul>
        @endif

        <div class="flex-1"></div>

        {{-- Day / night switch (the click is handled in resources/js/app.js) --}}
        <button type="button" data-theme-toggle
            class="flex h-11 items-center gap-2 rounded-full border border-line px-4 text-sm font-semibold transition hover:bg-ink/10">
            {{-- Moon: shown in day mode --}}
            <svg class="h-5 w-5 night:hidden" aria-hidden="true" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" />
            </svg>
            {{-- Sun: shown in night mode --}}
            <svg class="hidden h-5 w-5 night:block" aria-hidden="true" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                <circle cx="12" cy="12" r="4" />
                <path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
            </svg>
            <span class="hidden sm:inline sm:night:hidden">Sunset</span>
            <span class="hidden sm:night:inline">Sunrise</span>
        </button>

        @auth
            {{-- Logged in: first letter of the name in a circle, the name, and log out --}}
            <div class="flex items-center gap-3">
                <span aria-hidden="true"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-ember font-bold text-on-ember uppercase ring-2 ring-gold ring-offset-2 ring-offset-page">
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </span>
                <span class="hidden text-sm font-medium md:inline">{{ auth()->user()->name }}</span>
            </div>

            {{-- Logging out changes data, so it is a POST form (protected by @csrf), not a link --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm font-medium text-muted hover:text-ink">Log out</button>
            </form>
        @endauth

        @guest
            @if ($page !== 'Log In')
                <a href="{{ route('login') }}" class="{{ $page === 'Sign Up' ? 'btn-primary py-2' : 'text-sm font-medium text-muted hover:text-ink' }}">Log In</a>
            @endif
            @if ($page !== 'Sign Up')
                <a href="{{ route('signup') }}" class="btn-primary py-2">Sign Up</a>
            @endif
        @endguest

        <img src="{{ asset('images/logo.png') }}" class="hidden h-11 w-auto rounded-lg lg:block" alt="Tuwaiq Club">
    </div>
</nav>
