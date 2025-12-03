<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Providers;

use App\Modules\Core\Providers\ModuleServiceProvider;
use App\Modules\Workspace\Contracts\WorkspaceServiceInterface;
use App\Modules\Workspace\Events\MemberInvited;
use App\Modules\Workspace\Events\WorkspaceCreated;
use App\Modules\Workspace\Listeners\CreateDefaultWorkflows;
use App\Modules\Workspace\Listeners\SendWorkspaceInvitationEmail;
use App\Modules\Workspace\Services\WorkspaceService;
use Illuminate\Support\Facades\Event;

final class WorkspaceServiceProvider extends ModuleServiceProvider
{
    public function getModuleName(): string
    {
        return 'Workspace';
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
            WorkspaceServiceInterface::class,
            WorkspaceService::class
        );
    }

    protected function bootModule(): void
    {
        $this->registerPolicies();
        $this->registerEventListeners();
    }

    protected function registerPolicies(): void
    {
        // Policies will be registered here when we create them
    }

    protected function registerEventListeners(): void
    {
        Event::listen(
            MemberInvited::class,
            SendWorkspaceInvitationEmail::class
        );

        Event::listen(
            WorkspaceCreated::class,
            CreateDefaultWorkflows::class
        );
    }
}
