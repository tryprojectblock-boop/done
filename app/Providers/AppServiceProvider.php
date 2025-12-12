<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\LogOutgoingMail;
use App\View\Composers\PlanLimitsComposer;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
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
        \App\Modules\Drive\Providers\DriveServiceProvider::class,
        \App\Modules\Calendar\Providers\CalendarServiceProvider::class,
        \App\Modules\Document\Providers\DocumentServiceProvider::class,
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
        // Register view composer for plan limits (shared with navigation)
        View::composer('partials.navigation', PlanLimitsComposer::class);

        // Register mail logging listener
        Event::listen(MessageSending::class, LogOutgoingMail::class);
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
