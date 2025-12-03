<?php

declare(strict_types=1);

namespace App\Modules\Core\Contracts;

interface ModuleServiceProviderInterface
{
    /**
     * Get the module name.
     */
    public function getModuleName(): string;

    /**
     * Get the module's route files.
     */
    public function getRouteFiles(): array;

    /**
     * Get the module's migration paths.
     */
    public function getMigrationPaths(): array;

    /**
     * Get the module's view paths.
     */
    public function getViewPaths(): array;

    /**
     * Get the module's config files.
     */
    public function getConfigFiles(): array;

    /**
     * Check if the module is enabled.
     */
    public function isEnabled(): bool;
}
