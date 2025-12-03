<?php

declare(strict_types=1);

use App\Modules\Auth\Http\Controllers\LoginController;
use App\Modules\Auth\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth API Routes
|--------------------------------------------------------------------------
| These routes use 'web' middleware for session support since they need
| to establish user sessions after login/registration.
*/

Route::prefix('v1/auth')->name('api.auth.')->middleware('web')->group(function () {
    // Registration options (public)
    Route::get('/registration/options', [RegistrationController::class, 'getOptions'])
        ->name('registration.options');

    // Login (guest only)
    Route::post('/login', [LoginController::class, 'login'])
        ->name('login');

    // Logout (authenticated)
    Route::post('/logout', [LoginController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

    // Get authenticated user
    Route::get('/user', [LoginController::class, 'user'])
        ->middleware('auth')
        ->name('user');

    // Registration flow
    Route::prefix('registration')->name('registration.')->group(function () {
        // Step 1: Email registration
        Route::post('/email', [RegistrationController::class, 'registerEmail'])
            ->name('email');

        // Verify activation code
        Route::post('/verify-code', [RegistrationController::class, 'verifyCode'])
            ->name('verify-code');

        // Resend activation code
        Route::post('/resend-code', [RegistrationController::class, 'resendCode'])
            ->name('resend-code');

        // Get registration status
        Route::get('/{uuid}/status', [RegistrationController::class, 'getStatus'])
            ->name('status');

        // Step 2: Profile
        Route::post('/{uuid}/profile', [RegistrationController::class, 'completeProfile'])
            ->name('profile');

        // Step 3: Company
        Route::post('/{uuid}/company', [RegistrationController::class, 'completeCompany'])
            ->name('company');

        // Step 4: Complete with invitations
        Route::post('/{uuid}/complete', [RegistrationController::class, 'completeRegistration'])
            ->name('complete');
    });
});
