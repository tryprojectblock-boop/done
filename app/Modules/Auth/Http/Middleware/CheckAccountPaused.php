<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountPaused
{
    /**
     * Handle an incoming request.
     * Check if the user's company account is paused.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->company && $user->company->isPaused()) {
            // Allow access to the paused account page and logout
            if ($request->routeIs('account.paused') || $request->routeIs('logout')) {
                return $next($request);
            }

            // Redirect to paused account page
            return redirect()->route('account.paused');
        }

        return $next($request);
    }
}
