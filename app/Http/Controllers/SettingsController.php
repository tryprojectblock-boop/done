<?php

namespace App\Http\Controllers;

use App\Models\MailLog;
use App\Modules\Admin\Models\Coupon;
use App\Modules\Admin\Models\Plan;
use App\Modules\Auth\Enums\CompanySize;
use App\Modules\Auth\Enums\IndustryType;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the settings index page.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $company = $user->company;
        $tab = $request->get('tab', 'general');

        // Get marketplace data if admin
        $twoFactorStatus = null;
        $gmailSyncStatus = null;
        $googleDriveStatus = null;
        $milestonesStatus = null;
        $outOfOfficeStatus = null;
        $moduleSettings = [];
        $defaultWorkspace = null;

        if ($user->isAdminOrHigher()) {
            $twoFactorStatus = $this->getTwoFactorStatus($company);
            $gmailSyncStatus = $this->getGmailSyncStatus($company);
            $googleDriveStatus = $this->getGoogleDriveStatus($company);
            $milestonesStatus = $this->getMilestonesStatus($company);
            $outOfOfficeStatus = $this->getOutOfOfficeStatus($company);
            $moduleSettings = $this->getModuleSettings($company);
            // Get first workspace for module links
            $defaultWorkspace = Workspace::first();
        }

        return view('settings.index', [
            'user' => $user,
            'company' => $company,
            'tab' => $tab,
            'twoFactorStatus' => $twoFactorStatus,
            'gmailSyncStatus' => $gmailSyncStatus,
            'googleDriveStatus' => $googleDriveStatus,
            'milestonesStatus' => $milestonesStatus,
            'outOfOfficeStatus' => $outOfOfficeStatus,
            'moduleSettings' => $moduleSettings,
            'defaultWorkspace' => $defaultWorkspace,
        ]);
    }

    /**
     * Get the Two-Factor Authentication status for a company.
     */
    private function getTwoFactorStatus($company): array
    {
        $settings = $company->settings ?? [];
        $isEnabled = $settings['two_factor_enabled'] ?? false;
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
     * Get the Gmail Calendar Sync status for a company.
     */
    private function getGmailSyncStatus($company): array
    {
        $settings = $company->settings ?? [];
        $isEnabled = $settings['gmail_sync_enabled'] ?? false;
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
     * Get the Google Drive integration status for a company.
     */
    private function getGoogleDriveStatus($company): array
    {
        $settings = $company->settings ?? [];
        $isEnabled = $settings['google_drive_enabled'] ?? false;
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
     * Get the Milestones module status for a company.
     */
    private function getMilestonesStatus($company): array
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

    /**
     * Get the Out of Office status for a company.
     */
    private function getOutOfOfficeStatus($company): array
    {
        $settings = $company->settings ?? [];
        $isEnabled = $settings['out_of_office_enabled'] ?? false;

        return [
            'installed' => true,
            'enabled' => $isEnabled,
            'status' => $isEnabled ? 'enabled' : 'disabled',
            'status_label' => $isEnabled ? 'Enabled' : 'Disabled',
            'status_color' => $isEnabled ? 'success' : 'warning',
        ];
    }

    /**
     * Get all module settings for a company.
     */
    private function getModuleSettings($company): array
    {
        $settings = $company->settings ?? [];

        return [
            'crm_enabled' => $settings['crm_enabled'] ?? true,
            'milestones_enabled' => $settings['milestones_enabled'] ?? true,
            'two_factor_enabled' => $settings['two_factor_enabled'] ?? false,
            'gmail_sync_enabled' => $settings['gmail_sync_enabled'] ?? false,
            'google_drive_enabled' => $settings['google_drive_enabled'] ?? false,
        ];
    }

    /**
     * Display company settings (admin/owner only).
     */
    public function company(Request $request): View
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to access company settings.');
        }

        $company = $user->company;

        return view('settings.company', [
            'user' => $user,
            'company' => $company,
            'companySizes' => CompanySize::cases(),
            'industryTypes' => IndustryType::cases(),
        ]);
    }

    /**
     * Update company settings.
     */
    public function updateCompany(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to update company settings.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'size' => ['nullable', 'string'],
            'industry_type' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $company = $user->company;

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }

            // Store new logo
            $path = $request->file('logo')->store('company-logos', 'public');
            $validated['logo_path'] = $path;
        }

        // Convert size and industry_type to enums if provided
        if (!empty($validated['size'])) {
            $validated['size'] = CompanySize::from($validated['size']);
        }
        if (!empty($validated['industry_type'])) {
            $validated['industry_type'] = IndustryType::from($validated['industry_type']);
        }

        unset($validated['logo']);
        $company->update($validated);

        return redirect()->route('settings.company')->with('success', 'Company settings updated successfully.');
    }

    /**
     * Delete company logo.
     */
    public function deleteCompanyLogo(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to update company settings.');
        }

        $company = $user->company;

        if ($company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $company->update(['logo_path' => null]);
        }

        return redirect()->route('settings.company')->with('success', 'Company logo removed.');
    }

    /**
     * Display notification settings.
     */
    public function notifications(Request $request): View
    {
        $user = $request->user();
        $settings = $user->settings ?? [];

        // Default notification settings
        $notificationSettings = array_merge([
            'email_task_assigned' => true,
            'email_task_updated' => true,
            'email_task_commented' => true,
            'email_task_due_soon' => true,
            'email_mentioned' => true,
            'email_discussion_reply' => true,
            'email_idea_commented' => true,
            'email_weekly_digest' => false,
            'browser_notifications' => true,
        ], $settings['notifications'] ?? []);

        return view('settings.notifications', [
            'user' => $user,
            'notificationSettings' => $notificationSettings,
        ]);
    }

    /**
     * Update notification settings.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $user = $request->user();

        $notificationSettings = [
            'email_task_assigned' => $request->boolean('email_task_assigned'),
            'email_task_updated' => $request->boolean('email_task_updated'),
            'email_task_commented' => $request->boolean('email_task_commented'),
            'email_task_due_soon' => $request->boolean('email_task_due_soon'),
            'email_mentioned' => $request->boolean('email_mentioned'),
            'email_discussion_reply' => $request->boolean('email_discussion_reply'),
            'email_idea_commented' => $request->boolean('email_idea_commented'),
            'email_weekly_digest' => $request->boolean('email_weekly_digest'),
            'browser_notifications' => $request->boolean('browser_notifications'),
        ];

        $settings = $user->settings ?? [];
        $settings['notifications'] = $notificationSettings;

        $user->update(['settings' => $settings]);

        return redirect()->route('settings.notifications')->with('success', 'Notification settings updated successfully.');
    }

    /**
     * Display appearance settings.
     */
    public function appearance(Request $request): View
    {
        $user = $request->user();
        $settings = $user->settings ?? [];

        $appearanceSettings = array_merge([
            'theme' => 'system',
            'sidebar_collapsed' => false,
            'compact_mode' => false,
        ], $settings['appearance'] ?? []);

        return view('settings.appearance', [
            'user' => $user,
            'appearanceSettings' => $appearanceSettings,
        ]);
    }

    /**
     * Update appearance settings.
     */
    public function updateAppearance(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'theme' => ['required', 'in:light,dark,system'],
        ]);

        $appearanceSettings = [
            'theme' => $validated['theme'],
            'sidebar_collapsed' => $request->boolean('sidebar_collapsed'),
            'compact_mode' => $request->boolean('compact_mode'),
        ];

        $settings = $user->settings ?? [];
        $settings['appearance'] = $appearanceSettings;

        $user->update(['settings' => $settings]);

        return redirect()->route('settings.appearance')->with('success', 'Appearance settings updated successfully.');
    }

    /**
     * Display billing settings.
     */
    public function billing(Request $request): View
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to access billing settings.');
        }

        $company = $user->company;
        $company->load('plan');

        // Calculate usage
        $usage = [
            'workspaces' => Workspace::whereIn('owner_id', $company->users->pluck('id'))->count(),
            'team_members' => $company->users->count(),
            'storage_gb' => 0, // TODO: Calculate actual storage usage
        ];

        return view('settings.billing', [
            'user' => $user,
            'company' => $company,
            'usage' => $usage,
        ]);
    }

    /**
     * Display available plans for selection.
     */
    public function plans(Request $request): View
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to access billing settings.');
        }

        $company = $user->company;
        $plans = Plan::active()->ordered()->get();
        $billingCycle = $request->get('cycle', '1_month');

        return view('settings.plans', [
            'user' => $user,
            'company' => $company,
            'plans' => $plans,
            'billingCycle' => $billingCycle,
        ]);
    }

    /**
     * Subscribe to a plan.
     */
    public function subscribe(Request $request, Plan $plan): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to change subscription.');
        }

        $validated = $request->validate([
            'billing_cycle' => 'required|in:1_month,3_month,6_month,12_month,3_year,5_year',
        ]);

        $company = $user->company;
        $billingCycle = $validated['billing_cycle'];

        // Calculate subscription end date based on billing cycle
        $duration = match($billingCycle) {
            '1_month' => 1,
            '3_month' => 3,
            '6_month' => 6,
            '12_month' => 12,
            '3_year' => 36,
            '5_year' => 60,
        };

        $subscriptionEndsAt = $plan->isFree() ? null : now()->addMonths($duration);

        $company->update([
            'plan_id' => $plan->id,
            'billing_cycle' => $plan->isFree() ? null : $billingCycle,
            'subscription_starts_at' => now(),
            'subscription_ends_at' => $subscriptionEndsAt,
            'trial_ends_at' => null, // End trial when subscribing
        ]);

        $message = $plan->isFree()
            ? 'Switched to Free plan successfully.'
            : "Subscribed to {$plan->name} plan successfully.";

        return redirect()->route('settings.billing')->with('success', $message);
    }

    /**
     * Display integrations settings (admin/owner only).
     */
    public function integrations(Request $request): View
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to access integration settings.');
        }

        $company = $user->company;
        $settings = $company->settings ?? [];

        $integrationSettings = [
            'google_client_id' => $settings['google_client_id'] ?? '',
            'google_client_secret' => $settings['google_client_secret'] ?? '',
            'google_redirect_uri' => $settings['google_redirect_uri'] ?? url('/auth/google/callback'),
            'gmail_sync_enabled' => $settings['gmail_sync_enabled'] ?? false,
        ];

        return view('settings.integrations', [
            'user' => $user,
            'company' => $company,
            'integrationSettings' => $integrationSettings,
        ]);
    }

    /**
     * Update integrations settings.
     */
    public function updateIntegrations(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to update integration settings.');
        }

        $validated = $request->validate([
            'google_client_id' => 'nullable|string|max:255',
            'google_client_secret' => 'nullable|string|max:255',
            'google_redirect_uri' => 'nullable|url|max:255',
        ]);

        $company = $user->company;
        $settings = $company->settings ?? [];

        $settings['google_client_id'] = $validated['google_client_id'] ?? '';
        $settings['google_client_secret'] = $validated['google_client_secret'] ?? '';
        $settings['google_redirect_uri'] = $validated['google_redirect_uri'] ?? url('/auth/google/callback');

        // Auto-enable Gmail sync if credentials are provided
        if (!empty($validated['google_client_id']) && !empty($validated['google_client_secret'])) {
            $settings['gmail_sync_enabled'] = $settings['gmail_sync_enabled'] ?? true;
        }

        $company->update(['settings' => $settings]);

        return redirect()->route('settings.integrations')
            ->with('success', 'Integration settings updated successfully.');
    }

    /**
     * Toggle Gmail Calendar Sync.
     */
    public function toggleGmailSync(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to change integration settings.');
        }

        $company = $user->company;
        $settings = $company->settings ?? [];

        // Check if Google API is configured
        if (empty($settings['google_client_id']) || empty($settings['google_client_secret'])) {
            return redirect()->route('settings.integrations')
                ->with('error', 'Please configure Google API credentials first.');
        }

        $settings['gmail_sync_enabled'] = !($settings['gmail_sync_enabled'] ?? false);
        $settings['gmail_sync_toggled_at'] = now()->toISOString();
        $settings['gmail_sync_toggled_by'] = $user->id;

        $company->update(['settings' => $settings]);

        $status = $settings['gmail_sync_enabled'] ? 'enabled' : 'disabled';
        return redirect()->route('settings.integrations')
            ->with('success', "Gmail Calendar Sync has been {$status}.");
    }

    /**
     * Apply a coupon code.
     */
    public function applyCoupon(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to apply coupons.');
        }

        $validated = $request->validate([
            'coupon_code' => 'required|string|max:50',
        ]);

        $coupon = Coupon::where('code', strtoupper($validated['coupon_code']))
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return back()->withErrors(['coupon_code' => 'Invalid or expired coupon code.']);
        }

        // Check if coupon is within valid date range
        if ($coupon->start_date && $coupon->start_date->isFuture()) {
            return back()->withErrors(['coupon_code' => 'This coupon is not yet active.']);
        }

        if ($coupon->end_date && $coupon->end_date->isPast()) {
            return back()->withErrors(['coupon_code' => 'This coupon has expired.']);
        }

        // Check usage limit
        if ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) {
            return back()->withErrors(['coupon_code' => 'This coupon has reached its usage limit.']);
        }

        $company = $user->company;

        // Apply coupon
        $company->update([
            'applied_coupon_code' => $coupon->code,
            'discount_percent' => $coupon->discount_percent,
        ]);

        // Increment coupon usage
        $coupon->increment('usage_count');

        return redirect()->route('settings.billing')
            ->with('success', "Coupon applied! You now have {$coupon->discount_percent}% off.");
    }

    /**
     * Display mail logs.
     */
    public function mailLogs(Request $request): View
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to view mail logs.');
        }

        $logs = MailLog::orderBy('created_at', 'desc')->paginate(20);

        return view('settings.mail-logs', [
            'user' => $user,
            'logs' => $logs,
        ]);
    }

    /**
     * Display a single mail log.
     */
    public function showMailLog(Request $request, MailLog $mailLog): View
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to view mail logs.');
        }

        return view('settings.mail-log-show', [
            'user' => $user,
            'mailLog' => $mailLog,
        ]);
    }

    /**
     * Delete a mail log.
     */
    public function deleteMailLog(Request $request, MailLog $mailLog): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to delete mail logs.');
        }

        $mailLog->delete();

        return redirect()->route('settings.mail-logs')->with('success', 'Mail log deleted.');
    }

    /**
     * Delete all mail logs.
     */
    public function clearMailLogs(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to delete mail logs.');
        }

        $count = MailLog::count();
        MailLog::truncate();

        return redirect()->route('settings.mail-logs')->with('success', "Cleared {$count} mail logs.");
    }
}
