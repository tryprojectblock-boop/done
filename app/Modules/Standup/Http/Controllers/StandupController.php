<?php

declare(strict_types=1);

namespace App\Modules\Standup\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Standup\Enums\MoodType;
use App\Modules\Standup\Models\MemberTracker;
use App\Modules\Standup\Models\StandupEntry;
use App\Modules\Standup\Models\StandupTemplate;
use App\Modules\Standup\Services\StandupService;
use App\Modules\Workspace\Enums\WorkspaceRole;
use App\Modules\Workspace\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StandupController extends Controller
{
    public function __construct(
        private readonly StandupService $standupService
    ) {}

    /**
     * Display standup index with listing and filters.
     */
    public function index(Request $request, Workspace $workspace): View
    {
        $this->authorizeAccess($workspace);

        // Check if user is admin/owner
        $userRole = $workspace->getMemberRole(auth()->user());
        $isAdminOrOwner = in_array($userRole, [WorkspaceRole::OWNER, WorkspaceRole::ADMIN]);

        // Get filter parameters
        $filterDate = $request->get('date');
        $filterMember = $request->get('member');
        $filterMine = $request->boolean('mine');

        // Build query
        $query = StandupEntry::query()
            ->forWorkspace($workspace->id)
            ->with('user')
            ->orderBy('standup_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply date filter
        if ($filterDate) {
            $query->forDate(Carbon::parse($filterDate));
        }

        // Apply member filter (only for admin/owner)
        if ($filterMember && $isAdminOrOwner) {
            $query->where('user_id', $filterMember);
        }

        // Apply "my standups" filter or restrict to own standups for non-admin
        if ($filterMine || !$isAdminOrOwner) {
            $query->where('user_id', auth()->id());
        }

        // Paginate results
        $entries = $query->paginate(15)->withQueryString();

        // Get today's entry for current user
        $todayEntry = StandupEntry::query()
            ->forWorkspace($workspace->id)
            ->where('user_id', auth()->id())
            ->forDate(today())
            ->first();

        // Check if current user has submitted today
        $hasSubmittedToday = $todayEntry !== null;

        // Get workspace members for filter dropdown (only for admin/owner)
        $members = $isAdminOrOwner ? $workspace->members()->get() : collect();

        // Get template
        $template = $workspace->standupTemplate;

        return view('standup::index', [
            'workspace' => $workspace,
            'entries' => $entries,
            'hasSubmittedToday' => $hasSubmittedToday,
            'todayEntry' => $todayEntry,
            'template' => $template,
            'isAdminOrOwner' => $isAdminOrOwner,
            'members' => $members,
            'filterDate' => $filterDate,
            'filterMember' => $filterMember,
            'filterMine' => $filterMine,
            'tab' => 'standup',
        ]);
    }

    /**
     * Show standups for a specific date.
     */
    public function show(Request $request, Workspace $workspace, string $date): View
    {
        $this->authorizeAccess($workspace);

        $selectedDate = Carbon::parse($date);

        $entries = StandupEntry::query()
            ->forWorkspace($workspace->id)
            ->forDate($selectedDate)
            ->with('user')
            ->get();

        $blockers = $entries->filter(fn ($entry) => $entry->has_blockers);
        $moods = $entries->pluck('mood')->filter();
        $averageMood = MoodType::averageScore($moods->toArray());

        return view('standup::show', [
            'workspace' => $workspace,
            'entries' => $entries,
            'blockers' => $blockers,
            'selectedDate' => $selectedDate,
            'averageMood' => $averageMood,
            'tab' => 'standup',
        ]);
    }

    /**
     * Show the standup submission form.
     */
    public function create(Request $request, Workspace $workspace): View|RedirectResponse
    {
        $this->authorizeAccess($workspace);

        // Check if already submitted today
        if (StandupEntry::hasSubmittedForDate($workspace->id, auth()->id(), today())) {
            return redirect()
                ->route('standups.index', $workspace)
                ->with('info', 'You have already submitted your standup for today.');
        }

        // Get or create template
        $template = $workspace->standupTemplate;
        if (!$template) {
            $template = StandupTemplate::createDefault($workspace, auth()->user());
        }

        // Get current tracker status
        $tracker = MemberTracker::getOrCreateForMember($workspace, auth()->user());

        return view('standup::create', [
            'workspace' => $workspace,
            'template' => $template,
            'moodOptions' => MoodType::options(),
            'tracker' => $tracker,
            'tab' => 'standup',
        ]);
    }

    /**
     * Store a new standup entry.
     */
    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeAccess($workspace);

        // Check if already submitted today
        if (StandupEntry::hasSubmittedForDate($workspace->id, auth()->id(), today())) {
            return redirect()
                ->route('standups.index', $workspace)
                ->with('error', 'You have already submitted your standup for today.');
        }

        $template = $workspace->standupTemplate;
        if (!$template) {
            $template = StandupTemplate::createDefault($workspace, auth()->user());
        }

        $validated = $request->validate([
            'responses' => 'required|array',
            'responses.*' => 'nullable|string|max:5000',
            'mood' => 'nullable|string|in:great,good,okay,concerned,struggling',
            'is_on_track' => 'required|in:0,1',
            'off_track_reason' => 'nullable|string|max:500',
        ]);

        // Build responses array
        $responses = [];
        $hasBlockers = false;

        foreach ($template->getOrderedQuestions() as $question) {
            $answer = $validated['responses'][$question['id']] ?? '';

            $responses[] = [
                'question_id' => $question['id'],
                'question' => $question['question'],
                'type' => $question['type'],
                'answer' => $answer,
            ];

            // Check if blockers question has content
            if ($question['type'] === 'blockers' && !empty(trim($answer))) {
                $hasBlockers = true;
            }
        }

        StandupEntry::create([
            'workspace_id' => $workspace->id,
            'user_id' => auth()->id(),
            'template_id' => $template->id,
            'standup_date' => today(),
            'responses' => $responses,
            'mood' => $validated['mood'] ?? null,
            'has_blockers' => $hasBlockers,
        ]);

        // Update member tracker status
        $isOnTrack = $validated['is_on_track'] === '1';
        $tracker = MemberTracker::getOrCreateForMember($workspace, auth()->user());

        if ($isOnTrack) {
            $tracker->markOnTrack(auth()->user());
        } else {
            $tracker->markOffTrack(
                $validated['off_track_reason'] ?? '',
                auth()->user()
            );
        }

        return redirect()
            ->route('standups.index', $workspace)
            ->with('success', 'Standup submitted successfully!');
    }

    /**
     * Show the edit form for a standup entry.
     */
    public function edit(Request $request, Workspace $workspace, StandupEntry $entry): View|RedirectResponse
    {
        $this->authorizeAccess($workspace);

        // Only allow editing own entries
        if ($entry->user_id !== auth()->id()) {
            abort(403, 'You can only edit your own standup entries.');
        }

        // Only allow editing entries from today
        if (!$entry->standup_date->isToday()) {
            return redirect()
                ->route('standups.index', $workspace)
                ->with('error', 'You can only edit today\'s standup.');
        }

        $template = $workspace->standupTemplate;

        // Get current tracker status
        $tracker = MemberTracker::getOrCreateForMember($workspace, auth()->user());

        return view('standup::edit', [
            'workspace' => $workspace,
            'entry' => $entry,
            'template' => $template,
            'moodOptions' => MoodType::options(),
            'tracker' => $tracker,
            'tab' => 'standup',
        ]);
    }

    /**
     * Update a standup entry.
     */
    public function update(Request $request, Workspace $workspace, StandupEntry $entry): RedirectResponse
    {
        $this->authorizeAccess($workspace);

        // Only allow editing own entries
        if ($entry->user_id !== auth()->id()) {
            abort(403, 'You can only edit your own standup entries.');
        }

        // Only allow editing entries from today
        if (!$entry->standup_date->isToday()) {
            return redirect()
                ->route('standups.index', $workspace)
                ->with('error', 'You can only edit today\'s standup.');
        }

        $template = $workspace->standupTemplate;

        $validated = $request->validate([
            'responses' => 'required|array',
            'responses.*' => 'nullable|string|max:5000',
            'mood' => 'nullable|string|in:great,good,okay,concerned,struggling',
            'is_on_track' => 'required|in:0,1',
            'off_track_reason' => 'nullable|string|max:500',
        ]);

        // Build responses array
        $responses = [];
        $hasBlockers = false;

        foreach ($template->getOrderedQuestions() as $question) {
            $answer = $validated['responses'][$question['id']] ?? '';

            $responses[] = [
                'question_id' => $question['id'],
                'question' => $question['question'],
                'type' => $question['type'],
                'answer' => $answer,
            ];

            if ($question['type'] === 'blockers' && !empty(trim($answer))) {
                $hasBlockers = true;
            }
        }

        $entry->update([
            'responses' => $responses,
            'mood' => $validated['mood'] ?? null,
            'has_blockers' => $hasBlockers,
        ]);

        // Update member tracker status
        $isOnTrack = $validated['is_on_track'] === '1';
        $tracker = MemberTracker::getOrCreateForMember($workspace, auth()->user());

        if ($isOnTrack) {
            $tracker->markOnTrack(auth()->user());
        } else {
            $tracker->markOffTrack(
                $validated['off_track_reason'] ?? '',
                auth()->user()
            );
        }

        return redirect()
            ->route('standups.index', $workspace)
            ->with('success', 'Standup updated successfully!');
    }

    /**
     * Authorize access to standup features.
     */
    private function authorizeAccess(Workspace $workspace): void
    {
        if (!$workspace->hasMember(auth()->user())) {
            abort(403, 'You must be a member of this workspace.');
        }

        if (!$workspace->isStandupEnabled()) {
            abort(403, 'Daily Standup is not enabled for this workspace.');
        }
    }
}
