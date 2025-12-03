<?php

declare(strict_types=1);

namespace App\Modules\Auth\Providers;

use App\Modules\Auth\Contracts\RegistrationServiceInterface;
use App\Modules\Auth\Services\RegistrationService;
use App\Modules\Core\Providers\ModuleServiceProvider;

final class AuthServiceProvider extends ModuleServiceProvider
{
    public function getModuleName(): string
    {
        return 'Auth';
    }

    public function getRouteFiles(): array
    {
        return [
            'web' => $this->getModulePath('Routes/web.php'),
            'api' => $this->getModulePath('Routes/api.php'),
        ];
    }

    public function getViewPaths(): array
    {
        return [$this->getModulePath('Views')];
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(
            RegistrationServiceInterface::class,
            RegistrationService::class
        );
    }
}
