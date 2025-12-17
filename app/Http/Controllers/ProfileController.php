<?php

namespace App\Http\Controllers;

use App\Models\UserOutOfOffice;
use App\Modules\Discussion\Models\DiscussionComment;
use App\Modules\Idea\Models\IdeaComment;
use App\Modules\Task\Models\TaskActivity;
use App\Modules\Task\Models\TaskComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Common timezones for dropdown.
     */
    protected array $timezones = [
        'UTC' => 'UTC (Coordinated Universal Time)',
        'America/New_York' => 'Eastern Time (US & Canada)',
        'America/Chicago' => 'Central Time (US & Canada)',
        'America/Denver' => 'Mountain Time (US & Canada)',
        'America/Los_Angeles' => 'Pacific Time (US & Canada)',
        'America/Anchorage' => 'Alaska',
        'Pacific/Honolulu' => 'Hawaii',
        'America/Phoenix' => 'Arizona',
        'America/Toronto' => 'Toronto',
        'America/Vancouver' => 'Vancouver',
        'America/Mexico_City' => 'Mexico City',
        'America/Sao_Paulo' => 'Sao Paulo',
        'America/Buenos_Aires' => 'Buenos Aires',
        'Europe/London' => 'London',
        'Europe/Dublin' => 'Dublin',
        'Europe/Paris' => 'Paris',
        'Europe/Berlin' => 'Berlin',
        'Europe/Amsterdam' => 'Amsterdam',
        'Europe/Brussels' => 'Brussels',
        'Europe/Rome' => 'Rome',
        'Europe/Madrid' => 'Madrid',
        'Europe/Zurich' => 'Zurich',
        'Europe/Vienna' => 'Vienna',
        'Europe/Stockholm' => 'Stockholm',
        'Europe/Oslo' => 'Oslo',
        'Europe/Copenhagen' => 'Copenhagen',
        'Europe/Helsinki' => 'Helsinki',
        'Europe/Warsaw' => 'Warsaw',
        'Europe/Prague' => 'Prague',
        'Europe/Athens' => 'Athens',
        'Europe/Moscow' => 'Moscow',
        'Europe/Istanbul' => 'Istanbul',
        'Asia/Dubai' => 'Dubai',
        'Asia/Karachi' => 'Karachi',
        'Asia/Kolkata' => 'Mumbai, Kolkata, New Delhi',
        'Asia/Dhaka' => 'Dhaka',
        'Asia/Bangkok' => 'Bangkok',
        'Asia/Singapore' => 'Singapore',
        'Asia/Hong_Kong' => 'Hong Kong',
        'Asia/Shanghai' => 'Beijing, Shanghai',
        'Asia/Tokyo' => 'Tokyo',
        'Asia/Seoul' => 'Seoul',
        'Australia/Perth' => 'Perth',
        'Australia/Adelaide' => 'Adelaide',
        'Australia/Sydney' => 'Sydney, Melbourne',
        'Australia/Brisbane' => 'Brisbane',
        'Pacific/Auckland' => 'Auckland',
        'Pacific/Fiji' => 'Fiji',
        'Africa/Cairo' => 'Cairo',
        'Africa/Johannesburg' => 'Johannesburg',
        'Africa/Lagos' => 'Lagos',
        'Africa/Nairobi' => 'Nairobi',
    ];

    /**
     * Display the user's profile form.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $company = $user->company;

        // Check if Out of Office feature is enabled for the company
        $settings = $company->settings ?? [];
        $oooEnabled = $settings['out_of_office_enabled'] ?? false;

        // Get current Out of Office settings for the user
        $outOfOffice = $user->outOfOffice;

        return view('profile.index', [
            'user' => $user,
            'timezones' => $this->timezones,
            'oooEnabled' => $oooEnabled,
            'outOfOffice' => $outOfOffice,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'timezone' => ['required', 'string', 'timezone'],
            'avatar' => ['nullable', 'image', 'max:2048'], // Max 2MB
        ]);

        $user = $request->user();

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar_path) {
                Storage::disk('do_spaces')->delete($user->avatar_path);
            }

            // Store new avatar to DigitalOcean Spaces
            $file = $request->file('avatar');
            $filename = 'avatars/' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            Storage::disk('do_spaces')->putFileAs(
                dirname($filename),
                $file,
                basename($filename),
                'public'
            );
            $validated['avatar_path'] = $filename;
        }

        // Update name field (combination of first and last name)
        $validated['name'] = trim($validated['first_name'] . ' ' . $validated['last_name']);

        $user->update($validated);

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully.');
    }

    /**
     * Delete the user's avatar.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('do_spaces')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return redirect()->route('profile.index')->with('success', 'Profile photo removed.');
    }

    /**
     * Display the user's activity log.
     */
    public function activity(Request $request): View
    {
        $user = $request->user();
        $filter = $request->get('filter', 'all');

        // Build a unified activity feed
        $activities = collect();

        // Task Activities (created, updated, status changes, etc.)
        $taskActivities = TaskActivity::with(['task.workspace', 'user'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(100)
            ->get()
            ->map(function ($activity) {
                return [
                    'type' => 'task_activity',
                    'subtype' => $activity->type->value,
                    'icon' => $activity->type->icon(),
                    'description' => $activity->getFormattedDescription(),
                    'task' => $activity->task,
                    'created_at' => $activity->created_at,
                    'color' => 'primary',
                ];
            });

        // Task Comments
        $taskComments = TaskComment::with(['task.workspace', 'user'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($comment) {
                return [
                    'type' => 'comment',
                    'subtype' => 'task_comment',
                    'icon' => 'tabler--message',
                    'description' => 'Commented on task: ' . ($comment->task->title ?? 'Unknown'),
                    'task' => $comment->task,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at,
                    'color' => 'info',
                ];
            });

        // Idea Comments
        $ideaComments = IdeaComment::with(['idea', 'user'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($comment) {
                return [
                    'type' => 'comment',
                    'subtype' => 'idea_comment',
                    'icon' => 'tabler--message',
                    'description' => 'Commented on idea: ' . ($comment->idea->title ?? 'Unknown'),
                    'idea' => $comment->idea,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at,
                    'color' => 'warning',
                ];
            });

        // Discussion Comments
        $discussionComments = DiscussionComment::with(['discussion', 'user'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(50)
            ->get()
            ->map(function ($comment) {
                return [
                    'type' => 'comment',
                    'subtype' => 'discussion_comment',
                    'icon' => 'tabler--message',
                    'description' => 'Commented on discussion: ' . ($comment->discussion->title ?? 'Unknown'),
                    'discussion' => $comment->discussion,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at,
                    'color' => 'success',
                ];
            });

        // Merge and filter based on type
        if ($filter === 'tasks') {
            $activities = $taskActivities;
        } elseif ($filter === 'comments') {
            $activities = $taskComments->merge($ideaComments)->merge($discussionComments);
        } else {
            $activities = $taskActivities
                ->merge($taskComments)
                ->merge($ideaComments)
                ->merge($discussionComments);
        }

        // Sort by date and paginate manually
        $activities = $activities->sortByDesc('created_at')->values();

        // Group by date
        $groupedActivities = $activities->groupBy(function ($activity) {
            return $activity['created_at']->format('Y-m-d');
        });

        return view('profile.activity', [
            'user' => $user,
            'groupedActivities' => $groupedActivities,
            'filter' => $filter,
        ]);
    }

    /**
     * Update the user's Out of Office settings.
     */
    public function updateOutOfOffice(Request $request): RedirectResponse
    {
        $user = $request->user();
        $company = $user->company;

        // Check if Out of Office feature is enabled for the company
        $settings = $company->settings ?? [];
        if (!($settings['out_of_office_enabled'] ?? false)) {
            return redirect()->route('profile.index')
                ->with('error', 'Out of Office feature is not enabled for your organization.');
        }

        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'message' => ['nullable', 'string', 'max:500'],
            'auto_respond_message' => ['nullable', 'string', 'max:1000'],
        ]);

        // Deactivate any existing active OOO settings
        UserOutOfOffice::where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Create new Out of Office entry
        UserOutOfOffice::create([
            'user_id' => $user->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'message' => $validated['message'] ?? null,
            'auto_respond_message' => $validated['auto_respond_message'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('profile.index')
            ->with('success', 'Out of Office settings saved successfully.');
    }

    /**
     * Delete the user's Out of Office settings.
     */
    public function deleteOutOfOffice(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Deactivate all active OOO settings for the user
        UserOutOfOffice::where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return redirect()->route('profile.index')
            ->with('success', 'Out of Office has been disabled.');
    }

    /**
     * Update the user's signature.
     */
    public function updateSignature(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'signature' => ['nullable', 'string', 'max:5000'],
            'include_signature_in_inbox' => ['boolean'],
        ]);

        $user = $request->user();
        $user->update([
            'signature' => $validated['signature'] ?? null,
            'include_signature_in_inbox' => $request->boolean('include_signature_in_inbox'),
        ]);

        return redirect()->route('profile.index')
            ->with('success', 'Signature updated successfully.');
    }
}
