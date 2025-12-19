<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamChannelThreadResource;
use App\Modules\Discussion\Models\TeamChannel;
use App\Modules\Discussion\Models\TeamChannelThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TeamChannelThreadController extends Controller
{
    /**
     * List threads in a channel.
     */
    public function index(Request $request, TeamChannel $channel): JsonResponse
    {
        $user = $request->user();

        if (!$channel->canView($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this channel.',
            ], 403);
        }

        $query = $channel->threads()
            ->with('creator')
            ->orderByDesc('is_pinned')
            ->orderByDesc('last_reply_at');

        // Optional search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $threads = $query->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => TeamChannelThreadResource::collection($threads),
            'meta' => [
                'current_page' => $threads->currentPage(),
                'last_page' => $threads->lastPage(),
                'per_page' => $threads->perPage(),
                'total' => $threads->total(),
            ],
        ]);
    }

    /**
     * Create a new thread.
     */
    public function store(Request $request, TeamChannel $channel): JsonResponse
    {
        $user = $request->user();

        if (!$channel->canPost($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to post in this channel.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:10000',
            'task_ids' => 'nullable|array',
            'task_ids.*' => 'exists:tasks,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $thread = TeamChannelThread::create([
                'channel_id' => $channel->id,
                'company_id' => $user->company_id,
                'created_by' => $user->id,
                'title' => $request->title,
                'content' => $request->content,
            ]);

            // Sync attached tasks
            if ($request->has('task_ids') && is_array($request->task_ids)) {
                $thread->tasks()->sync($request->task_ids);
            }

            $channel->updateThreadsCount();
            $channel->updateLastActivity();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thread created successfully',
                'data' => new TeamChannelThreadResource($thread->load(['creator', 'tasks.workspace', 'tasks.status'])),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create thread',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get thread with replies.
     */
    public function show(Request $request, TeamChannel $channel, TeamChannelThread $thread): JsonResponse
    {
        $user = $request->user();

        // Ensure thread belongs to channel
        if ($thread->channel_id !== $channel->id) {
            return response()->json([
                'success' => false,
                'message' => 'Thread not found in this channel.',
            ], 404);
        }

        if (!$thread->canView($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this thread.',
            ], 403);
        }

        // Load thread with creator, tasks, and replies (including nested replies)
        $thread->load([
            'creator',
            'tasks.workspace',
            'tasks.status',
            'replies' => function ($query) {
                $query->with(['user', 'replies.user'])
                    ->orderBy('created_at', 'asc');
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => new TeamChannelThreadResource($thread),
        ]);
    }

    /**
     * Update thread.
     */
    public function update(Request $request, TeamChannel $channel, TeamChannelThread $thread): JsonResponse
    {
        $user = $request->user();

        // Ensure thread belongs to channel
        if ($thread->channel_id !== $channel->id) {
            return response()->json([
                'success' => false,
                'message' => 'Thread not found in this channel.',
            ], 404);
        }

        if (!$thread->canEdit($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit this thread.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'content' => 'nullable|string|max:10000',
            'task_ids' => 'nullable|array',
            'task_ids.*' => 'exists:tasks,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $thread->update($request->only(['title', 'content']));

        // Sync attached tasks if provided
        if ($request->has('task_ids')) {
            $thread->tasks()->sync($request->task_ids ?? []);
        }

        return response()->json([
            'success' => true,
            'message' => 'Thread updated successfully',
            'data' => new TeamChannelThreadResource($thread->fresh()->load(['creator', 'tasks.workspace', 'tasks.status'])),
        ]);
    }

    /**
     * Delete thread.
     */
    public function destroy(Request $request, TeamChannel $channel, TeamChannelThread $thread): JsonResponse
    {
        $user = $request->user();

        // Ensure thread belongs to channel
        if ($thread->channel_id !== $channel->id) {
            return response()->json([
                'success' => false,
                'message' => 'Thread not found in this channel.',
            ], 404);
        }

        if (!$thread->canDelete($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this thread.',
            ], 403);
        }

        $thread->delete();
        $channel->updateThreadsCount();

        return response()->json([
            'success' => true,
            'message' => 'Thread deleted successfully',
        ]);
    }

    /**
     * Toggle pin status of thread.
     */
    public function togglePin(Request $request, TeamChannel $channel, TeamChannelThread $thread): JsonResponse
    {
        $user = $request->user();

        // Ensure thread belongs to channel
        if ($thread->channel_id !== $channel->id) {
            return response()->json([
                'success' => false,
                'message' => 'Thread not found in this channel.',
            ], 404);
        }

        if (!$thread->canPin($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to pin threads.',
            ], 403);
        }

        $thread->update(['is_pinned' => !$thread->is_pinned]);

        return response()->json([
            'success' => true,
            'message' => $thread->is_pinned ? 'Thread pinned successfully' : 'Thread unpinned successfully',
            'data' => new TeamChannelThreadResource($thread->fresh()->load('creator')),
        ]);
    }
}
