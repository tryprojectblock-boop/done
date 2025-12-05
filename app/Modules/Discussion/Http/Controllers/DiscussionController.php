<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Discussion\Contracts\DiscussionServiceInterface;
use App\Modules\Discussion\Enums\DiscussionType;
use App\Modules\Discussion\Http\Requests\StoreDiscussionRequest;
use App\Modules\Discussion\Http\Requests\UpdateDiscussionRequest;
use App\Modules\Discussion\Models\Discussion;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscussionController extends Controller
{
    public function __construct(
        private readonly DiscussionServiceInterface $discussionService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $filters = [
            'type' => $request->get('type'),
            'is_public' => $request->get('is_public'),
            'workspace_id' => $request->get('workspace_id'),
            'my_discussions' => $request->get('my_discussions'),
            'search' => $request->get('search'),
            'sort' => $request->get('sort', 'last_activity_at'),
            'direction' => $request->get('direction', 'desc'),
        ];

        $viewMode = $request->get('view', 'card');
        if (!in_array($viewMode, ['card', 'table'])) {
            $viewMode = 'table';
        }

        $discussions = $this->discussionService->getDiscussionsForUser($user, $filters, 20);
        $workspaces = Workspace::forUser($user)->get();
        $types = DiscussionType::options();

        return view('discussion::index', compact('discussions', 'workspaces', 'types', 'filters', 'viewMode'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $workspaces = Workspace::forUser($user)->get();
        $members = User::where('company_id', $user->company_id)
            ->where('id', '!=', $user->id)
            ->where('role', '!=', User::ROLE_GUEST)
            ->get();

        // Get guests from user's workspaces
        $workspaceIds = $workspaces->pluck('id');
        $guestUserIds = \DB::table('workspace_guests')
            ->whereIn('workspace_id', $workspaceIds)
            ->pluck('user_id')
            ->unique();
        $guests = User::whereIn('id', $guestUserIds)
            ->where('id', '!=', $user->id)
            ->get();

        $types = DiscussionType::options();

        return view('discussion::create', compact('workspaces', 'members', 'guests', 'types'));
    }

    public function store(StoreDiscussionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $data['attachments'] = $request->file('attachments');
        }

        $discussion = $this->discussionService->createDiscussion($data, $request->user());

        return redirect()
            ->route('discussions.show', $discussion->uuid)
            ->with('success', 'Discussion created successfully!');
    }

    public function show(string $uuid): View
    {
        $discussion = $this->discussionService->getDiscussionByUuid($uuid);

        if (!$discussion) {
            abort(404);
        }

        $user = auth()->user();

        if (!$discussion->canView($user)) {
            abort(403, 'You don\'t have permission to view this discussion.');
        }

        return view('discussion::show', compact('discussion', 'user'));
    }

    public function edit(string $uuid): View
    {
        $discussion = $this->discussionService->getDiscussionByUuid($uuid);

        if (!$discussion) {
            abort(404);
        }

        $user = auth()->user();

        if (!$discussion->canEdit($user)) {
            abort(403);
        }

        $workspaces = Workspace::forUser($user)->get();
        $members = User::where('company_id', $user->company_id)
            ->where('id', '!=', $discussion->created_by)
            ->where('role', '!=', User::ROLE_GUEST)
            ->get();

        // Get guests from user's workspaces
        $workspaceIds = $workspaces->pluck('id');
        $guestUserIds = \DB::table('workspace_guests')
            ->whereIn('workspace_id', $workspaceIds)
            ->pluck('user_id')
            ->unique();
        $guests = User::whereIn('id', $guestUserIds)
            ->where('id', '!=', $discussion->created_by)
            ->get();

        $types = DiscussionType::options();

        return view('discussion::edit', compact('discussion', 'workspaces', 'members', 'guests', 'types'));
    }

    public function update(UpdateDiscussionRequest $request, string $uuid): RedirectResponse
    {
        $discussion = $this->discussionService->getDiscussionByUuid($uuid);

        if (!$discussion) {
            abort(404);
        }

        $user = $request->user();

        if (!$discussion->canEdit($user)) {
            abort(403);
        }

        $data = $request->validated();

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $data['attachments'] = $request->file('attachments');
        }

        $this->discussionService->updateDiscussion($discussion, $data, $user);

        return redirect()
            ->route('discussions.show', $discussion->uuid)
            ->with('success', 'Discussion updated successfully!');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $discussion = $this->discussionService->getDiscussionByUuid($uuid);

        if (!$discussion) {
            abort(404);
        }

        $user = auth()->user();

        if (!$discussion->canDelete($user)) {
            abort(403);
        }

        $this->discussionService->deleteDiscussion($discussion, $user);

        return redirect()
            ->route('discussions.index')
            ->with('success', 'Discussion deleted successfully!');
    }
}
