<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Models\Company;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AppController extends Controller
{
    public function index(): View
    {
        $settings = $this->getSettings();

        // Get counts for factory reset info
        $counts = [
            'companies' => Company::count(),
            'users' => User::count(),
            'workspaces' => Workspace::count(),
        ];

        return view('admin::app.index', compact('settings', 'counts'));
    }

    public function enableMaintenanceMode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'maintenance_until' => 'nullable|date|after:now',
            'maintenance_message' => 'nullable|string|max:500',
        ]);

        $this->setSetting('maintenance_mode', true);
        $this->setSetting('maintenance_until', $validated['maintenance_until'] ?? null);
        $this->setSetting('maintenance_message', $validated['maintenance_message'] ?? 'We are currently performing maintenance. Please check back soon.');

        Cache::forget('app_settings');

        return back()->with('success', 'Maintenance mode enabled successfully.');
    }

    public function disableMaintenanceMode(): RedirectResponse
    {
        $this->setSetting('maintenance_mode', false);
        $this->setSetting('maintenance_until', null);
        $this->setSetting('maintenance_message', null);

        Cache::forget('app_settings');

        return back()->with('success', 'Maintenance mode disabled successfully.');
    }

    public function factoryReset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'confirmation' => 'required|string|in:RESET',
        ]);

        try {
            DB::beginTransaction();

            // Delete all workspaces (this will cascade to tasks, etc.)
            Workspace::query()->forceDelete();

            // Delete all users
            User::query()->forceDelete();

            // Delete all companies
            Company::query()->forceDelete();

            DB::commit();

            // Clear all caches
            Cache::flush();

            return back()->with('success', 'Factory reset completed successfully. All clients, users, and workspaces have been deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Factory reset failed: ' . $e->getMessage());
        }
    }

    protected function getSettings(): array
    {
        // Read directly from file to get fresh values
        $allSettings = $this->loadSettingsFile();

        return [
            'maintenance_mode' => $allSettings['maintenance_mode'] ?? false,
            'maintenance_until' => $allSettings['maintenance_until'] ?? null,
            'maintenance_message' => $allSettings['maintenance_message'] ?? null,
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
