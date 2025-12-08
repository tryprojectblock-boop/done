<?php

use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\CheckRegistrationEnabled;
use App\Http\Middleware\EnsureTwoFactorAuthenticated;
use App\Http\Middleware\EnsureUserCanManageUsers;
use App\Http\Middleware\EnsureUserIsNotSuspended;
use App\Modules\Auth\Http\Middleware\CheckAccountPaused;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Check maintenance mode first (before other middleware)
        $middleware->prependToGroup('web', CheckMaintenanceMode::class);

        // Append suspended check middleware to the web middleware group
        $middleware->appendToGroup('web', EnsureUserIsNotSuspended::class);

        // Append paused account check middleware to the web middleware group
        $middleware->appendToGroup('web', CheckAccountPaused::class);

        // Append 2FA check middleware to the web middleware group
        $middleware->appendToGroup('web', EnsureTwoFactorAuthenticated::class);

        $middleware->alias([
            'can.manage.users' => EnsureUserCanManageUsers::class,
            'check.account.paused' => CheckAccountPaused::class,
            'registration.enabled' => CheckRegistrationEnabled::class,
            'two-factor' => EnsureTwoFactorAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
