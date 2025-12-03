<?php

use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\GuestPortalController;
use App\Http\Controllers\GuestSignupController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamSignupController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-flyonui', function () {
    return view('test-flyonui');
});

Route::get('/demo-theme', function () {
    return view('demo-theme');
});

/*
|--------------------------------------------------------------------------
| Dashboard Routes (Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/complete-onboarding', [DashboardController::class, 'completeOnboarding'])->name('dashboard.complete-onboarding');

    // Workspace routes placeholder
    Route::get('/workspace', function () {
        return redirect('/dashboard');
    })->name('workspace.index');

    // Logout route
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');

    // Password routes
    Route::get('/profile/password', [PasswordController::class, 'index'])->name('profile.password');
    Route::put('/profile/password', [PasswordController::class, 'update'])->name('profile.password.update');

    // User management routes (Admin & Owner only)
    Route::middleware(['can.manage.users'])->group(function () {
        Route::get('/users', [UsersController::class, 'index'])->name('users.index');
        Route::get('/users/invite', [UsersController::class, 'invitePage'])->name('users.invite');
        Route::post('/users/invite', [UsersController::class, 'sendInvitations'])->name('users.invite.send');
        Route::get('/users/{user}', [UsersController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/resend-invitation', [UsersController::class, 'resendInvitation'])->name('users.resend-invitation');
    });

    // Guest/Client CRM routes
    Route::get('/guests', [GuestController::class, 'index'])->name('guests.index');
    Route::get('/guests/create', [GuestController::class, 'create'])->name('guests.create');
    Route::post('/guests', [GuestController::class, 'store'])->name('guests.store');
    Route::get('/guests/{guest}', [GuestController::class, 'show'])->name('guests.show');
    Route::get('/guests/{guest}/edit', [GuestController::class, 'edit'])->name('guests.edit');
    Route::put('/guests/{guest}', [GuestController::class, 'update'])->name('guests.update');
    Route::delete('/guests/{guest}', [GuestController::class, 'destroy'])->name('guests.destroy');
    Route::post('/guests/{guest}/resend-invitation', [GuestController::class, 'resendInvitation'])->name('guests.resend-invitation');
});

/*
|--------------------------------------------------------------------------
| Team Signup Routes (For Invited Users)
|--------------------------------------------------------------------------
*/
Route::get('/team/signup/{token}', [TeamSignupController::class, 'show'])->name('team.signup');
Route::post('/team/signup/{token}', [TeamSignupController::class, 'complete'])->name('team.signup.complete');

/*
|--------------------------------------------------------------------------
| Guest Signup Routes (For Invited Guests/Clients)
|--------------------------------------------------------------------------
*/
Route::get('/guest/signup/{token}', [GuestSignupController::class, 'show'])->name('guest.signup');
Route::post('/guest/signup/{token}', [GuestSignupController::class, 'complete'])->name('guest.signup.complete');

/*
|--------------------------------------------------------------------------
| Guest Portal Routes (For Authenticated Guests)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:guest'])->prefix('guest')->name('guest.portal.')->group(function () {
    Route::get('/portal', [GuestPortalController::class, 'index'])->name('index');
    Route::get('/portal/workspace/{workspace:uuid}', [GuestPortalController::class, 'workspace'])->name('workspace');
    Route::post('/logout', [GuestPortalController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| File Upload Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('files')->name('files.')->group(function () {
    Route::post('/upload', [FileUploadController::class, 'upload'])->name('upload');
    Route::delete('/delete', [FileUploadController::class, 'delete'])->name('delete');
    Route::post('/temporary-url', [FileUploadController::class, 'getTemporaryUrl'])->name('temporary-url');
});

/*
|--------------------------------------------------------------------------
| Workflow Routes (Company-based)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('workflows')->name('workflows.')->group(function () {
    Route::get('/', [WorkflowController::class, 'index'])->name('index');
    Route::get('/create', [WorkflowController::class, 'create'])->name('create');
    Route::post('/', [WorkflowController::class, 'store'])->name('store');
    Route::get('/{workflow}/edit', [WorkflowController::class, 'edit'])->name('edit');
    Route::put('/{workflow}', [WorkflowController::class, 'update'])->name('update');
    Route::delete('/{workflow}', [WorkflowController::class, 'destroy'])->name('destroy');
    Route::post('/{workflow}/duplicate', [WorkflowController::class, 'duplicate'])->name('duplicate');
    Route::post('/{workflow}/archive', [WorkflowController::class, 'archive'])->name('archive');
    Route::post('/{workflow}/restore', [WorkflowController::class, 'restore'])->name('restore');
});

