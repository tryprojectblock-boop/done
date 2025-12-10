<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    /**
     * Redirect to settings marketplace tab.
     * Marketplace is now part of settings.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('settings.index', ['tab' => 'marketplace']);
    }

    /**
     * Display the Two-Factor Authentication detail page.
     */
    public function twoFactor(Request $request): View
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to access this feature.');
        }

        $company = $user->company;
        $twoFactorStatus = $this->getTwoFactorStatus($company);

        return view('marketplace.two-factor', [
            'user' => $user,
            'company' => $company,
            'twoFactorStatus' => $twoFactorStatus,
        ]);
    }

    /**
     * Enable Two-Factor Authentication for the company.
     */
    public function enableTwoFactor(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to enable this feature.');
        }

        $company = $user->company;

        // Update company settings to enable 2FA requirement
        $settings = $company->settings ?? [];
        $settings['two_factor_enabled'] = true;
        $settings['two_factor_enabled_at'] = now()->toISOString();
        $settings['two_factor_enabled_by'] = $user->id;

        $company->update(['settings' => $settings]);

        return redirect()->route('marketplace.two-factor')
            ->with('success', 'Two-Factor Authentication has been enabled for your organization.');
    }

    /**
     * Disable Two-Factor Authentication for the company.
     */
    public function disableTwoFactor(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to disable this feature.');
        }

        $company = $user->company;

        // Update company settings to disable 2FA requirement
        $settings = $company->settings ?? [];
        $settings['two_factor_enabled'] = false;
        $settings['two_factor_disabled_at'] = now()->toISOString();
        $settings['two_factor_disabled_by'] = $user->id;

        $company->update(['settings' => $settings]);

        return redirect()->route('marketplace.two-factor')
            ->with('success', 'Two-Factor Authentication has been disabled for your organization.');
    }

    /**
     * Get the Two-Factor Authentication status for a company.
     */
    private function getTwoFactorStatus($company): array
    {
        $settings = $company->settings ?? [];
        $isEnabled = $settings['two_factor_enabled'] ?? false;

        // Check if 2FA package is installed (for "Not Installed" status)
        // For now, we'll assume it's always "installed" since we're implementing it
        $isInstalled = true;

        return [
            'installed' => $isInstalled,
            'enabled' => $isEnabled,
            'status' => !$isInstalled ? 'not_installed' : ($isEnabled ? 'enabled' : 'disabled'),
            'status_label' => !$isInstalled ? 'Not Installed' : ($isEnabled ? 'Enabled' : 'Disabled'),
            'status_color' => !$isInstalled ? 'ghost' : ($isEnabled ? 'success' : 'warning'),
        ];
    }

    /**
     * Display the Gmail Calendar Sync detail page.
     */
    public function gmailSync(Request $request): View
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to access this feature.');
        }

        $company = $user->company;
        $gmailSyncStatus = $this->getGmailSyncStatus($company);

        return view('marketplace.gmail-sync', [
            'user' => $user,
            'company' => $company,
            'gmailSyncStatus' => $gmailSyncStatus,
        ]);
    }

    /**
     * Enable Gmail Calendar Sync for the company.
     */
    public function enableGmailSync(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to enable this feature.');
        }

        $company = $user->company;

        // Update company settings to enable Gmail Sync
        $settings = $company->settings ?? [];
        $settings['gmail_sync_enabled'] = true;
        $settings['gmail_sync_enabled_at'] = now()->toISOString();
        $settings['gmail_sync_enabled_by'] = $user->id;

        $company->update(['settings' => $settings]);

        return redirect()->route('marketplace.gmail-sync')
            ->with('success', 'Gmail Calendar Sync has been enabled for your organization. Team members can now connect their Google accounts.');
    }

    /**
     * Disable Gmail Calendar Sync for the company.
     */
    public function disableGmailSync(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to disable this feature.');
        }

        $company = $user->company;

        // Update company settings to disable Gmail Sync
        $settings = $company->settings ?? [];
        $settings['gmail_sync_enabled'] = false;
        $settings['gmail_sync_disabled_at'] = now()->toISOString();
        $settings['gmail_sync_disabled_by'] = $user->id;

        $company->update(['settings' => $settings]);

        return redirect()->route('marketplace.gmail-sync')
            ->with('success', 'Gmail Calendar Sync has been disabled for your organization.');
    }

    /**
     * Get the Gmail Calendar Sync status for a company.
     */
    private function getGmailSyncStatus($company): array
    {
        $settings = $company->settings ?? [];
        $isEnabled = $settings['gmail_sync_enabled'] ?? false;

        // Check if Gmail API credentials are configured in company settings
        $isInstalled = !empty($settings['google_client_id']) && !empty($settings['google_client_secret']);

        return [
            'installed' => $isInstalled,
            'enabled' => $isEnabled,
            'status' => !$isInstalled ? 'not_installed' : ($isEnabled ? 'enabled' : 'disabled'),
            'status_label' => !$isInstalled ? 'Not Configured' : ($isEnabled ? 'Enabled' : 'Disabled'),
            'status_color' => !$isInstalled ? 'ghost' : ($isEnabled ? 'success' : 'warning'),
        ];
    }

    /**
     * Display the Milestones module detail page.
     */
    public function milestones(Request $request): View
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to access this feature.');
        }

        $company = $user->company;
        $milestoneStatus = $this->getMilestoneStatus($company);

        return view('marketplace.milestones', [
            'user' => $user,
            'company' => $company,
            'milestoneStatus' => $milestoneStatus,
        ]);
    }

    /**
     * Enable Milestones module for the company.
     */
    public function enableMilestones(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to enable this feature.');
        }

        $company = $user->company;

        $settings = $company->settings ?? [];
        $settings['milestones_enabled'] = true;
        $settings['milestones_enabled_at'] = now()->toISOString();
        $settings['milestones_enabled_by'] = $user->id;

        $company->update(['settings' => $settings]);

        return redirect()->route('marketplace.milestones')
            ->with('success', 'Milestones module has been enabled for your organization.');
    }

    /**
     * Disable Milestones module for the company.
     */
    public function disableMilestones(Request $request)
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to disable this feature.');
        }

        $company = $user->company;

        $settings = $company->settings ?? [];
        $settings['milestones_enabled'] = false;
        $settings['milestones_disabled_at'] = now()->toISOString();
        $settings['milestones_disabled_by'] = $user->id;

        $company->update(['settings' => $settings]);

        return redirect()->route('marketplace.milestones')
            ->with('success', 'Milestones module has been disabled for your organization.');
    }

    /**
     * Get the Milestones module status for a company.
     */
    private function getMilestoneStatus($company): array
    {
        $settings = $company->settings ?? [];
        $isEnabled = $settings['milestones_enabled'] ?? true; // Enabled by default

        return [
            'installed' => true,
            'enabled' => $isEnabled,
            'status' => $isEnabled ? 'enabled' : 'disabled',
            'status_label' => $isEnabled ? 'Enabled' : 'Disabled',
            'status_color' => $isEnabled ? 'success' : 'warning',
        ];
    }
}
