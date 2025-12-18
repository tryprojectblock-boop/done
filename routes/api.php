<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\TeamChannelController;
use App\Http\Controllers\Api\V1\TeamChannelThreadController;
use App\Http\Controllers\Api\V1\TeamChannelReplyController;
use App\Http\Controllers\Webhooks\MailgunWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Mailgun Webhooks (no auth required - uses signature verification)
Route::prefix('webhooks')->group(function () {
    Route::post('/mailgun/inbound', [MailgunWebhookController::class, 'handleInbound'])
        ->name('webhooks.mailgun.inbound');
});

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

    // Public routes (no authentication required)
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Protected routes (authentication required)
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/tasks', [DashboardController::class, 'tasks']);

        // Team Channels
        Route::apiResource('channels', TeamChannelController::class);
        Route::post('/channels/{channel}/join', [TeamChannelController::class, 'join']);
        Route::post('/channels/{channel}/leave', [TeamChannelController::class, 'leave']);
        Route::get('/channels/{channel}/members', [TeamChannelController::class, 'members']);
        Route::post('/channels/{channel}/members', [TeamChannelController::class, 'addMember']);
        Route::delete('/channels/{channel}/members/{member}', [TeamChannelController::class, 'removeMember']);

        // Channel Threads
        Route::apiResource('channels.threads', TeamChannelThreadController::class);
        Route::post('/channels/{channel}/threads/{thread}/pin', [TeamChannelThreadController::class, 'togglePin']);

        // Thread Replies
        Route::post('/channels/{channel}/threads/{thread}/replies', [TeamChannelReplyController::class, 'store']);
        Route::put('/replies/{reply}', [TeamChannelReplyController::class, 'update']);
        Route::delete('/replies/{reply}', [TeamChannelReplyController::class, 'destroy']);
    });
});
