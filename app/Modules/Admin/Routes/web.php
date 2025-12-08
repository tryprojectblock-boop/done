<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Admin\Http\Controllers\AdminAuthController;
use App\Modules\Admin\Http\Controllers\DashboardController;
use App\Modules\Admin\Http\Controllers\ClientsController;
use App\Modules\Admin\Http\Controllers\WorkspacesController;
use App\Modules\Admin\Http\Controllers\PlansController;
use App\Modules\Admin\Http\Controllers\CouponsController;
use App\Modules\Admin\Http\Controllers\InvoicesController;
use App\Modules\Admin\Http\Controllers\AdminUsersController;
use App\Modules\Admin\Http\Controllers\AppSettingsController;

/*
|--------------------------------------------------------------------------
| Admin (Backoffice) Routes
|--------------------------------------------------------------------------
*/

Route::prefix('backoffice')->name('backoffice.')->group(function () {
    // Guest routes (authentication)
    Route::middleware('guest:admin')->group(function () {
        // Step 1: Email verification
        Route::get('/', [AdminAuthController::class, 'showVerifyEmail'])->name('verify-email');
        Route::post('/verify-email', [AdminAuthController::class, 'sendVerificationCode'])->name('verify-email.send');

        // Step 2: Verify code
        Route::get('/verify-code', [AdminAuthController::class, 'showVerifyCode'])->name('verify-code');
        Route::post('/verify-code', [AdminAuthController::class, 'verifyCode'])->name('verify-code.verify');
        Route::post('/resend-code', [AdminAuthController::class, 'resendCode'])->name('resend-code');

        // Step 3: Login
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    });

    // Authenticated admin routes
    Route::middleware('admin.auth')->group(function () {
        // Logout
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Clients (Companies)
        Route::prefix('clients')->name('clients.')->group(function () {
            Route::get('/', [ClientsController::class, 'index'])->name('index');
            Route::get('/{company}', [ClientsController::class, 'show'])->name('show');
            Route::get('/{company}/edit', [ClientsController::class, 'edit'])->name('edit');
            Route::put('/{company}', [ClientsController::class, 'update'])->name('update');
            Route::get('/{company}/users', [ClientsController::class, 'users'])->name('users');
            Route::patch('/{company}/toggle-status', [ClientsController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{company}/pause', [ClientsController::class, 'pauseAccount'])->name('pause');
            Route::post('/{company}/activate', [ClientsController::class, 'activateAccount'])->name('activate');
            Route::post('/{company}/send-email', [ClientsController::class, 'sendEmail'])->name('send-email');
            Route::delete('/{company}/delete-data', [ClientsController::class, 'deleteData'])->name('delete-data');
            Route::delete('/{company}', [ClientsController::class, 'destroy'])->name('destroy');
        });

        // Workspaces
        Route::prefix('workspaces')->name('workspaces.')->group(function () {
            Route::get('/', [WorkspacesController::class, 'index'])->name('index');
            Route::get('/{workspace}', [WorkspacesController::class, 'show'])->name('show');
        });

        // Plans & Coupons
        Route::prefix('plans')->name('plans.')->group(function () {
            Route::get('/', [PlansController::class, 'index'])->name('index');
            Route::get('/create', [PlansController::class, 'create'])->name('create');
            Route::post('/', [PlansController::class, 'store'])->name('store');
            Route::get('/{plan}/edit', [PlansController::class, 'edit'])->name('edit');
            Route::put('/{plan}', [PlansController::class, 'update'])->name('update');
            Route::patch('/{plan}/toggle-status', [PlansController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{plan}', [PlansController::class, 'destroy'])->name('destroy');
        });

        // Coupons
        Route::prefix('coupons')->name('coupons.')->group(function () {
            Route::get('/create', [CouponsController::class, 'create'])->name('create');
            Route::post('/', [CouponsController::class, 'store'])->name('store');
            Route::get('/{coupon}/edit', [CouponsController::class, 'edit'])->name('edit');
            Route::put('/{coupon}', [CouponsController::class, 'update'])->name('update');
            Route::patch('/{coupon}/toggle-status', [CouponsController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{coupon}', [CouponsController::class, 'destroy'])->name('destroy');
        });

        // Invoices & Payments (read-only from Stripe)
        Route::get('/invoices', [InvoicesController::class, 'index'])->name('invoices.index');

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            // App Settings
            Route::get('/app', [AppSettingsController::class, 'index'])->name('app');
            Route::put('/app', [AppSettingsController::class, 'update'])->name('app.update');

            // Admin Users (Administrator only)
            Route::middleware('admin.administrator')->prefix('admins')->name('admins.')->group(function () {
                Route::get('/', [AdminUsersController::class, 'index'])->name('index');
                Route::get('/create', [AdminUsersController::class, 'create'])->name('create');
                Route::post('/', [AdminUsersController::class, 'store'])->name('store');
                Route::get('/{admin}/edit', [AdminUsersController::class, 'edit'])->name('edit');
                Route::put('/{admin}', [AdminUsersController::class, 'update'])->name('update');
                Route::patch('/{admin}/toggle-status', [AdminUsersController::class, 'toggleStatus'])->name('toggle-status');
                Route::delete('/{admin}', [AdminUsersController::class, 'destroy'])->name('destroy');
            });
        });
    });
});
