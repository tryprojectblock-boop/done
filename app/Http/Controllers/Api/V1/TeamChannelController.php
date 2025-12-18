<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamChannelResource;
use App\Http\Resources\UserSimpleResource;
use App\Models\User;
use App\Modules\Discussion\Models\TeamChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TeamChannelController extends Controller
{
    /**
     * List channels accessible by the user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $channels = TeamChannel::query()
            ->visibleTo($user)
            ->notArchived()
            ->with('creator')
            ->orderBy('last_activity_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => TeamChannelResource::collection($channels),
            'meta' => [
                'current_page' => $channels->currentPage(),
                'last_page' => $channels->lastPage(),
                'per_page' => $channels->perPage(),
                'total' => $channels->total(),
            ],
        ]);
    }

    /**
     * Create a new channel.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only admins can create channels
        if (!$user->isAdminOrHigher()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create channels.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'tag' => 'required|string|max:50|unique:team_channels,tag,NULL,id,company_id,' . $user->company_id,
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|in:primary,secondary,accent,info,success,warning,error,orange,teal,indigo,gray',
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

            $channel = TeamChannel::create([
                'company_id' => $user->company_id,
                'created_by' => $user->id,
                'name' => $request->name,
                'tag' => $request->tag,
                'description' => $request->description,
                'color' => $request->input('color', 'primary'),
                'status' => TeamChannel::STATUS_ACTIVE,
            ]);

            // Add creator as admin member
            $channel->addMember($user, null, 'admin');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Channel created successfully',
                'data' => new TeamChannelResource($channel->load('creator')),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create channel',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get channel details.
     */
    public function show(Request $request, TeamChannel $channel): JsonResponse
    {
        $user = $request->user();

        if (!$channel->canView($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this channel.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new TeamChannelResource($channel->load('creator')),
        ]);
    }

    /**
     * Update channel.
     */
    public function update(Request $request, TeamChannel $channel): JsonResponse
    {
        $user = $request->user();

        if (!$channel->canManage($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this channel.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'tag' => 'sometimes|string|max:50|unique:team_channels,tag,' . $channel->id . ',id,company_id,' . $user->company_id,
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|in:primary,secondary,accent,info,success,warning,error,orange,teal,indigo,gray',
            'status' => 'sometimes|string|in:active,inactive,archive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $channel->update($request->only(['name', 'tag', 'description', 'color', 'status']));

        return response()->json([
            'success' => true,
            'message' => 'Channel updated successfully',
            'data' => new TeamChannelResource($channel->fresh()->load('creator')),
        ]);
    }

    /**
     * Delete/archive channel.
     */
    public function destroy(Request $request, TeamChannel $channel): JsonResponse
    {
        $user = $request->user();

        if (!$channel->canManage($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this channel.',
            ], 403);
        }

        // Archive instead of hard delete
        $channel->update(['status' => TeamChannel::STATUS_ARCHIVE]);

        return response()->json([
            'success' => true,
            'message' => 'Channel archived successfully',
        ]);
    }

    /**
     * Join a channel (for public channels or if invited).
     */
    public function join(Request $request, TeamChannel $channel): JsonResponse
    {
        $user = $request->user();

        if ($user->company_id !== $channel->company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Channel not found.',
            ], 404);
        }

        if ($channel->isMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already a member of this channel.',
            ], 422);
        }

        $channel->addMember($user);

        return response()->json([
            'success' => true,
            'message' => 'Joined channel successfully',
            'data' => new TeamChannelResource($channel->fresh()->load('creator')),
        ]);
    }

    /**
     * Leave a channel.
     */
    public function leave(Request $request, TeamChannel $channel): JsonResponse
    {
        $user = $request->user();

        if (!$channel->isMember($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this channel.',
            ], 422);
        }

        if ($user->id === $channel->created_by) {
            return response()->json([
                'success' => false,
                'message' => 'Channel creator cannot leave the channel.',
            ], 422);
        }

        $channel->removeMember($user);

        return response()->json([
            'success' => true,
            'message' => 'Left channel successfully',
        ]);
    }

    /**
     * List channel members.
     */
    public function members(Request $request, TeamChannel $channel): JsonResponse
    {
        $user = $request->user();

        if (!$channel->canView($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to view this channel.',
            ], 403);
        }

        $members = $channel->members()
            ->orderBy('pivot_joined_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $members->map(function ($member) {
                return [
                    'user' => new UserSimpleResource($member),
                    'role' => $member->pivot->role,
                    'joined_at' => $member->pivot->joined_at?->toISOString(),
                ];
            }),
        ]);
    }

    /**
     * Add member to channel.
     */
    public function addMember(Request $request, TeamChannel $channel): JsonResponse
    {
        $user = $request->user();

        if (!$channel->canManage($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to add members.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'nullable|string|in:admin,member',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $memberUser = User::where('id', $request->user_id)
            ->where('company_id', $user->company_id)
            ->first();

        if (!$memberUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in your company.',
            ], 404);
        }

        if ($channel->isMember($memberUser)) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a member of this channel.',
            ], 422);
        }

        $channel->addMember($memberUser, $user, $request->input('role', 'member'));

        return response()->json([
            'success' => true,
            'message' => 'Member added successfully',
        ]);
    }

    /**
     * Remove member from channel.
     */
    public function removeMember(Request $request, TeamChannel $channel, User $member): JsonResponse
    {
        $user = $request->user();

        if (!$channel->canManage($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to remove members.',
            ], 403);
        }

        if ($member->id === $channel->created_by) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove the channel creator.',
            ], 422);
        }

        if (!$channel->isMember($member)) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a member of this channel.',
            ], 422);
        }

        $channel->removeMember($member);

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully',
        ]);
    }
}
