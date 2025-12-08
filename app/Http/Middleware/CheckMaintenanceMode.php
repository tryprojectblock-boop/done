<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AppSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isMaintenanceMode() && !$this->shouldPassThrough($request)) {
            // For API requests, return JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => AppSettings::getMaintenanceMessage() ?? 'We are currently performing maintenance. Please check back soon.',
                    'maintenance_until' => AppSettings::getMaintenanceUntil(),
                ], 503);
            }

            // For web requests, show maintenance page
            return response()->view('maintenance', [
                'message' => AppSettings::getMaintenanceMessage() ?? 'We are currently performing maintenance. Please check back soon.',
                'until' => AppSettings::getMaintenanceUntil(),
            ], 503);
        }

        return $next($request);
    }

    protected function isMaintenanceMode(): bool
    {
        if (!AppSettings::isMaintenanceMode()) {
            return false;
        }

        // Check if maintenance period has ended
        $until = AppSettings::getMaintenanceUntil();
        if ($until && now()->isAfter($until)) {
            return false;
        }

        return true;
    }

    protected function shouldPassThrough(Request $request): bool
    {
        // Only allow admin panel (backoffice) access during maintenance
        if ($request->is('backoffice/*') || $request->is('backoffice')) {
            return true;
        }

        // Allow authenticated admin users to browse anywhere
        if (auth()->guard('admin')->check()) {
            return true;
        }

        return false;
    }
}
