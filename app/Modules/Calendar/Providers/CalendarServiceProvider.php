<?php

declare(strict_types=1);

namespace App\Modules\Calendar\Providers;

use App\Modules\Core\Providers\ModuleServiceProvider;

final class CalendarServiceProvider extends ModuleServiceProvider
{
    public function getModuleName(): string
    {
        return 'Calendar';
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

    public function getMigrationPaths(): array
    {
        return [];
    }
}
