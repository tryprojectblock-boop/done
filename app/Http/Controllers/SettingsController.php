<?php

namespace App\Http\Controllers;

use App\Modules\Auth\Enums\CompanySize;
use App\Modules\Auth\Enums\IndustryType;
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

        return view('settings.index', [
            'user' => $user,
            'company' => $company,
        ]);
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
}
