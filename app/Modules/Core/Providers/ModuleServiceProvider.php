<?php

declare(strict_types=1);

namespace App\Modules\Core\Providers;

use App\Modules\Core\Contracts\ModuleServiceProviderInterface;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

abstract class ModuleServiceProvider extends ServiceProvider implements ModuleServiceProviderInterface
{
    protected string $moduleName;
    protected string $moduleNameLower;

    public function register(): void
    {
        $this->moduleName = $this->getModuleName();
        $this->moduleNameLower = strtolower($this->moduleName);

        $this->registerConfig();
        $this->registerBindings();
    }

    public function boot(): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        $this->registerRoutes();
        $this->registerViews();
        $this->registerMigrations();
        $this->registerCommands();
        $this->bootModule();
    }

    abstract public function getModuleName(): string;

    public function getRouteFiles(): array
    {
        return [
            'web' => $this->getModulePath('Routes/web.php'),
            'api' => $this->getModulePath('Routes/api.php'),
        ];
    }

    public function getMigrationPaths(): array
    {
        return [$this->getModulePath('Database/Migrations')];
    }

    public function getViewPaths(): array
    {
        return [$this->getModulePath('Views')];
    }

    public function getConfigFiles(): array
    {
        return [];
    }

    public function isEnabled(): bool
    {
        return config("modules.{$this->moduleNameLower}.enabled", true);
    }

    protected function registerBindings(): void
    {
        // Override in child classes to register module-specific bindings
    }

    protected function bootModule(): void
    {
        // Override in child classes for additional boot logic
    }

    protected function registerCommands(): void
    {
        // Override in child classes to register module-specific commands
    }

    protected function registerConfig(): void
    {
        foreach ($this->getConfigFiles() as $key => $path) {
            if (file_exists($path)) {
                $this->mergeConfigFrom($path, is_string($key) ? $key : $this->moduleNameLower);
            }
        }
    }

    protected function registerRoutes(): void
    {
        $routeFiles = $this->getRouteFiles();

        if (isset($routeFiles['web']) && file_exists($routeFiles['web'])) {
            Route::middleware('web')
                ->group($routeFiles['web']);
        }

        if (isset($routeFiles['api']) && file_exists($routeFiles['api'])) {
            Route::middleware('api')
                ->prefix('api')
                ->group($routeFiles['api']);
        }
    }

    protected function registerViews(): void
    {
        foreach ($this->getViewPaths() as $path) {
            if (is_dir($path)) {
                $this->loadViewsFrom($path, $this->moduleNameLower);
            }
        }
    }

    protected function registerMigrations(): void
    {
        foreach ($this->getMigrationPaths() as $path) {
            if (is_dir($path)) {
                $this->loadMigrationsFrom($path);
            }
        }
    }

    protected function getModulePath(string $path = ''): string
    {
        $basePath = app_path("Modules/{$this->moduleName}");

        return $path ? "{$basePath}/{$path}" : $basePath;
    }
}
