<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Discussion\Models\TeamChannel;
use App\Modules\Discussion\Models\TeamChannelThread;
use App\Modules\Discussion\Models\TeamChannelReply;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeamChannelThreadController extends Controller
{
    /**
     * Show the form for creating a new thread.
     */
    public function create(Request $request, TeamChannel $channel): View
    {
        $user = $request->user();

        if (!$channel->canPost($user)) {
            abort(403, 'You do not have permission to post in this channel.');
        }

        // Get all channels for sidebar
        $allChannels = TeamChannel::visibleTo($user)
            ->orderBy('name')
            ->get();

        return view('discussion::channels.threads.create', [
            'channel' => $channel,
            'allChannels' => $allChannels,
            'user' => $user,
        ]);
    }

    /**
     * Store a new thread in the channel.
     */
    public function store(Request $request, TeamChannel $channel): RedirectResponse
    {
        $user = $request->user();

        if (!$channel->canPost($user)) {
            abort(403, 'You do not have permission to post in this channel.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:50000',
        ]);

        DB::transaction(function () use ($channel, $user, $validated) {
            $thread = TeamChannelThread::create([
                'channel_id' => $channel->id,
                'company_id' => $user->company_id,
                'created_by' => $user->id,
                'title' => $validated['title'],
                'content' => $validated['content'] ?? null,
                'last_reply_at' => now(),
            ]);

            $channel->updateThreadsCount();
            $channel->updateLastActivity();
        });

        return redirect()->route('channels.show', $channel)
            ->with('success', 'Thread created successfully.');
    }

    /**
     * Display the thread with replies.
     */
    public function show(Request $request, TeamChannel $channel, TeamChannelThread $thread): View
    {
        $user = $request->user();

        if (!$thread->canView($user)) {
            abort(403, 'You do not have permission to view this thread.');
        }

        $thread->load(['creator', 'replies.user', 'replies.replies.user']);

        // Get all channels for sidebar
        $allChannels = TeamChannel::visibleTo($user)
            ->orderBy('name')
            ->get();

        return view('discussion::channels.thread', [
            'channel' => $channel,
            'thread' => $thread,
            'allChannels' => $allChannels,
            'user' => $user,
        ]);
    }

    /**
     * Update a thread.
     */
    public function update(Request $request, TeamChannel $channel, TeamChannelThread $thread): RedirectResponse
    {
        $user = $request->user();

        if (!$thread->canEdit($user)) {
            abort(403, 'You do not have permission to edit this thread.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:50000',
        ]);

        $thread->update($validated);

        return redirect()->route('channels.threads.show', [$channel, $thread])
            ->with('success', 'Thread updated successfully.');
    }

    /**
     * Delete a thread.
     */
    public function destroy(Request $request, TeamChannel $channel, TeamChannelThread $thread): RedirectResponse
    {
        $user = $request->user();

        if (!$thread->canDelete($user)) {
            abort(403, 'You do not have permission to delete this thread.');
        }

        $thread->delete();
        $channel->updateThreadsCount();

        return redirect()->route('channels.show', $channel)
            ->with('success', 'Thread deleted successfully.');
    }

    /**
     * Toggle pin status.
     */
    public function togglePin(Request $request, TeamChannel $channel, TeamChannelThread $thread): RedirectResponse
    {
        $user = $request->user();

        if (!$thread->canPin($user)) {
            abort(403, 'You do not have permission to pin this thread.');
        }

        $thread->update(['is_pinned' => !$thread->is_pinned]);

        return redirect()->route('channels.show', $channel)
            ->with('success', $thread->is_pinned ? 'Thread pinned.' : 'Thread unpinned.');
    }

    /**
     * Store a reply to a thread.
     */
    public function storeReply(Request $request, TeamChannel $channel, TeamChannelThread $thread): RedirectResponse
    {
        $user = $request->user();

        if (!$thread->canReply($user)) {
            abort(403, 'You do not have permission to reply to this thread.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:10000',
            'parent_id' => 'nullable|exists:team_channel_replies,id',
        ]);

        $reply = null;
        DB::transaction(function () use ($thread, $channel, $user, $validated, &$reply) {
            $reply = TeamChannelReply::create([
                'thread_id' => $thread->id,
                'user_id' => $user->id,
                'parent_id' => $validated['parent_id'] ?? null,
                'content' => $validated['content'],
            ]);

            $thread->updateRepliesCount();
            $thread->updateLastReply();
            $channel->updateLastActivity();
        });

        // Send notifications for mentions in the reply
        if ($reply && !empty($validated['content'])) {
            app(NotificationService::class)->notifyMentionedUsersInChannelReply(
                $validated['content'],
                $user,
                $thread,
                $reply
            );
        }

        return redirect()->route('channels.threads.show', [$channel, $thread])
            ->with('success', 'Reply added successfully.');
    }

    /**
     * Update a reply.
     */
    public function updateReply(Request $request, TeamChannelReply $reply): RedirectResponse
    {
        $user = $request->user();

        if (!$reply->canEdit($user)) {
            abort(403, 'You do not have permission to edit this reply.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        $reply->update(['content' => $validated['content']]);
        $reply->markAsEdited();

        $thread = $reply->thread;
        $channel = $thread->channel;

        return redirect()->route('channels.threads.show', [$channel, $thread])
            ->with('success', 'Reply updated successfully.');
    }

    /**
     * Delete a reply.
     */
    public function destroyReply(Request $request, TeamChannelReply $reply): RedirectResponse
    {
        $user = $request->user();

        if (!$reply->canDelete($user)) {
            abort(403, 'You do not have permission to delete this reply.');
        }

        $thread = $reply->thread;
        $channel = $thread->channel;

        $reply->delete();
        $thread->updateRepliesCount();

        return redirect()->route('channels.threads.show', [$channel, $thread])
            ->with('success', 'Reply deleted successfully.');
    }
}
