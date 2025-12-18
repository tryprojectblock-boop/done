<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamChannelReplyResource;
use App\Modules\Discussion\Models\TeamChannel;
use App\Modules\Discussion\Models\TeamChannelReply;
use App\Modules\Discussion\Models\TeamChannelThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TeamChannelReplyController extends Controller
{
    /**
     * Create a reply to a thread.
     */
    public function store(Request $request, TeamChannel $channel, TeamChannelThread $thread): JsonResponse
    {
        $user = $request->user();

        // Ensure thread belongs to channel
        if ($thread->channel_id !== $channel->id) {
            return response()->json([
                'success' => false,
                'message' => 'Thread not found in this channel.',
            ], 404);
        }

        if (!$thread->canReply($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to reply to this thread.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:10000',
            'parent_id' => 'nullable|integer|exists:team_channel_replies,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // If parent_id is provided, verify it belongs to the same thread
        if ($request->has('parent_id') && $request->parent_id) {
            $parentReply = TeamChannelReply::find($request->parent_id);
            if (!$parentReply || $parentReply->thread_id !== $thread->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent reply not found in this thread.',
                ], 404);
            }
        }

        try {
            DB::beginTransaction();

            $reply = TeamChannelReply::create([
                'thread_id' => $thread->id,
                'user_id' => $user->id,
                'parent_id' => $request->parent_id,
                'content' => $request->content,
            ]);

            $thread->updateRepliesCount();
            $thread->updateLastReply();
            $channel->updateLastActivity();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reply created successfully',
                'data' => new TeamChannelReplyResource($reply->load('user')),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create reply',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update a reply.
     */
    public function update(Request $request, TeamChannelReply $reply): JsonResponse
    {
        $user = $request->user();

        if (!$reply->canEdit($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit this reply.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:10000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $reply->update(['content' => $request->content]);
        $reply->markAsEdited();

        return response()->json([
            'success' => true,
            'message' => 'Reply updated successfully',
            'data' => new TeamChannelReplyResource($reply->fresh()->load('user')),
        ]);
    }

    /**
     * Delete a reply.
     */
    public function destroy(Request $request, TeamChannelReply $reply): JsonResponse
    {
        $user = $request->user();

        if (!$reply->canDelete($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this reply.',
            ], 403);
        }

        $thread = $reply->thread;
        $channel = $thread->channel;

        $reply->delete();

        $thread->updateRepliesCount();
        $thread->updateLastReply();

        return response()->json([
            'success' => true,
            'message' => 'Reply deleted successfully',
        ]);
    }
}
