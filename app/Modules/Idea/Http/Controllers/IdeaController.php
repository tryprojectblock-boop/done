<?php

declare(strict_types=1);

namespace App\Modules\Idea\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Idea\Contracts\IdeaServiceInterface;
use App\Modules\Idea\Enums\IdeaPriority;
use App\Modules\Idea\Enums\IdeaStatus;
use App\Modules\Idea\Http\Requests\StoreIdeaRequest;
use App\Modules\Idea\Http\Requests\UpdateIdeaRequest;
use App\Modules\Idea\Models\Idea;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IdeaController extends Controller
{
    public function __construct(
        private readonly IdeaServiceInterface $ideaService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $filters = [
            'status' => $request->get('status'),
            'priority' => $request->get('priority'),
            'workspace_id' => $request->get('workspace_id'),
            'search' => $request->get('search'),
            'sort' => $request->get('sort', 'created_at'),
            'direction' => $request->get('direction', 'desc'),
        ];

        $viewMode = $request->get('view', 'card');
        if (!in_array($viewMode, ['card', 'table'])) {
            $viewMode = 'table';
        }

        $ideas = $this->ideaService->getIdeasForUser($user, $filters, 20);
        $workspaces = Workspace::forUser($user)->get();
        $statuses = IdeaStatus::options();
        $priorities = IdeaPriority::options();

        return view('idea::index', compact('ideas', 'workspaces', 'statuses', 'priorities', 'filters', 'viewMode'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $workspaces = Workspace::forUser($user)->get();
        $members = User::where('company_id', $user->company_id)
            ->where('id', '!=', $user->id)
            ->where('role', '!=', User::ROLE_GUEST)
            ->get();

        // Get guests from user's workspaces (guests are linked via workspace_guests table)
        $workspaceIds = $workspaces->pluck('id');
        $guestUserIds = \DB::table('workspace_guests')
            ->whereIn('workspace_id', $workspaceIds)
            ->pluck('user_id')
            ->unique();
        $guests = User::whereIn('id', $guestUserIds)
            ->where('id', '!=', $user->id)
            ->get();

        $priorities = IdeaPriority::options();

        return view('idea::create', compact('workspaces', 'members', 'guests', 'priorities'));
    }

    public function store(StoreIdeaRequest $request): RedirectResponse
    {
        $idea = $this->ideaService->createIdea($request->validated(), $request->user());

        return redirect()
            ->route('ideas.show', $idea->uuid)
            ->with('success', 'Idea created successfully!');
    }

    public function show(string $uuid): View
    {
        $idea = $this->ideaService->getIdeaByUuid($uuid);

        if (!$idea) {
            abort(404);
        }

        $user = auth()->user();
        $statuses = IdeaStatus::options();

        return view('idea::show', compact('idea', 'user', 'statuses'));
    }

    public function edit(string $uuid): View
    {
        $idea = $this->ideaService->getIdeaByUuid($uuid);

        if (!$idea) {
            abort(404);
        }

        $user = auth()->user();

        if (!$idea->canEdit($user)) {
            abort(403);
        }

        $workspaces = Workspace::forUser($user)->get();
        $members = User::where('company_id', $user->company_id)
            ->where('id', '!=', $idea->created_by)
            ->where('role', '!=', User::ROLE_GUEST)
            ->get();

        // Get guests from user's workspaces (guests are linked via workspace_guests table)
        $workspaceIds = $workspaces->pluck('id');
        $guestUserIds = \DB::table('workspace_guests')
            ->whereIn('workspace_id', $workspaceIds)
            ->pluck('user_id')
            ->unique();
        $guests = User::whereIn('id', $guestUserIds)
            ->where('id', '!=', $idea->created_by)
            ->get();

        $priorities = IdeaPriority::options();

        return view('idea::edit', compact('idea', 'workspaces', 'members', 'guests', 'priorities'));
    }

    public function update(UpdateIdeaRequest $request, string $uuid): RedirectResponse
    {
        $idea = $this->ideaService->getIdeaByUuid($uuid);

        if (!$idea) {
            abort(404);
        }

        $user = $request->user();

        if (!$idea->canEdit($user)) {
            abort(403);
        }

        $this->ideaService->updateIdea($idea, $request->validated(), $user);

        return redirect()
            ->route('ideas.show', $idea->uuid)
            ->with('success', 'Idea updated successfully!');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $idea = $this->ideaService->getIdeaByUuid($uuid);

        if (!$idea) {
            abort(404);
        }

        $user = auth()->user();

        if (!$idea->canDelete($user)) {
            abort(403);
        }

        $this->ideaService->deleteIdea($idea, $user);

        return redirect()
            ->route('ideas.index')
            ->with('success', 'Idea deleted successfully!');
    }

    public function changeStatus(Request $request, string $uuid): RedirectResponse
    {
        $idea = $this->ideaService->getIdeaByUuid($uuid);

        if (!$idea) {
            abort(404);
        }

        $user = $request->user();

        if (!$idea->canChangeStatus($user)) {
            return back()->with('error', 'You do not have permission to change the status.');
        }

        $request->validate([
            'status' => ['required', 'in:' . implode(',', array_column(IdeaStatus::cases(), 'value'))],
        ]);

        $status = IdeaStatus::from($request->input('status'));
        $this->ideaService->changeStatus($idea, $status, $user);

        return back()->with('success', 'Status updated successfully!');
    }

    public function vote(Request $request, string $uuid): RedirectResponse
    {
        $idea = $this->ideaService->getIdeaByUuid($uuid);

        if (!$idea) {
            abort(404);
        }

        $request->validate([
            'vote' => ['required', 'integer', 'in:-1,1'],
        ]);

        $user = $request->user();
        $vote = (int) $request->input('vote');

        // If user already voted with the same vote, remove it (toggle)
        if ($idea->getUserVote($user) === $vote) {
            $this->ideaService->removeVote($idea, $user);
        } else {
            $this->ideaService->vote($idea, $user, $vote);
        }

        return back();
    }
}
