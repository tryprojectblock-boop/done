<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class AppSettingsController extends Controller
{
    public function index(): View
    {
        $settings = $this->getSettings();

        return view('admin::settings.app-settings', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'support_email' => 'required|email',
            'default_trial_days' => 'required|integer|min:0|max:365',
            'max_workspaces_per_company' => 'required|integer|min:1|max:100',
            'max_users_per_company' => 'required|integer|min:1|max:1000',
            'max_storage_per_company_gb' => 'required|integer|min:1|max:100',
        ]);

        // Store settings
        foreach ($validated as $key => $value) {
            $this->setSetting($key, $value);
        }

        // Handle checkbox - unchecked checkboxes don't send any value
        $this->setSetting('registration_enabled', $request->has('registration_enabled'));

        Cache::forget('app_settings');

        return back()->with('success', 'App settings updated successfully.');
    }

    protected function getSettings(): array
    {
        // Read directly from file to get fresh values (avoid cache issues with checkboxes)
        $allSettings = $this->loadSettingsFile();

        return [
            'app_name' => $allSettings['app_name'] ?? config('app.name'),
            'support_email' => $allSettings['support_email'] ?? 'support@example.com',
            'default_trial_days' => $allSettings['default_trial_days'] ?? 14,
            'max_workspaces_per_company' => $allSettings['max_workspaces_per_company'] ?? 10,
            'max_users_per_company' => $allSettings['max_users_per_company'] ?? 50,
            'max_storage_per_company_gb' => $allSettings['max_storage_per_company_gb'] ?? 10,
            'registration_enabled' => $allSettings['registration_enabled'] ?? true,
        ];
    }

    protected function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->loadSettingsFile();
        return $settings[$key] ?? $default;
    }

    protected function setSetting(string $key, mixed $value): void
    {
        $settings = $this->loadSettingsFile();
        $settings[$key] = $value;
        $this->saveSettingsFile($settings);
    }

    protected function loadSettingsFile(): array
    {
        $path = storage_path('app/admin_settings.json');
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true) ?? [];
        }
        return [];
    }

    protected function saveSettingsFile(array $settings): void
    {
        $path = storage_path('app/admin_settings.json');
        file_put_contents($path, json_encode($settings, JSON_PRETTY_PRINT));
    }
}
