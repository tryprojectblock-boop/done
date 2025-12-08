<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Services\PlanLimitService;
use Illuminate\View\View;

class PlanLimitsComposer
{
    public function __construct(
        private readonly PlanLimitService $planLimitService
    ) {}

    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $user = auth()->user();

        if (!$user || !$user->company) {
            $view->with('planLimits', null);
            return;
        }

        $company = $user->company;
        $reachedLimits = $this->planLimitService->getReachedLimits($company);
        $usageSummary = $this->planLimitService->getUsageSummary($company);

        $view->with('planLimits', [
            'has_reached_limit' => !empty($reachedLimits),
            'reached_limits' => $reachedLimits,
            'usage' => $usageSummary,
            'plan_name' => $company->plan?->name ?? 'Free',
            'can_create_workspace' => $this->planLimitService->canCreateWorkspace($company),
            'can_add_team_member' => $this->planLimitService->canAddTeamMember($company),
            'can_upload_storage' => $this->planLimitService->canUploadStorage($company),
        ]);
    }
}
