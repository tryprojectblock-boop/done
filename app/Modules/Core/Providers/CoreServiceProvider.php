<?php

declare(strict_types=1);

namespace App\Modules\Core\Providers;

use App\Modules\Core\Contracts\EncryptionInterface;
use App\Modules\Core\Contracts\FileUploadInterface;
use App\Modules\Core\Services\Encryption\EncryptionService;
use App\Modules\Core\Services\FileUpload\FileUploadService;
use Illuminate\Support\ServiceProvider;

final class CoreServiceProvider extends ServiceProvider
{
    public array $singletons = [
        FileUploadInterface::class => FileUploadService::class,
        EncryptionInterface::class => EncryptionService::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(
            $this->getModulePath('Config/core.php'),
            'core'
        );

        $this->registerHelpers();
    }

    public function boot(): void
    {
        $this->publishConfig();
    }

    protected function registerHelpers(): void
    {
        $helpersPath = $this->getModulePath('Support/helpers.php');

        if (file_exists($helpersPath)) {
            require_once $helpersPath;
        }
    }

    protected function publishConfig(): void
    {
        $this->publishes([
            $this->getModulePath('Config/core.php') => config_path('core.php'),
        ], 'core-config');
    }

    protected function getModulePath(string $path = ''): string
    {
        $basePath = app_path('Modules/Core');

        return $path ? "{$basePath}/{$path}" : $basePath;
    }
}
