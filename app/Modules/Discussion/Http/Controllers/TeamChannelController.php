<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Discussion\Models\TeamChannel;
use App\Modules\Discussion\Models\TeamChannelThread;
use App\Modules\Discussion\Models\TeamChannelReply;
use App\Modules\Discussion\Models\TeamChannelJoinRequest;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeamChannelController extends Controller
{
    /**
     * Display a listing of team channels.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $companyId = $user->company_id;

        // Show all company channels (private ones will be displayed but not clickable for non-members)
        $channels = TeamChannel::visibleTo($user)
            ->with(['creator', 'members'])
            ->withCount('threads')
            ->orderBy('last_activity_at', 'desc')
            ->get();

        return view('discussion::channels.index', [
            'channels' => $channels,
            'user' => $user,
        ]);
    }

    /**
     * Show the form for creating a new channel.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        // Only admin/owner can create channels
        if (!$user->isAdminOrHigher()) {
            abort(403, 'Only administrators and owners can create team channels.');
        }

        // Get company members for invitation
        $members = User::where('company_id', $user->company_id)
            ->where('id', '!=', $user->id)
            ->where('role', '!=', User::ROLE_GUEST)
            ->orderBy('first_name')
            ->get();

        // Get all channels for sidebar (visible to user, including private)
        $allChannels = TeamChannel::visibleTo($user)
            ->orderBy('name')
            ->get();

        return view('discussion::channels.create', [
            'user' => $user,
            'members' => $members,
            'allChannels' => $allChannels,
        ]);
    }

    /**
     * Store a newly created channel.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Only admin/owner can create channels
        if (!$user->isAdminOrHigher()) {
            abort(403, 'Only administrators and owners can create team channels.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'tag' => ['required', 'string', 'max:50'],
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|in:primary,secondary,accent,info,success,warning,error,orange,teal,indigo,gray',
            'is_private' => 'nullable|boolean',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        // Ensure tag is lowercase and has #
        $tag = strtolower(trim($validated['tag']));
        if (!str_starts_with($tag, '#')) {
            $tag = '#' . $tag;
        }

        // Check if tag already exists for this company
        $existingChannel = TeamChannel::where('company_id', $user->company_id)
            ->where('tag', $tag)
            ->first();

        if ($existingChannel) {
            return back()->withInput()->withErrors(['tag' => 'This channel tag already exists.']);
        }

        DB::transaction(function () use ($user, $validated, $tag) {
            $channel = TeamChannel::create([
                'company_id' => $user->company_id,
                'created_by' => $user->id,
                'name' => $validated['name'],
                'tag' => $tag,
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'] ?? 'primary',
                'is_private' => $validated['is_private'] ?? false,
            ]);

            // Add creator as admin member
            $channel->addMember($user, null, 'admin');

            // Add invited members
            if (!empty($validated['member_ids'])) {
                foreach ($validated['member_ids'] as $memberId) {
                    $member = User::find($memberId);
                    if ($member && $member->company_id === $user->company_id) {
                        $channel->addMember($member, $user, 'member');
                    }
                }
            }
        });

        return redirect()->route('channels.index')
            ->with('success', 'Team channel created successfully.');
    }

    /**
     * Display the specified channel.
     */
    public function show(Request $request, TeamChannel $channel): View
    {
        $user = $request->user();

        if (!$channel->canView($user)) {
            abort(403, 'You do not have permission to view this channel.');
        }

        // Load members and pending join requests for UI
        $channel->load(['members', 'pendingJoinRequests']);

        // Get all channels for sidebar
        $allChannels = TeamChannel::visibleTo($user)
            ->orderBy('name')
            ->get();

        // Check if user can view threads (members, admins, or public channels)
        $canViewThreads = $channel->canAccess($user);
        $hasPendingRequest = $channel->hasPendingJoinRequest($user);

        // Only load threads if user can view them
        $threads = collect();
        if ($canViewThreads) {
            $threads = $channel->threads()
                ->with(['creator', 'allReplies.user'])
                ->withCount('allReplies')
                ->orderByDesc('is_pinned')
                ->orderByDesc('last_reply_at')
                ->paginate(20);
        }

        return view('discussion::channels.show', [
            'channel' => $channel,
            'threads' => $threads,
            'allChannels' => $allChannels,
            'user' => $user,
            'canViewThreads' => $canViewThreads,
            'hasPendingRequest' => $hasPendingRequest,
        ]);
    }

    /**
     * Show the form for editing the channel.
     */
    public function edit(Request $request, TeamChannel $channel): View
    {
        $user = $request->user();

        if (!$channel->canManage($user)) {
            abort(403, 'You do not have permission to edit this channel.');
        }

        $members = User::where('company_id', $user->company_id)
            ->where('id', '!=', $user->id)
            ->where('role', '!=', User::ROLE_GUEST)
            ->orderBy('first_name')
            ->get();

        $channelMemberIds = $channel->members->pluck('id')->toArray();

        return view('discussion::channels.edit', [
            'channel' => $channel,
            'user' => $user,
            'members' => $members,
            'channelMemberIds' => $channelMemberIds,
        ]);
    }

    /**
     * Update the specified channel.
     */
    public function update(Request $request, TeamChannel $channel): RedirectResponse
    {
        $user = $request->user();

        if (!$channel->canManage($user)) {
            abort(403, 'You do not have permission to edit this channel.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'tag' => ['required', 'string', 'max:50'],
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|in:primary,secondary,accent,info,success,warning,error,orange,teal,indigo,gray',
            'is_private' => 'nullable|boolean',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        // Ensure tag is lowercase and has #
        $tag = strtolower(trim($validated['tag']));
        if (!str_starts_with($tag, '#')) {
            $tag = '#' . $tag;
        }

        // Check if tag already exists for another channel
        $existingChannel = TeamChannel::where('company_id', $user->company_id)
            ->where('tag', $tag)
            ->where('id', '!=', $channel->id)
            ->first();

        if ($existingChannel) {
            return back()->withInput()->withErrors(['tag' => 'This channel tag already exists.']);
        }

        DB::transaction(function () use ($channel, $user, $validated, $tag) {
            $channel->update([
                'name' => $validated['name'],
                'tag' => $tag,
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'] ?? 'primary',
                'is_private' => $validated['is_private'] ?? false,
            ]);

            // Sync members (keep creator always)
            $newMemberIds = $validated['member_ids'] ?? [];
            $currentMemberIds = $channel->members->pluck('id')->toArray();

            // Remove members not in new list (except creator)
            foreach ($currentMemberIds as $memberId) {
                if (!in_array($memberId, $newMemberIds) && $memberId !== $channel->created_by) {
                    $channel->members()->detach($memberId);
                }
            }

            // Add new members
            foreach ($newMemberIds as $memberId) {
                if (!in_array($memberId, $currentMemberIds)) {
                    $member = User::find($memberId);
                    if ($member && $member->company_id === $user->company_id) {
                        $channel->addMember($member, $user, 'member');
                    }
                }
            }

            $channel->updateMembersCount();
        });

        return redirect()->route('channels.show', $channel)
            ->with('success', 'Team channel updated successfully.');
    }

    /**
     * Remove the specified channel.
     */
    public function destroy(Request $request, TeamChannel $channel): RedirectResponse
    {
        $user = $request->user();

        if (!$channel->canManage($user)) {
            abort(403, 'You do not have permission to delete this channel.');
        }

        $channel->delete();

        return redirect()->route('channels.index')
            ->with('success', 'Team channel deleted successfully.');
    }

    /**
     * Join a public channel.
     */
    public function join(Request $request, TeamChannel $channel): RedirectResponse
    {
        $user = $request->user();

        if ($channel->is_private) {
            abort(403, 'You cannot join a private channel.');
        }

        if ($channel->company_id !== $user->company_id) {
            abort(403, 'You cannot join this channel.');
        }

        $channel->addMember($user);

        return redirect()->route('channels.show', $channel)
            ->with('success', 'You have joined the channel.');
    }

    /**
     * Leave a channel.
     */
    public function leave(Request $request, TeamChannel $channel): RedirectResponse
    {
        $user = $request->user();

        if ($user->id === $channel->created_by) {
            return back()->with('error', 'Channel creator cannot leave the channel.');
        }

        $channel->removeMember($user);

        return redirect()->route('channels.index')
            ->with('success', 'You have left the channel.');
    }

    /**
     * Request to join a channel.
     */
    public function requestJoin(Request $request, TeamChannel $channel): RedirectResponse
    {
        $user = $request->user();

        // Check if user is from the same company
        if ($channel->company_id !== $user->company_id) {
            abort(403, 'You cannot request to join this channel.');
        }

        // Check if already a member
        if ($channel->isMember($user)) {
            return back()->with('info', 'You are already a member of this channel.');
        }

        // Check if already has pending request
        if ($channel->hasPendingJoinRequest($user)) {
            return back()->with('info', 'You already have a pending join request for this channel.');
        }

        $validated = $request->validate([
            'message' => 'nullable|string|max:500',
        ]);

        TeamChannelJoinRequest::create([
            'channel_id' => $channel->id,
            'user_id' => $user->id,
            'message' => $validated['message'] ?? null,
            'status' => TeamChannelJoinRequest::STATUS_PENDING,
        ]);

        // Notify channel admins (creator and admins)
        $this->notifyChannelAdminsOfJoinRequest($channel, $user);

        return back()->with('success', 'Your join request has been submitted.');
    }

    /**
     * View pending join requests for a channel (admin/owner only).
     */
    public function joinRequests(Request $request, TeamChannel $channel): View
    {
        $user = $request->user();

        if (!$channel->canManage($user)) {
            abort(403, 'You do not have permission to manage join requests.');
        }

        $pendingRequests = $channel->pendingJoinRequests()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all channels for sidebar
        $allChannels = TeamChannel::visibleTo($user)
            ->orderBy('name')
            ->get();

        return view('discussion::channels.join-requests', [
            'channel' => $channel,
            'pendingRequests' => $pendingRequests,
            'allChannels' => $allChannels,
            'user' => $user,
        ]);
    }

    /**
     * Approve a join request.
     */
    public function approveJoinRequest(Request $request, TeamChannelJoinRequest $joinRequest): RedirectResponse
    {
        $user = $request->user();
        $channel = $joinRequest->channel;

        if (!$channel->canManage($user)) {
            abort(403, 'You do not have permission to approve join requests.');
        }

        if (!$joinRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $joinRequest->approve($user);

        // The notification is already sent via addMember in the approve method

        return back()->with('success', 'Join request approved. User has been added to the channel.');
    }

    /**
     * Reject a join request.
     */
    public function rejectJoinRequest(Request $request, TeamChannelJoinRequest $joinRequest): RedirectResponse
    {
        $user = $request->user();
        $channel = $joinRequest->channel;

        if (!$channel->canManage($user)) {
            abort(403, 'You do not have permission to reject join requests.');
        }

        if (!$joinRequest->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $joinRequest->reject($user);

        // Notify user that their request was rejected
        $this->notifyUserOfRejection($joinRequest, $user);

        return back()->with('success', 'Join request rejected.');
    }

    /**
     * Cancel a join request (by the requester).
     */
    public function cancelJoinRequest(Request $request, TeamChannel $channel): RedirectResponse
    {
        $user = $request->user();

        $joinRequest = $channel->getPendingJoinRequest($user);

        if (!$joinRequest) {
            return back()->with('error', 'No pending join request found.');
        }

        $joinRequest->delete();

        return back()->with('success', 'Join request cancelled.');
    }

    /**
     * Notify channel admins of a new join request.
     */
    protected function notifyChannelAdminsOfJoinRequest(TeamChannel $channel, User $requester): void
    {
        // Get creator and admin members
        $adminIds = [$channel->created_by];
        $adminMembers = $channel->members()
            ->wherePivot('role', 'admin')
            ->pluck('user_id')
            ->toArray();
        $adminIds = array_unique(array_merge($adminIds, $adminMembers));

        foreach ($adminIds as $adminId) {
            if ($adminId === $requester->id) {
                continue;
            }

            Notification::create([
                'user_id' => $adminId,
                'type' => 'channel_join_request',
                'title' => 'New join request',
                'message' => "{$requester->name} requested to join channel: {$channel->name}",
                'notifiable_type' => TeamChannel::class,
                'notifiable_id' => $channel->id,
                'data' => [
                    'requester_id' => $requester->id,
                    'requester_name' => $requester->name,
                    'requester_avatar' => $requester->avatar_url,
                    'channel_id' => $channel->id,
                    'channel_uuid' => $channel->uuid,
                    'channel_name' => $channel->name,
                    'channel_url' => route('channels.join-requests', $channel->uuid),
                ],
            ]);
        }
    }

    /**
     * Notify user that their join request was rejected.
     */
    protected function notifyUserOfRejection(TeamChannelJoinRequest $joinRequest, User $reviewer): void
    {
        $channel = $joinRequest->channel;

        Notification::create([
            'user_id' => $joinRequest->user_id,
            'type' => 'channel_join_rejected',
            'title' => 'Join request rejected',
            'message' => "Your request to join channel: {$channel->name} was rejected.",
            'notifiable_type' => TeamChannel::class,
            'notifiable_id' => $channel->id,
            'data' => [
                'reviewer_id' => $reviewer->id,
                'reviewer_name' => $reviewer->name,
                'channel_id' => $channel->id,
                'channel_uuid' => $channel->uuid,
                'channel_name' => $channel->name,
            ],
        ]);
    }
}
