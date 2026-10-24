<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Accounts: sign up, log in and log out.
 */
class AuthController extends Controller
{
    /**
     * Show the sign up form.
     */
    public function showSignUp(): View
    {
        return view('Authentication.SignUp', ['page' => 'Sign Up']);
    }

    /**
     * Create the account, log the new user in and send them to the home page.
     */
    public function signUp(Request $request): RedirectResponse
    {
        $details = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\pL\s]+$/u'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
        ], [
            'name.regex' => 'Your name can only contain letters and spaces.',
            'email.unique' => 'This email already has an account. Try logging in instead.',
        ]);

        // The User model hashes the password automatically (see the "hashed" cast)
        $user = User::create($details);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', "Welcome to QuoteIt, {$user->name}!");
    }

    /**
     * Show the log in form.
     */
    public function showLogIn(): View
    {
        return view('Authentication.LogIn', ['page' => 'Log In']);
    }

    /**
     * Check the email and password, then log the user in.
     */
    public function logIn(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors(['email' => 'The email or password is incorrect.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('status', 'Welcome back, '.Auth::user()->name.'!');
    }

    /**
     * Log the user out and forget their session.
     */
    public function logOut(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
