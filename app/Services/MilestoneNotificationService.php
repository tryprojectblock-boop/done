<?php

namespace App\Services;

use App\Models\Milestone;
use App\Models\Notification;
use App\Models\User;

class MilestoneNotificationService
{
    /**
     * Notify when a milestone is assigned to a user.
     */
    public function notifyMilestoneAssigned(Milestone $milestone, User $owner, User $assignedBy): void
    {
        // Don't notify if user assigned to themselves
        if ($owner->id === $assignedBy->id) {
            return;
        }

        Notification::create([
            'user_id' => $owner->id,
            'type' => Notification::TYPE_MILESTONE_ASSIGNED,
            'title' => 'Milestone Assigned',
            'message' => "{$assignedBy->full_name} assigned you to milestone \"{$milestone->title}\"",
            'notifiable_type' => Milestone::class,
            'notifiable_id' => $milestone->id,
            'data' => [
                'milestone_id' => $milestone->id,
                'milestone_uuid' => $milestone->uuid,
                'milestone_title' => $milestone->title,
                'workspace_uuid' => $milestone->workspace->uuid,
                'milestone_url' => route('milestones.show', [$milestone->workspace->uuid, $milestone->uuid]),
                'assigned_by' => $assignedBy->full_name,
            ],
        ]);
    }

    /**
     * Notify when a milestone is approaching its due date.
     */
    public function notifyMilestoneDueSoon(Milestone $milestone, int $daysRemaining): void
    {
        // Notify owner if exists
        if ($milestone->owner) {
            $this->createDueSoonNotification($milestone, $milestone->owner, $daysRemaining);
        }

        // Also notify creator if different from owner
        if ($milestone->creator && $milestone->creator->id !== $milestone->owner_id) {
            $this->createDueSoonNotification($milestone, $milestone->creator, $daysRemaining);
        }
    }

    protected function createDueSoonNotification(Milestone $milestone, User $user, int $daysRemaining): void
    {
        $timeText = $daysRemaining === 0 ? 'today' : ($daysRemaining === 1 ? 'tomorrow' : "in {$daysRemaining} days");

        Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_MILESTONE_DUE_SOON,
            'title' => 'Milestone Due Soon',
            'message' => "Milestone \"{$milestone->title}\" is due {$timeText}",
            'notifiable_type' => Milestone::class,
            'notifiable_id' => $milestone->id,
            'data' => [
                'milestone_id' => $milestone->id,
                'milestone_uuid' => $milestone->uuid,
                'milestone_title' => $milestone->title,
                'workspace_uuid' => $milestone->workspace->uuid,
                'milestone_url' => route('milestones.show', [$milestone->workspace->uuid, $milestone->uuid]),
                'days_remaining' => $daysRemaining,
                'due_date' => $milestone->due_date->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Notify when a milestone is completed.
     */
    public function notifyMilestoneCompleted(Milestone $milestone, User $completedBy): void
    {
        $usersToNotify = collect();

        // Notify owner if exists and not the one who completed
        if ($milestone->owner && $milestone->owner->id !== $completedBy->id) {
            $usersToNotify->push($milestone->owner);
        }

        // Notify creator if different from owner and not the one who completed
        if ($milestone->creator && $milestone->creator->id !== $completedBy->id && $milestone->creator->id !== $milestone->owner_id) {
            $usersToNotify->push($milestone->creator);
        }

        foreach ($usersToNotify as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => Notification::TYPE_MILESTONE_COMPLETED,
                'title' => 'Milestone Completed',
                'message' => "{$completedBy->full_name} completed milestone \"{$milestone->title}\"",
                'notifiable_type' => Milestone::class,
                'notifiable_id' => $milestone->id,
                'data' => [
                    'milestone_id' => $milestone->id,
                    'milestone_uuid' => $milestone->uuid,
                    'milestone_title' => $milestone->title,
                    'workspace_uuid' => $milestone->workspace->uuid,
                    'milestone_url' => route('milestones.show', [$milestone->workspace->uuid, $milestone->uuid]),
                    'completed_by' => $completedBy->full_name,
                ],
            ]);
        }
    }

    /**
     * Notify when someone comments on a milestone.
     */
    public function notifyMilestoneComment(Milestone $milestone, User $commenter): void
    {
        $usersToNotify = collect();

        // Notify owner if exists and not the commenter
        if ($milestone->owner && $milestone->owner->id !== $commenter->id) {
            $usersToNotify->push($milestone->owner);
        }

        // Notify creator if different from owner and not the commenter
        if ($milestone->creator && $milestone->creator->id !== $commenter->id && $milestone->creator->id !== $milestone->owner_id) {
            $usersToNotify->push($milestone->creator);
        }

        foreach ($usersToNotify as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => Notification::TYPE_MILESTONE_COMMENT,
                'title' => 'New Comment on Milestone',
                'message' => "{$commenter->full_name} commented on milestone \"{$milestone->title}\"",
                'notifiable_type' => Milestone::class,
                'notifiable_id' => $milestone->id,
                'data' => [
                    'milestone_id' => $milestone->id,
                    'milestone_uuid' => $milestone->uuid,
                    'milestone_title' => $milestone->title,
                    'workspace_uuid' => $milestone->workspace->uuid,
                    'milestone_url' => route('milestones.show', [$milestone->workspace->uuid, $milestone->uuid]),
                    'commenter' => $commenter->full_name,
                ],
            ]);
        }
    }
}
