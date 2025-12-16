<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientPortalAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via client-portal guard
        if (!Auth::guard('client-portal')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('client-portal.login');
        }

        $user = Auth::guard('client-portal')->user();

        // Verify user is a guest (client)
        if (!$user->is_guest) {
            Auth::guard('client-portal')->logout();
            return redirect()->route('client-portal.login')
                ->with('error', 'Access denied. This portal is for clients only.');
        }

        // Verify user has inbox workspace access
        if (!$user->inboxGuestWorkspaces()->exists()) {
            Auth::guard('client-portal')->logout();
            return redirect()->route('client-portal.login')
                ->with('error', 'Access denied. You do not have access to any inbox workspace.');
        }

        // Check user status
        if ($user->status === 'suspended') {
            Auth::guard('client-portal')->logout();
            return redirect()->route('client-portal.login')
                ->with('error', 'Your account has been suspended.');
        }

        return $next($request);
    }
}
