<?php

declare(strict_types=1);

namespace App\Modules\Task\Providers;

use App\Modules\Core\Providers\ModuleServiceProvider;
use App\Modules\Task\Contracts\TaskServiceInterface;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Policies\TaskPolicy;
use App\Modules\Task\Services\TaskService;
use Illuminate\Support\Facades\Gate;

final class TaskServiceProvider extends ModuleServiceProvider
{
    public function getModuleName(): string
    {
        return 'Task';
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
            TaskServiceInterface::class,
            TaskService::class
        );
    }

    protected function bootModule(): void
    {
        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Task::class, TaskPolicy::class);
    }
}
