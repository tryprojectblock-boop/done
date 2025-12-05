<?php

declare(strict_types=1);

namespace App\Modules\Idea\Providers;

use App\Modules\Core\Providers\ModuleServiceProvider;
use App\Modules\Idea\Contracts\IdeaServiceInterface;
use App\Modules\Idea\Services\IdeaService;

final class IdeaServiceProvider extends ModuleServiceProvider
{
    public function getModuleName(): string
    {
        return 'Idea';
    }

    public function getRouteFiles(): array
    {
        return [
            'web' => $this->getModulePath('Routes/web.php'),
        ];
    }

    public function getViewPaths(): array
    {
        return [$this->getModulePath('Views')];
    }

    protected function registerBindings(): void
    {
        $this->app->singleton(
            IdeaServiceInterface::class,
            IdeaService::class
        );
    }

    protected function bootModule(): void
    {
        // Register policies if needed
    }
}
