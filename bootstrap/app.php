<?php

use App\Http\Middleware\EnsureUserCanManageUsers;
use App\Http\Middleware\EnsureUserIsNotSuspended;
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
        // Append suspended check middleware to the web middleware group
        $middleware->appendToGroup('web', EnsureUserIsNotSuspended::class);

        $middleware->alias([
            'can.manage.users' => EnsureUserCanManageUsers::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
