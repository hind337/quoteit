@extends('Layout.AuthImg')

@section('title', 'Log In')

@section('form')
    <form action="{{ route('login') }}" method="POST" class="w-full max-w-md space-y-5">
        @csrf

        <div>
            <h1 class="font-serif text-5xl leading-none font-bold text-gold-ink">Welcome back</h1>
            <p class="mt-2 text-muted">Log in to share the lines that stayed with you.</p>
        </div>

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email"
                class="input peer user-invalid:border-red-500 @error('email') border-red-500 @enderror" required
                pattern="[^@\s]+@[^@\s]+\.[A-Za-z]{2,}" placeholder="name@example.com"
                data-sun-watch data-sun-say="“Oh, hello! I remember you.”">
            @error('email')
                <p class="mt-1 text-xs font-semibold text-red-600 night:text-red-400">{{ $message }}</p>
            @else
                <p class="hint peer-user-invalid:text-red-600">A valid email, e.g. name@example.com</p>
            @enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <div class="relative">
                <input id="password" name="password" type="password" autocomplete="current-password"
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

        <button type="submit" class="btn-primary w-full" data-sun-ready>Log In</button>

        <p class="text-center text-sm text-muted">
            New here? <a href="{{ route('signup') }}" class="font-semibold text-gold-ink hover:underline">Sign Up</a>
        </p>
    </form>
@endsection
