<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Modules\Auth\Models\Company;
use App\Modules\Workspace\Models\Workspace;

class PlanLimitService
{
    /**
     * Get the current usage statistics for a company
     */
    public function getUsage(Company $company): array
    {
        return [
            'workspaces' => $this->getWorkspaceCount($company),
            'team_members' => $this->getTeamMemberCount($company),
            'storage_gb' => $this->getStorageUsageGb($company),
        ];
    }

    /**
     * Get the plan limits for a company
     */
    public function getLimits(Company $company): array
    {
        $plan = $company->plan;

        return [
            'workspaces' => $plan?->workspace_limit ?? 1,
            'team_members' => $plan?->team_member_limit ?? 5,
            'storage_gb' => $plan?->storage_limit_gb ?? 5,
        ];
    }

    /**
     * Check if any limit has been reached
     */
    public function hasReachedAnyLimit(Company $company): bool
    {
        return $this->hasReachedWorkspaceLimit($company)
            || $this->hasReachedTeamMemberLimit($company)
            || $this->hasReachedStorageLimit($company);
    }

    /**
     * Get which limits have been reached
     */
    public function getReachedLimits(Company $company): array
    {
        $reached = [];

        if ($this->hasReachedWorkspaceLimit($company)) {
            $reached[] = 'workspaces';
        }

        if ($this->hasReachedTeamMemberLimit($company)) {
            $reached[] = 'team_members';
        }

        if ($this->hasReachedStorageLimit($company)) {
            $reached[] = 'storage';
        }

        return $reached;
    }

    /**
     * Check if the company can create a new workspace
     */
    public function canCreateWorkspace(Company $company): bool
    {
        $plan = $company->plan;

        // No plan = use defaults (limit of 1)
        if (!$plan) {
            return $this->getWorkspaceCount($company) < 1;
        }

        // 0 means unlimited
        if ($plan->workspace_limit === 0) {
            return true;
        }

        return $this->getWorkspaceCount($company) < $plan->workspace_limit;
    }

    /**
     * Check if the company has reached the workspace limit
     */
    public function hasReachedWorkspaceLimit(Company $company): bool
    {
        return !$this->canCreateWorkspace($company);
    }

    /**
     * Get remaining workspace slots
     */
    public function getRemainingWorkspaces(Company $company): int|string
    {
        $plan = $company->plan;

        if (!$plan || $plan->workspace_limit === 0) {
            return $plan && $plan->workspace_limit === 0 ? 'unlimited' : max(0, 1 - $this->getWorkspaceCount($company));
        }

        return max(0, $plan->workspace_limit - $this->getWorkspaceCount($company));
    }

    /**
     * Check if the company can add a new team member
     */
    public function canAddTeamMember(Company $company): bool
    {
        $plan = $company->plan;

        // No plan = use defaults (limit of 5)
        if (!$plan) {
            return $this->getTeamMemberCount($company) < 5;
        }

        // 0 means unlimited
        if ($plan->team_member_limit === 0) {
            return true;
        }

        return $this->getTeamMemberCount($company) < $plan->team_member_limit;
    }

    /**
     * Check if the company has reached the team member limit
     */
    public function hasReachedTeamMemberLimit(Company $company): bool
    {
        return !$this->canAddTeamMember($company);
    }

    /**
     * Get remaining team member slots
     */
    public function getRemainingTeamMembers(Company $company): int|string
    {
        $plan = $company->plan;

        if (!$plan || $plan->team_member_limit === 0) {
            return $plan && $plan->team_member_limit === 0 ? 'unlimited' : max(0, 5 - $this->getTeamMemberCount($company));
        }

        return max(0, $plan->team_member_limit - $this->getTeamMemberCount($company));
    }

    /**
     * Check if the company can upload more files (storage limit)
     */
    public function canUploadStorage(Company $company, float $fileSizeGb = 0): bool
    {
        $plan = $company->plan;

        // No plan = use defaults (limit of 5GB)
        if (!$plan) {
            return ($this->getStorageUsageGb($company) + $fileSizeGb) < 5;
        }

        // 0 means unlimited
        if ($plan->storage_limit_gb === 0) {
            return true;
        }

        return ($this->getStorageUsageGb($company) + $fileSizeGb) < $plan->storage_limit_gb;
    }

    /**
     * Check if the company has reached the storage limit
     */
    public function hasReachedStorageLimit(Company $company): bool
    {
        return !$this->canUploadStorage($company);
    }

    /**
     * Get remaining storage in GB
     */
    public function getRemainingStorageGb(Company $company): float|string
    {
        $plan = $company->plan;

        if (!$plan || $plan->storage_limit_gb === 0) {
            return $plan && $plan->storage_limit_gb === 0 ? 'unlimited' : max(0, 5 - $this->getStorageUsageGb($company));
        }

        return max(0, $plan->storage_limit_gb - $this->getStorageUsageGb($company));
    }

    /**
     * Get the workspace count for the company
     */
    public function getWorkspaceCount(Company $company): int
    {
        return Workspace::whereIn('owner_id', $company->users->pluck('id'))->count();
    }

    /**
     * Get the team member count for the company
     */
    public function getTeamMemberCount(Company $company): int
    {
        return $company->users()->count();
    }

    /**
     * Get the storage usage in GB for the company
     */
    public function getStorageUsageGb(Company $company): float
    {
        // For now, return 0
        return 0.0;
    }

    /**
     * Get a summary of plan usage for display
     */
    public function getUsageSummary(Company $company): array
    {
        $usage = $this->getUsage($company);
        $limits = $this->getLimits($company);

        return [
            'workspaces' => [
                'used' => $usage['workspaces'],
                'limit' => $limits['workspaces'],
                'unlimited' => $limits['workspaces'] === 0,
                'reached' => $this->hasReachedWorkspaceLimit($company),
                'percentage' => $limits['workspaces'] > 0 ? min(100, round(($usage['workspaces'] / $limits['workspaces']) * 100)) : 0,
            ],
            'team_members' => [
                'used' => $usage['team_members'],
                'limit' => $limits['team_members'],
                'unlimited' => $limits['team_members'] === 0,
                'reached' => $this->hasReachedTeamMemberLimit($company),
                'percentage' => $limits['team_members'] > 0 ? min(100, round(($usage['team_members'] / $limits['team_members']) * 100)) : 0,
            ],
            'storage' => [
                'used' => $usage['storage_gb'],
                'limit' => $limits['storage_gb'],
                'unlimited' => $limits['storage_gb'] === 0,
                'reached' => $this->hasReachedStorageLimit($company),
                'percentage' => $limits['storage_gb'] > 0 ? min(100, round(($usage['storage_gb'] / $limits['storage_gb']) * 100)) : 0,
            ],
        ];
    }
}
