<?php

declare(strict_types=1);

use App\Modules\Core\Contracts\EncryptionInterface;
use App\Modules\Core\Contracts\FileUploadInterface;

if (! function_exists('file_upload')) {
    /**
     * Get the file upload service instance.
     */
    function file_upload(): FileUploadInterface
    {
        return app(FileUploadInterface::class);
    }
}

if (! function_exists('encryption')) {
    /**
     * Get the encryption service instance.
     */
    function encryption(): EncryptionInterface
    {
        return app(EncryptionInterface::class);
    }
}

if (! function_exists('format_bytes')) {
    /**
     * Format bytes to human readable format.
     */
    function format_bytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (! function_exists('is_tenant_context')) {
    /**
     * Check if we're currently in a tenant context.
     */
    function is_tenant_context(): bool
    {
        if (function_exists('tenant')) {
            return tenant() !== null;
        }

        return session()->has('current_tenant_id');
    }
}

if (! function_exists('current_tenant_id')) {
    /**
     * Get the current tenant ID.
     */
    function current_tenant_id(): ?int
    {
        if (function_exists('tenant') && tenant()) {
            return tenant()->id;
        }

        return session('current_tenant_id');
    }
}

if (! function_exists('module_path')) {
    /**
     * Get the path to a module directory.
     */
    function module_path(string $module, string $path = ''): string
    {
        $basePath = app_path("Modules/{$module}");

        return $path ? "{$basePath}/{$path}" : $basePath;
    }
}
