<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the user needs to complete 2FA setup
     * when their company requires it.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Skip for 2FA setup routes
        if ($request->routeIs('two-factor.*')) {
            return $next($request);
        }

        // Skip for logout route
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        // If user's company requires 2FA and user hasn't set it up, redirect to setup
        if ($user->needsTwoFactorSetup()) {
            return redirect()->route('two-factor.setup')
                ->with('warning', 'Your organization requires Two-Factor Authentication. Please set it up to continue.');
        }

        return $next($request);
    }
}
