<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AppSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRegistrationEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!AppSettings::isRegistrationEnabled()) {
            // For API requests, return JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration is currently disabled. Please try again later.',
                ], 403);
            }

            // For web requests, redirect to login with message
            return redirect()->route('login')
                ->with('error', 'Registration is currently disabled. Please try again later.');
        }

        return $next($request);
    }
}
