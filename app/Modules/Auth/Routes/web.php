<?php

declare(strict_types=1);

use App\Modules\Auth\Http\Controllers\AccountPausedController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['guest'])->group(function () {
    // Registration page (Vue SPA)
    Route::get('/register', function () {
        return view('auth::register');
    })->name('register');

    // Login page
    Route::get('/login', function () {
        return view('auth::login');
    })->name('login');
});

Route::middleware(['auth'])->group(function () {
    // Account paused page (no CheckAccountPaused middleware here)
    Route::get('/account/paused', [AccountPausedController::class, 'show'])->name('account.paused');

    // Logout
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
