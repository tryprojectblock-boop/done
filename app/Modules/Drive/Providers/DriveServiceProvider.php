<?php

declare(strict_types=1);

namespace App\Modules\Drive\Providers;

use App\Modules\Core\Providers\ModuleServiceProvider;

final class DriveServiceProvider extends ModuleServiceProvider
{
    public function getModuleName(): string
    {
        return 'Drive';
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
