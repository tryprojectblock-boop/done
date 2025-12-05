<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Module service providers to register.
     *
     * @var array<class-string>
     */
    protected array $moduleProviders = [
        \App\Modules\Core\Providers\CoreServiceProvider::class,
        \App\Modules\Workspace\Providers\WorkspaceServiceProvider::class,
        \App\Modules\Auth\Providers\AuthServiceProvider::class,
        \App\Modules\Task\Providers\TaskServiceProvider::class,
        \App\Modules\Idea\Providers\IdeaServiceProvider::class,
        \App\Modules\Discussion\Providers\DiscussionServiceProvider::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerModules();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register all module service providers.
     */
    protected function registerModules(): void
    {
        foreach ($this->moduleProviders as $provider) {
            $this->app->register($provider);
        }
    }
}
