<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class IntegrationsController extends Controller
{
    public function index(): View
    {
        $settings = $this->getSettings();

        return view('admin::settings.integrations', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'google_client_id' => 'nullable|string|max:255',
            'google_client_secret' => 'nullable|string|max:255',
            'google_redirect_uri' => 'nullable|url|max:255',
        ]);

        // Store settings
        foreach ($validated as $key => $value) {
            $this->setSetting($key, $value);
        }

        Cache::forget('app_settings');

        return back()->with('success', 'Integration settings updated successfully.');
    }

    protected function getSettings(): array
    {
        $allSettings = $this->loadSettingsFile();

        return [
            'google_client_id' => $allSettings['google_client_id'] ?? '',
            'google_client_secret' => $allSettings['google_client_secret'] ?? '',
            'google_redirect_uri' => $allSettings['google_redirect_uri'] ?? url('/auth/google/callback'),
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
