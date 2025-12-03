<?php

declare(strict_types=1);

use App\Modules\Workspace\Http\Controllers\Api\WorkspaceController;
use App\Modules\Workspace\Http\Controllers\Api\WorkspaceMemberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Workspace API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('workspaces')->group(function () {
        Route::get('/', [WorkspaceController::class, 'index']);
        Route::post('/', [WorkspaceController::class, 'store']);

        Route::prefix('{workspace:uuid}')->group(function () {
            Route::get('/', [WorkspaceController::class, 'show']);
            Route::put('/', [WorkspaceController::class, 'update']);
            Route::delete('/', [WorkspaceController::class, 'destroy']);

            // Members
            Route::get('/members', [WorkspaceMemberController::class, 'index']);
            Route::post('/members/invite', [WorkspaceMemberController::class, 'invite']);
            Route::put('/members/{user}', [WorkspaceMemberController::class, 'updateRole']);
            Route::delete('/members/{user}', [WorkspaceMemberController::class, 'remove']);
        });
    });
});
