<?php

declare(strict_types=1);

use App\Modules\Workspace\Http\Controllers\WorkspaceController;
use App\Modules\Workspace\Http\Controllers\WorkspaceMemberController;
use App\Modules\Workspace\Http\Controllers\WorkspaceInvitationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Workspace Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Workspace CRUD
    Route::prefix('workspaces')->name('workspace.')->group(function () {
        Route::get('/', [WorkspaceController::class, 'index'])->name('index');
        Route::get('/create', [WorkspaceController::class, 'create'])->name('create');
        Route::post('/', [WorkspaceController::class, 'store'])->name('store');

        Route::prefix('{workspace:uuid}')->group(function () {
            Route::get('/', [WorkspaceController::class, 'show'])->name('show');
            Route::get('/guest-view', [WorkspaceController::class, 'guestView'])->name('guest-view');
            Route::get('/settings', [WorkspaceController::class, 'settings'])->name('settings');
            Route::put('/', [WorkspaceController::class, 'update'])->name('update');
            Route::delete('/', [WorkspaceController::class, 'destroy'])->name('destroy');
            Route::post('/archive', [WorkspaceController::class, 'archive'])->name('archive');
            Route::post('/restore', [WorkspaceController::class, 'restore'])->name('restore');
            Route::post('/logo', [WorkspaceController::class, 'updateLogo'])->name('logo.update');

            // Members
            Route::prefix('members')->name('members.')->group(function () {
                Route::get('/', [WorkspaceMemberController::class, 'index'])->name('index');
                Route::post('/invite', [WorkspaceMemberController::class, 'invite'])->name('invite');
                Route::put('/{user}', [WorkspaceMemberController::class, 'updateRole'])->name('update-role');
                Route::delete('/{user}', [WorkspaceMemberController::class, 'remove'])->name('remove');
                Route::post('/transfer-ownership/{user}', [WorkspaceMemberController::class, 'transferOwnership'])->name('transfer-ownership');
            });

            // Guests
            Route::prefix('guests')->name('guests.')->group(function () {
                Route::delete('/{guest}', [WorkspaceMemberController::class, 'removeGuest'])->name('remove');
            });

            // Modules
            Route::post('/modules', [WorkspaceController::class, 'updateModules'])->name('modules.update');
        });
    });

    // Invitation acceptance (separate route for cleaner URL)
    Route::get('/invitation/{token}', [WorkspaceInvitationController::class, 'show'])->name('workspace.invitation.show');
    Route::post('/invitation/{token}/accept', [WorkspaceInvitationController::class, 'accept'])->name('workspace.invitation.accept');
});
