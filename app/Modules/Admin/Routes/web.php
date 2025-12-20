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
use App\Modules\Admin\Http\Controllers\AppController;
use App\Modules\Admin\Http\Controllers\ScheduledTasksController;
use App\Modules\Admin\Http\Controllers\FunnelController;
use App\Modules\Admin\Http\Controllers\FunnelEmailLogController;
use App\Modules\Admin\Http\Controllers\FunnelTrackingController;

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

        // App (Maintenance Mode & Factory Reset)
        Route::prefix('app')->name('app.')->group(function () {
            Route::get('/', [AppController::class, 'index'])->name('index');
            Route::post('/maintenance/enable', [AppController::class, 'enableMaintenanceMode'])->name('maintenance.enable');
            Route::post('/maintenance/disable', [AppController::class, 'disableMaintenanceMode'])->name('maintenance.disable');
            Route::post('/factory-reset', [AppController::class, 'factoryReset'])->name('factory-reset');
        });

        // Scheduled Tasks (Cron Jobs)
        Route::prefix('scheduled-tasks')->name('scheduled-tasks.')->group(function () {
            Route::get('/', [ScheduledTasksController::class, 'index'])->name('index');
            Route::get('/create', [ScheduledTasksController::class, 'create'])->name('create');
            Route::post('/', [ScheduledTasksController::class, 'store'])->name('store');
            Route::put('/{task}', [ScheduledTasksController::class, 'update'])->name('update');
            Route::post('/{task}/toggle', [ScheduledTasksController::class, 'toggle'])->name('toggle');
            Route::post('/{task}/run', [ScheduledTasksController::class, 'run'])->name('run');
            Route::post('/{task}/run-ajax', [ScheduledTasksController::class, 'runAjax'])->name('run-ajax');
            Route::delete('/{task}', [ScheduledTasksController::class, 'destroy'])->name('destroy');
        });

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

        // Funnel (Email Automation)
        Route::prefix('funnel')->name('funnel.')->group(function () {
            Route::get('/', [FunnelController::class, 'index'])->name('index');
            Route::get('/create', [FunnelController::class, 'create'])->name('create');
            Route::post('/', [FunnelController::class, 'store'])->name('store');
            Route::get('/{funnel}/edit', [FunnelController::class, 'edit'])->name('edit');
            Route::put('/{funnel}', [FunnelController::class, 'update'])->name('update');
            Route::delete('/{funnel}', [FunnelController::class, 'destroy'])->name('destroy');
            Route::post('/{funnel}/toggle', [FunnelController::class, 'toggle'])->name('toggle');
            Route::post('/{funnel}/duplicate', [FunnelController::class, 'duplicate'])->name('duplicate');

            // Steps
            Route::post('/{funnel}/steps', [FunnelController::class, 'storeStep'])->name('steps.store');
            Route::put('/{funnel}/steps/{step}', [FunnelController::class, 'updateStep'])->name('steps.update');
            Route::delete('/{funnel}/steps/{step}', [FunnelController::class, 'destroyStep'])->name('steps.destroy');
            Route::post('/{funnel}/steps/reorder', [FunnelController::class, 'reorderSteps'])->name('steps.reorder');

            // Email Logs
            Route::get('/logs', [FunnelEmailLogController::class, 'index'])->name('logs');
            Route::get('/logs/{log}', [FunnelEmailLogController::class, 'show'])->name('logs.show');
        });
    });

    // Public Tracking Routes (no auth required for email open/click tracking)
    Route::get('/funnel/t/o/{uuid}', [FunnelTrackingController::class, 'trackOpen'])->name('funnel.track.open');
    Route::get('/funnel/t/c/{uuid}/{linkId}', [FunnelTrackingController::class, 'trackClick'])->name('funnel.track.click');
});
