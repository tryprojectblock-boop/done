<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use App\Modules\Task\Models\Task;
use App\Services\GoogleCalendarService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SyncTaskToGoogleCalendar implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        protected GoogleCalendarService $googleCalendarService
    ) {}

    /**
     * Handle the task saved event.
     */
    public function handle(Task $task): void
    {
        // Only sync tasks with due dates
        if (!$task->due_date) {
            return;
        }

        // Skip if task was synced from Google Calendar (avoid loops)
        if ($task->google_sync_source === 'google_calendar' && $task->google_synced_at) {
            // Check if it was recently synced (within last minute)
            if ($task->google_synced_at->diffInMinutes(now()) < 1) {
                return;
            }
        }

        // Get the task creator or assignee to sync with their Google Calendar
        $syncUser = $this->getSyncUser($task);
        if (!$syncUser) {
            return;
        }

        // Check if user can sync
        if (!$syncUser->canSyncGoogleCalendar()) {
            return;
        }

        try {
            $this->googleCalendarService->syncTaskToGoogle($task, $syncUser);
        } catch (\Exception $e) {
            Log::error('Failed to sync task to Google Calendar', [
                'task_id' => $task->id,
                'user_id' => $syncUser->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the user to sync the task with.
     * Prioritizes assignee, falls back to creator.
     */
    protected function getSyncUser(Task $task): ?User
    {
        // If task has an assignee with Google connected, use them
        if ($task->assignee_id) {
            $assignee = User::find($task->assignee_id);
            if ($assignee?->canSyncGoogleCalendar()) {
                return $assignee;
            }
        }

        // Fall back to creator
        if ($task->created_by) {
            $creator = User::find($task->created_by);
            if ($creator?->canSyncGoogleCalendar()) {
                return $creator;
            }
        }

        return null;
    }
}
