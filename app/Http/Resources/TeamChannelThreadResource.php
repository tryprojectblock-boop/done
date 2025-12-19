<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamChannelThreadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'channel_id' => $this->channel_id,
            'title' => $this->title,
            'content' => $this->content,
            'is_pinned' => $this->is_pinned,
            'replies_count' => $this->replies_count,
            'last_reply_at' => $this->last_reply_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'creator' => new UserSimpleResource($this->whenLoaded('creator')),
            'tasks' => $this->when($this->relationLoaded('tasks'), function () {
                return $this->tasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'uuid' => $task->uuid,
                        'task_number' => $task->task_number,
                        'title' => $task->title,
                        'workspace' => $task->workspace ? [
                            'id' => $task->workspace->id,
                            'name' => $task->workspace->name,
                            'prefix' => $task->workspace->prefix,
                        ] : null,
                        'status' => $task->status ? [
                            'id' => $task->status->id,
                            'name' => $task->status->name,
                            'color' => $task->status->color,
                        ] : null,
                    ];
                });
            }),
            'replies' => TeamChannelReplyResource::collection($this->whenLoaded('replies')),
        ];
    }
}
