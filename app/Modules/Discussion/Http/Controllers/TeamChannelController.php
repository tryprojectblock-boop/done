<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Discussion\Models\TeamChannel;
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
            'status' => 'nullable|string|in:active,inactive,archive',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        // Ensure tag is lowercase and has #
        $tag = strtolower(trim($validated['tag']));
        if (!str_starts_with($tag, '#')) {
            $tag = '#' . $tag;
        }

        // Check if tag already exists for this company (only non-deleted)
        $existingChannel = TeamChannel::where('company_id', $user->company_id)
            ->where('tag', $tag)
            ->first();

        if ($existingChannel) {
            return back()->withInput()->withErrors(['tag' => 'This channel tag already exists.']);
        }

        // Force delete any soft-deleted channel with same tag to avoid unique constraint
        TeamChannel::onlyTrashed()
            ->where('company_id', $user->company_id)
            ->where('tag', $tag)
            ->forceDelete();

        DB::transaction(function () use ($user, $validated, $tag) {
            $channel = TeamChannel::create([
                'company_id' => $user->company_id,
                'created_by' => $user->id,
                'name' => $validated['name'],
                'tag' => $tag,
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'] ?? 'primary',
                'status' => $validated['status'] ?? TeamChannel::STATUS_ACTIVE,
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

        // Load members for UI
        $channel->load(['members']);

        // Get all channels for sidebar
        $allChannels = TeamChannel::visibleTo($user)
            ->orderBy('name')
            ->get();

        // Load threads
        $threads = $channel->threads()
            ->with(['creator', 'allReplies.user'])
            ->withCount('allReplies')
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_reply_at')
            ->paginate(20);

        return view('discussion::channels.show', [
            'channel' => $channel,
            'threads' => $threads,
            'allChannels' => $allChannels,
            'user' => $user,
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
            'status' => 'nullable|string|in:active,inactive,archive',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        // Ensure tag is lowercase and has #
        $tag = strtolower(trim($validated['tag']));
        if (!str_starts_with($tag, '#')) {
            $tag = '#' . $tag;
        }

        // Check if tag already exists for another channel (only non-deleted)
        $existingChannel = TeamChannel::where('company_id', $user->company_id)
            ->where('tag', $tag)
            ->where('id', '!=', $channel->id)
            ->first();

        if ($existingChannel) {
            return back()->withInput()->withErrors(['tag' => 'This channel tag already exists.']);
        }

        // Force delete any soft-deleted channel with same tag to avoid unique constraint
        TeamChannel::onlyTrashed()
            ->where('company_id', $user->company_id)
            ->where('tag', $tag)
            ->forceDelete();

        DB::transaction(function () use ($channel, $user, $validated, $tag) {
            $channel->update([
                'name' => $validated['name'],
                'tag' => $tag,
                'description' => $validated['description'] ?? null,
                'color' => $validated['color'] ?? 'primary',
                'status' => $validated['status'] ?? TeamChannel::STATUS_ACTIVE,
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
     * Invite a team member to the channel (admin/owner only).
     */
    public function inviteMember(Request $request, TeamChannel $channel): RedirectResponse
    {
        $user = $request->user();

        if (!$channel->canManage($user)) {
            abort(403, 'Only administrators and owners can invite members.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $memberToInvite = User::find($validated['user_id']);

        if (!$memberToInvite || $memberToInvite->company_id !== $user->company_id) {
            return back()->with('error', 'Invalid user selected.');
        }

        if ($channel->isMember($memberToInvite)) {
            return back()->with('info', 'This user is already a member of this channel.');
        }

        $channel->addMember($memberToInvite, $user, 'member');

        return back()->with('success', "{$memberToInvite->name} has been added to the channel.");
    }

    /**
     * Remove a member from the channel (admin/owner only).
     */
    public function removeMember(Request $request, TeamChannel $channel, User $member): RedirectResponse
    {
        $user = $request->user();

        if (!$channel->canManage($user)) {
            abort(403, 'Only administrators and owners can remove members.');
        }

        if ($member->id === $channel->created_by) {
            return back()->with('error', 'Cannot remove the channel creator.');
        }

        if (!$channel->isMember($member)) {
            return back()->with('error', 'This user is not a member of this channel.');
        }

        $channel->removeMember($member);

        return back()->with('success', "{$member->name} has been removed from the channel.");
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
     * View team members to invite to channel (admin/owner only).
     */
    public function manageMembers(Request $request, TeamChannel $channel): View
    {
        $user = $request->user();

        if (!$channel->canManage($user)) {
            abort(403, 'You do not have permission to manage channel members.');
        }

        // Get current channel member IDs
        $currentMemberIds = $channel->members->pluck('id')->toArray();

        // Get team members who are not already in the channel
        $availableMembers = User::where('company_id', $user->company_id)
            ->whereNotIn('id', $currentMemberIds)
            ->where('role', '!=', User::ROLE_GUEST)
            ->orderBy('first_name')
            ->get();

        // Get all channels for sidebar
        $allChannels = TeamChannel::visibleTo($user)
            ->orderBy('name')
            ->get();

        return view('discussion::channels.manage-members', [
            'channel' => $channel,
            'availableMembers' => $availableMembers,
            'allChannels' => $allChannels,
            'user' => $user,
        ]);
    }
}
