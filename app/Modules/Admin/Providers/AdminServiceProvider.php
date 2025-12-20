<?php

namespace App\Modules\Admin\Providers;

use App\Modules\Admin\Console\Commands\CreateAdminCommand;
use App\Modules\Admin\Console\Commands\ProcessFunnels;
use App\Modules\Admin\Http\Middleware\AdminAuthenticate;
use App\Modules\Admin\Http\Middleware\AdminIsAdministrator;
use App\Modules\Admin\Listeners\FunnelTagListener;
use App\Modules\Admin\Observers\FunnelTaskObserver;
use App\Modules\Auth\Events\RegistrationCompleted;
use App\Modules\Auth\Events\UserRegistered;
use App\Modules\Task\Models\Task;
use App\Modules\Workspace\Events\MemberInvited;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'admin');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->registerMiddleware();
        $this->registerRoutes();
        $this->registerCommands();
        $this->registerEventListeners();
        $this->registerObservers();
    }

    /**
     * Register admin middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('admin.auth', AdminAuthenticate::class);
        $router->aliasMiddleware('admin.administrator', AdminIsAdministrator::class);
    }

    /**
     * Register admin routes.
     */
    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../Routes/web.php');
    }

    /**
     * Register admin console commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateAdminCommand::class,
                ProcessFunnels::class,
            ]);
        }
    }

    /**
     * Register event listeners for funnel tags.
     */
    protected function registerEventListeners(): void
    {
        Event::listen(UserRegistered::class, [FunnelTagListener::class, 'handleUserRegistered']);
        Event::listen(RegistrationCompleted::class, [FunnelTagListener::class, 'handleRegistrationCompleted']);
        Event::listen(MemberInvited::class, [FunnelTagListener::class, 'handleMemberInvited']);
    }

    /**
     * Register model observers for funnel tags.
     */
    protected function registerObservers(): void
    {
        Task::observe(FunnelTaskObserver::class);
    }
}
