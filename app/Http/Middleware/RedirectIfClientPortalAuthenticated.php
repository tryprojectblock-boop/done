<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfClientPortalAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * Redirect authenticated client portal users away from guest pages (login, signup).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('client-portal')->check()) {
            return redirect()->route('client-portal.dashboard');
        }

        return $next($request);
    }
}
