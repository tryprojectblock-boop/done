<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageUsers
{
    /**
     * Handle an incoming request.
     * Only allow users with admin or higher role to access user management.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isAdminOrHigher()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
            }

            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}
