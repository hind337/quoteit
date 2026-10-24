@extends('Layout.AuthImg')

@section('title', 'Sign Up')

@section('form')
    <form action="{{ route('signup') }}" method="POST" class="w-full max-w-md space-y-5">
        @csrf

        <div>
            <h1 class="font-serif text-5xl leading-none font-bold text-gold-ink">Join QuoteIt</h1>
            <p class="mt-2 text-muted">Keep the lines you love, and share them with others.</p>
        </div>

        <div>
            <label for="name" class="label">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name"
                class="input peer user-invalid:border-red-500 @error('name') border-red-500 @enderror" required
                minlength="2" maxlength="50" pattern="[A-Za-z\u0600-\u06FF ]+"
                data-sun-watch data-sun-say="“Nice to meet you!”">
            @error('name')
                <p class="mt-1 text-xs font-semibold text-red-600 night:text-red-400">{{ $message }}</p>
            @else
                <p class="hint peer-user-invalid:text-red-600">2–50 characters, letters only.</p>
            @enderror
        </div>

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email"
                class="input peer user-invalid:border-red-500 @error('email') border-red-500 @enderror" required
                pattern="[^@\s]+@[^@\s]+\.[A-Za-z]{2,}" placeholder="name@example.com"
                data-sun-watch data-sun-say="“I’ll remember you by this.”">
            @error('email')
                <p class="mt-1 text-xs font-semibold text-red-600 night:text-red-400">{{ $message }}</p>
            @else
                <p class="hint peer-user-invalid:text-red-600">A valid email, e.g. name@example.com</p>
            @enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <div class="relative">
                <input id="password" name="password" type="password" autocomplete="new-password"
                    class="input peer pr-20 user-invalid:border-red-500 @error('password') border-red-500 @enderror" required
                    minlength="8" placeholder="At least 8 characters" data-password>
                <button type="button" data-show-password
                    class="absolute top-1 right-1 h-11 rounded-xl px-3 text-sm font-semibold text-gold-ink hover:bg-ink/10">Show</button>
            </div>
            @error('password')
                <p class="mt-1 text-xs font-semibold text-red-600 night:text-red-400">{{ $message }}</p>
            @else
                <p class="hint">At least 8 characters.</p>
            @enderror
        </div>

        <button type="submit" class="btn-primary w-full" data-sun-ready>Sign Up</button>

        <p class="text-center text-sm text-muted">
            Already have an account? <a href="{{ route('login') }}" class="font-semibold text-gold-ink hover:underline">Log In</a>
        </p>
    </form>
@endsection
