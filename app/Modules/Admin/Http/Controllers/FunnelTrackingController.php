<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Models\FunnelEmailLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class FunnelTrackingController extends Controller
{
    /**
     * 1x1 transparent GIF (base64 encoded)
     */
    private const TRANSPARENT_GIF = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    /**
     * Track email open via tracking pixel
     */
    public function trackOpen(string $uuid): Response
    {
        $log = FunnelEmailLog::where('uuid', $uuid)->first();

        if ($log) {
            $log->recordOpen();
        }

        // Return 1x1 transparent GIF
        return response(base64_decode(self::TRANSPARENT_GIF))
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Track link click and redirect to original URL
     */
    public function trackClick(string $uuid, string $linkId): RedirectResponse
    {
        $originalUrl = base64_decode($linkId);

        // Validate URL
        if (!$originalUrl || !filter_var($originalUrl, FILTER_VALIDATE_URL)) {
            // Fallback to a safe URL if decoding fails
            $originalUrl = config('app.url');
        }

        $log = FunnelEmailLog::where('uuid', $uuid)->first();

        if ($log) {
            $log->recordClick($originalUrl);
        }

        return redirect($originalUrl);
    }
}
