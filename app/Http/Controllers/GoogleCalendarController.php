<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GoogleCalendarController extends Controller
{
    public function __construct(
        protected GoogleCalendarService $googleCalendarService
    ) {}

    /**
     * Redirect to Google OAuth authorization.
     */
    public function connect(Request $request): RedirectResponse
    {
        $user = $request->user();
        $company = $user->company;

        // Check if company has Google sync enabled
        if (!$user->companyHasGoogleSyncEnabled()) {
            return redirect()->route('profile.index')
                ->with('error', 'Google Calendar sync is not enabled for your organization. Contact your admin to enable it.');
        }

        // Check if Google API is configured for this company
        if (!$this->googleCalendarService->isConfigured($company)) {
            return redirect()->route('profile.index')
                ->with('error', 'Google Calendar integration is not configured. Contact your administrator.');
        }

        $authUrl = $this->googleCalendarService->getClient($company)->createAuthUrl();

        return redirect()->away($authUrl);
    }

    /**
     * Handle Google OAuth callback.
     */
    public function callback(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please log in to connect your Google account.');
        }

        if ($request->has('error')) {
            return redirect()->route('profile.index')
                ->with('error', 'Google authorization was cancelled or failed: ' . $request->get('error'));
        }

        $code = $request->get('code');
        if (!$code) {
            return redirect()->route('profile.index')
                ->with('error', 'Invalid authorization response from Google.');
        }

        // Set company context for the service
        $this->googleCalendarService->setCompany($user->company);
        $success = $this->googleCalendarService->handleCallback($code, $user);

        if ($success) {
            return redirect()->route('profile.index')
                ->with('success', 'Google Calendar connected successfully! Your tasks will now sync with Google Calendar.');
        }

        return redirect()->route('profile.index')
            ->with('error', 'Failed to connect Google Calendar. Please try again.');
    }

    /**
     * Disconnect Google account.
     */
    public function disconnect(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->disconnectGoogle();

        return redirect()->route('profile.index')
            ->with('success', 'Google Calendar has been disconnected.');
    }

    /**
     * Trigger manual sync.
     */
    public function sync(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->canSyncGoogleCalendar()) {
            return response()->json([
                'success' => false,
                'message' => 'Google Calendar sync is not available. Please connect your Google account first.',
            ], 400);
        }

        // Set company context for the service
        $this->googleCalendarService->setCompany($user->company);

        // Get user's default workspace
        $defaultWorkspace = $user->company?->workspaces()->first();
        if (!$defaultWorkspace) {
            return response()->json([
                'success' => false,
                'message' => 'No workspace found for syncing.',
            ], 400);
        }

        $results = $this->googleCalendarService->performFullSync($user, $defaultWorkspace->id);

        return response()->json([
            'success' => true,
            'message' => "Sync completed. {$results['synced_to_google']} tasks synced to Google, {$results['synced_from_google']} events synced from Google.",
            'data' => $results,
        ]);
    }

    /**
     * Get sync status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'google_connected' => $user->hasGoogleConnected(),
                'company_enabled' => $user->companyHasGoogleSyncEnabled(),
                'can_sync' => $user->canSyncGoogleCalendar(),
                'connected_at' => $user->google_connected_at?->toISOString(),
            ],
        ]);
    }
}
