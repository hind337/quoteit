<!DOCTYPE html>
<html lang="en" data-theme="day">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'QuoteIt')</title>

    {{-- Day or night, decided before the page paints so it doesn't flash:
         the visitor's own choice if they pressed the switch, otherwise the real time
         (day from 06:00 to 17:30, night after sunset) --}}
    <script>
        (() => {
            const hourFromAddress = new URLSearchParams(window.location.search).get('hour');
            const hour = hourFromAddress !== null
                ? Number(hourFromAddress)
                : new Date().getHours() + new Date().getMinutes() / 60;
            let theme = hour >= 6 && hour <= 17.5 ? 'day' : 'night';

            try {
                // A demo hour in the address wins; otherwise the visitor's saved choice
                theme = hourFromAddress !== null ? theme : localStorage.getItem('quoteit-theme') || theme;
            } catch (error) {}

            document.documentElement.dataset.theme = theme;
        })();
    </script>

    {{-- Cormorant Garamond: the classic font for quotes and titles --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,700;1,500;1,700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-page font-sans text-ink">
    <x-navbar :page="$page ?? ''" />

    {{-- A short message after signing up or logging in, e.g. "Welcome back, Sara!" --}}
    @if (session('status'))
        <p role="status" class="border-b border-line bg-gold/20 px-4 py-3 text-center text-sm font-semibold text-gold-ink">
            {{ session('status') }}
        </p>
    @endif

    <main>
        @yield('content')
    </main>

</body>

</html>
