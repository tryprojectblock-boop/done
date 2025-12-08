<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class AppSettings
{
    protected static ?array $settings = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::all();
        return $settings[$key] ?? $default;
    }

    public static function all(): array
    {
        if (static::$settings !== null) {
            return static::$settings;
        }

        static::$settings = Cache::remember('app_settings', 3600, function () {
            return static::loadFromFile();
        });

        return static::$settings;
    }

    public static function isRegistrationEnabled(): bool
    {
        return (bool) static::get('registration_enabled', true);
    }

    public static function isMaintenanceMode(): bool
    {
        return (bool) static::get('maintenance_mode', false);
    }

    public static function getMaintenanceMessage(): ?string
    {
        return static::get('maintenance_message');
    }

    public static function getMaintenanceUntil(): ?string
    {
        return static::get('maintenance_until');
    }

    public static function clearCache(): void
    {
        static::$settings = null;
        Cache::forget('app_settings');
    }

    protected static function loadFromFile(): array
    {
        $path = storage_path('app/admin_settings.json');

        if (file_exists($path)) {
            $content = file_get_contents($path);
            return json_decode($content, true) ?? [];
        }

        return [];
    }
}
