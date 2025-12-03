<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotSuspended
{
    /**
     * Handle an incoming request.
     *
     * Check if the authenticated user is suspended and log them out if so.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Check if user is suspended
            if ($user->status === User::STATUS_SUSPENDED) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Your account has been suspended. Please contact an administrator.',
                    ], 403);
                }

                return redirect()->route('login')
                    ->with('error', 'Your account has been suspended. Please contact an administrator.');
            }
        }

        return $next($request);
    }
}
