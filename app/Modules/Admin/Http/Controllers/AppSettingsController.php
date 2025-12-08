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
            'maintenance_mode' => 'boolean',
            'registration_enabled' => 'boolean',
        ]);

        // Store settings
        foreach ($validated as $key => $value) {
            $this->setSetting($key, $value);
        }

        Cache::forget('app_settings');

        return back()->with('success', 'App settings updated successfully.');
    }

    protected function getSettings(): array
    {
        return Cache::remember('app_settings', 3600, function () {
            return [
                'app_name' => $this->getSetting('app_name', config('app.name')),
                'support_email' => $this->getSetting('support_email', 'support@example.com'),
                'default_trial_days' => $this->getSetting('default_trial_days', 14),
                'max_workspaces_per_company' => $this->getSetting('max_workspaces_per_company', 10),
                'max_users_per_company' => $this->getSetting('max_users_per_company', 50),
                'max_storage_per_company_gb' => $this->getSetting('max_storage_per_company_gb', 10),
                'maintenance_mode' => $this->getSetting('maintenance_mode', false),
                'registration_enabled' => $this->getSetting('registration_enabled', true),
            ];
        });
    }

    protected function getSetting(string $key, mixed $default = null): mixed
    {
        // You could store these in a database settings table
        // For now, we'll use a simple JSON file
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
