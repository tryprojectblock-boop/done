<?php

declare(strict_types=1);

namespace App\Modules\Document\Providers;

use App\Modules\Document\Contracts\DocumentServiceInterface;
use App\Modules\Document\Services\DocumentService;
use Illuminate\Support\ServiceProvider;

class DocumentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DocumentServiceInterface::class, DocumentService::class);
    }

    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Views', 'document');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
