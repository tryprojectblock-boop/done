<?php

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
