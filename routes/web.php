<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\RepostController;
use Illuminate\Support\Facades\Route;

// The wall: everyone can read it, even without an account
Route::get('/', [QuoteController::class, 'index'])->name('home');
Route::get('/quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
Route::get('/quotes/{quote}/wallpaper', [QuoteController::class, 'wallpaper'])->name('quotes.wallpaper');

// Only for visitors who are NOT logged in
Route::middleware('guest')->group(function () {
    Route::get('/signup', [AuthController::class, 'showSignUp'])->name('signup');
    Route::post('/signup', [AuthController::class, 'signUp']);

    Route::get('/login', [AuthController::class, 'showLogIn'])->name('login');
    Route::post('/login', [AuthController::class, 'logIn']);
});

// Only for logged-in users
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logOut'])->name('logout');

    Route::post('/quotes', [QuoteController::class, 'store'])->name('quotes.store');
    Route::put('/quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::delete('/quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');

    Route::post('/quotes/{quote}/like', [LikeController::class, 'toggle'])->name('quotes.like');
    Route::post('/quotes/{quote}/repost', [RepostController::class, 'toggle'])->name('quotes.repost');
});
