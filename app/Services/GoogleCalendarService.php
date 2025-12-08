<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Modules\Auth\Models\Company;
use App\Modules\Task\Models\Task;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    protected ?GoogleClient $client = null;
    protected ?Calendar $calendarService = null;
    protected ?Company $company = null;

    /**
     * Set the company context for this service instance.
     */
    public function setCompany(Company $company): self
    {
        $this->company = $company;
        $this->client = null; // Reset client when company changes
        return $this;
    }

    /**
     * Get company settings for Google API credentials.
     */
    protected function getCompanySettings(): array
    {
        if (!$this->company) {
            return [];
        }
        return $this->company->settings ?? [];
    }

    /**
     * Check if Google API is configured for a company.
     */
    public function isConfigured(?Company $company = null): bool
    {
        if ($company) {
            $this->setCompany($company);
        }
        $settings = $this->getCompanySettings();
        return !empty($settings['google_client_id']) && !empty($settings['google_client_secret']);
    }

    /**
     * Check if Gmail sync is enabled for a company.
     */
    public function isSyncEnabled(?Company $company = null): bool
    {
        if ($company) {
            $this->setCompany($company);
        }
        $settings = $this->getCompanySettings();
        return ($settings['gmail_sync_enabled'] ?? false) && $this->isConfigured();
    }

    /**
     * Get configured Google Client for the current company.
     */
    public function getClient(?Company $company = null): GoogleClient
    {
        if ($company) {
            $this->setCompany($company);
        }

        if ($this->client) {
            return $this->client;
        }

        $settings = $this->getCompanySettings();

        $this->client = new GoogleClient();
        $this->client->setClientId($settings['google_client_id'] ?? '');
        $this->client->setClientSecret($settings['google_client_secret'] ?? '');
        $this->client->setRedirectUri($settings['google_redirect_uri'] ?? url('/auth/google/callback'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->addScope(Calendar::CALENDAR);
        $this->client->addScope('email');
        $this->client->addScope('profile');

        return $this->client;
    }

    /**
     * Get authorization URL for OAuth flow.
     */
    public function getAuthUrl(): string
    {
        return $this->getClient()->createAuthUrl();
    }

    /**
     * Handle OAuth callback and store tokens.
     */
    public function handleCallback(string $code, User $user): bool
    {
        try {
            $client = $this->getClient();
            $token = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                Log::error('Google OAuth error', ['error' => $token['error']]);
                return false;
            }

            // Get user info
            $oauth2 = new \Google\Service\Oauth2($client);
            $googleUser = $oauth2->userinfo->get();

            // Store tokens
            $user->update([
                'google_id' => $googleUser->id,
                'google_access_token' => encrypt($token['access_token']),
                'google_refresh_token' => isset($token['refresh_token']) ? encrypt($token['refresh_token']) : $user->google_refresh_token,
                'google_token_expires_at' => now()->addSeconds($token['expires_in'] ?? 3600),
                'google_calendar_id' => 'primary', // Use primary calendar by default
                'google_connected_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Google Calendar callback error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return false;
        }
    }

    /**
     * Get authenticated client for a user.
     */
    public function getAuthenticatedClient(User $user): ?GoogleClient
    {
        if (!$user->hasGoogleConnected()) {
            return null;
        }

        try {
            $client = $this->getClient();

            $accessToken = decrypt($user->google_access_token);
            $refreshToken = decrypt($user->google_refresh_token);

            $client->setAccessToken([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => $user->google_token_expires_at ? $user->google_token_expires_at->diffInSeconds(now()) : 0,
            ]);

            // Refresh token if expired
            if ($client->isAccessTokenExpired()) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);

                if (isset($newToken['error'])) {
                    Log::error('Google token refresh error', ['error' => $newToken['error']]);
                    return null;
                }

                $user->update([
                    'google_access_token' => encrypt($newToken['access_token']),
                    'google_token_expires_at' => now()->addSeconds($newToken['expires_in'] ?? 3600),
                ]);
            }

            return $client;
        } catch (\Exception $e) {
            Log::error('Google authenticated client error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return null;
        }
    }

    /**
     * Get Calendar service for a user.
     */
    public function getCalendarService(User $user): ?Calendar
    {
        $client = $this->getAuthenticatedClient($user);
        if (!$client) {
            return null;
        }

        return new Calendar($client);
    }

    /**
     * Sync a task to Google Calendar.
     */
    public function syncTaskToGoogle(Task $task, User $user): ?string
    {
        if (!$user->canSyncGoogleCalendar()) {
            return null;
        }

        $calendarService = $this->getCalendarService($user);
        if (!$calendarService) {
            return null;
        }

        try {
            $calendarId = $user->google_calendar_id ?? 'primary';

            // If task already has a Google event, update it
            if ($task->google_event_id) {
                return $this->updateGoogleEvent($calendarService, $calendarId, $task);
            }

            // Create new event
            return $this->createGoogleEvent($calendarService, $calendarId, $task);
        } catch (\Exception $e) {
            Log::error('Google Calendar sync error', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
                'user_id' => $user->id,
            ]);
            return null;
        }
    }

    /**
     * Create a new Google Calendar event from a task.
     */
    protected function createGoogleEvent(Calendar $service, string $calendarId, Task $task): ?string
    {
        $event = $this->buildEventFromTask($task);

        $createdEvent = $service->events->insert($calendarId, $event);

        // Update task with Google event ID
        $task->update([
            'google_event_id' => $createdEvent->getId(),
            'google_synced_at' => now(),
            'google_sync_source' => 'project_block',
        ]);

        return $createdEvent->getId();
    }

    /**
     * Update an existing Google Calendar event.
     */
    protected function updateGoogleEvent(Calendar $service, string $calendarId, Task $task): ?string
    {
        try {
            $event = $service->events->get($calendarId, $task->google_event_id);
            $updatedEvent = $this->buildEventFromTask($task, $event);

            $service->events->update($calendarId, $task->google_event_id, $updatedEvent);

            $task->update([
                'google_synced_at' => now(),
            ]);

            return $task->google_event_id;
        } catch (\Google\Service\Exception $e) {
            // If event not found, create a new one
            if ($e->getCode() === 404) {
                $task->update(['google_event_id' => null]);
                return $this->createGoogleEvent($service, $calendarId, $task);
            }
            throw $e;
        }
    }

    /**
     * Build a Google Calendar Event from a Task.
     */
    protected function buildEventFromTask(Task $task, ?Event $existingEvent = null): Event
    {
        $event = $existingEvent ?? new Event();

        $event->setSummary($task->title);
        $event->setDescription($this->buildEventDescription($task));

        // Set date/time
        if ($task->due_date) {
            $startDateTime = new EventDateTime();
            $endDateTime = new EventDateTime();

            // All-day event (just date, no time)
            $startDateTime->setDate($task->due_date->format('Y-m-d'));
            $endDateTime->setDate($task->due_date->format('Y-m-d'));

            $event->setStart($startDateTime);
            $event->setEnd($endDateTime);
        }

        // Add extended properties to identify this event as synced from Project Block
        $event->setExtendedProperties(new \Google\Service\Calendar\EventExtendedProperties([
            'private' => [
                'projectblock_task_id' => $task->uuid,
                'projectblock_workspace_id' => $task->workspace?->uuid ?? '',
            ],
        ]));

        return $event;
    }

    /**
     * Build event description from task.
     */
    protected function buildEventDescription(Task $task): string
    {
        $parts = [];

        if ($task->description) {
            // Strip HTML tags for plain text description
            $parts[] = strip_tags($task->description);
        }

        $parts[] = "\n---";
        $parts[] = "Workspace: " . ($task->workspace?->name ?? 'Unknown');
        $parts[] = "Priority: " . ucfirst($task->priority?->value ?? 'medium');
        $parts[] = "Synced from Project Block";

        return implode("\n", $parts);
    }

    /**
     * Delete a Google Calendar event.
     */
    public function deleteGoogleEvent(Task $task, User $user): bool
    {
        if (!$task->google_event_id || !$user->canSyncGoogleCalendar()) {
            return false;
        }

        $calendarService = $this->getCalendarService($user);
        if (!$calendarService) {
            return false;
        }

        try {
            $calendarId = $user->google_calendar_id ?? 'primary';
            $calendarService->events->delete($calendarId, $task->google_event_id);

            $task->update([
                'google_event_id' => null,
                'google_synced_at' => null,
                'google_sync_source' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Google Calendar delete error', [
                'error' => $e->getMessage(),
                'task_id' => $task->id,
            ]);
            return false;
        }
    }

    /**
     * Fetch events from Google Calendar.
     */
    public function fetchGoogleEvents(User $user, ?string $syncToken = null): array
    {
        if (!$user->canSyncGoogleCalendar()) {
            return ['events' => [], 'nextSyncToken' => null];
        }

        $calendarService = $this->getCalendarService($user);
        if (!$calendarService) {
            return ['events' => [], 'nextSyncToken' => null];
        }

        try {
            $calendarId = $user->google_calendar_id ?? 'primary';
            $params = [
                'maxResults' => 100,
                'singleEvents' => true,
                'orderBy' => 'startTime',
            ];

            // If we have a sync token, use incremental sync
            if ($syncToken) {
                $params['syncToken'] = $syncToken;
            } else {
                // Initial sync - get events from last 30 days to next 90 days
                $params['timeMin'] = now()->subDays(30)->toRfc3339String();
                $params['timeMax'] = now()->addDays(90)->toRfc3339String();
            }

            $events = $calendarService->events->listEvents($calendarId, $params);

            return [
                'events' => $events->getItems(),
                'nextSyncToken' => $events->getNextSyncToken(),
            ];
        } catch (\Google\Service\Exception $e) {
            // If sync token is invalid, do a full sync
            if ($e->getCode() === 410 && $syncToken) {
                return $this->fetchGoogleEvents($user, null);
            }
            throw $e;
        }
    }

    /**
     * Create a task from a Google Calendar event.
     */
    public function createTaskFromEvent(Event $event, User $user, int $workspaceId): ?Task
    {
        // Check if task already exists for this event
        $existingTask = Task::where('google_event_id', $event->getId())->first();
        if ($existingTask) {
            return $this->updateTaskFromEvent($existingTask, $event);
        }

        // Extract date from event
        $startDate = null;
        if ($event->getStart()) {
            if ($event->getStart()->getDate()) {
                // All-day event
                $startDate = $event->getStart()->getDate();
            } elseif ($event->getStart()->getDateTime()) {
                // Timed event
                $startDate = date('Y-m-d', strtotime($event->getStart()->getDateTime()));
            }
        }

        // Create task
        $task = Task::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'workspace_id' => $workspaceId,
            'company_id' => $user->company_id,
            'title' => $event->getSummary() ?? 'Untitled Event',
            'description' => $event->getDescription(),
            'due_date' => $startDate,
            'created_by' => $user->id,
            'google_event_id' => $event->getId(),
            'google_synced_at' => now(),
            'google_sync_source' => 'google_calendar',
        ]);

        return $task;
    }

    /**
     * Update a task from a Google Calendar event.
     */
    public function updateTaskFromEvent(Task $task, Event $event): Task
    {
        // Extract date from event
        $startDate = null;
        if ($event->getStart()) {
            if ($event->getStart()->getDate()) {
                $startDate = $event->getStart()->getDate();
            } elseif ($event->getStart()->getDateTime()) {
                $startDate = date('Y-m-d', strtotime($event->getStart()->getDateTime()));
            }
        }

        $task->update([
            'title' => $event->getSummary() ?? $task->title,
            'description' => $event->getDescription() ?? $task->description,
            'due_date' => $startDate ?? $task->due_date,
            'google_synced_at' => now(),
        ]);

        return $task;
    }

    /**
     * Perform full two-way sync for a user.
     */
    public function performFullSync(User $user, int $defaultWorkspaceId): array
    {
        $results = [
            'synced_to_google' => 0,
            'synced_from_google' => 0,
            'errors' => [],
        ];

        if (!$user->canSyncGoogleCalendar()) {
            $results['errors'][] = 'User cannot sync Google Calendar';
            return $results;
        }

        // 1. Sync tasks to Google (tasks with due dates that haven't been synced)
        $tasksToSync = Task::where('company_id', $user->company_id)
            ->whereNotNull('due_date')
            ->where(function ($query) {
                $query->whereNull('google_event_id')
                    ->orWhere('google_synced_at', '<', now()->subMinutes(5));
            })
            ->where('closed_at', null)
            ->limit(50)
            ->get();

        foreach ($tasksToSync as $task) {
            try {
                if ($this->syncTaskToGoogle($task, $user)) {
                    $results['synced_to_google']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = "Task {$task->id}: " . $e->getMessage();
            }
        }

        // 2. Fetch events from Google and create/update tasks
        try {
            $googleData = $this->fetchGoogleEvents($user);
            foreach ($googleData['events'] as $event) {
                // Skip events that were created from Project Block
                $extendedProps = $event->getExtendedProperties();
                if ($extendedProps && $extendedProps->getPrivate()) {
                    $private = $extendedProps->getPrivate();
                    if (isset($private['projectblock_task_id'])) {
                        continue; // Skip - this event was synced from Project Block
                    }
                }

                // Skip cancelled events
                if ($event->getStatus() === 'cancelled') {
                    continue;
                }

                try {
                    $this->createTaskFromEvent($event, $user, $defaultWorkspaceId);
                    $results['synced_from_google']++;
                } catch (\Exception $e) {
                    $results['errors'][] = "Event {$event->getId()}: " . $e->getMessage();
                }
            }
        } catch (\Exception $e) {
            $results['errors'][] = "Fetch events: " . $e->getMessage();
        }

        return $results;
    }
}
