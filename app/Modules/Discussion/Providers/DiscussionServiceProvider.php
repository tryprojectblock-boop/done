<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Providers;

use App\Modules\Discussion\Contracts\DiscussionServiceInterface;
use App\Modules\Discussion\Services\DiscussionService;
use Illuminate\Support\ServiceProvider;

class DiscussionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DiscussionServiceInterface::class, DiscussionService::class);
    }

    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Views', 'discussion');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
