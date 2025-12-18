<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamChannelResource extends JsonResource
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
            'name' => $this->name,
            'tag' => $this->tag,
            'description' => $this->description,
            'color' => $this->color,
            'is_private' => $this->is_private,
            'status' => $this->status,
            'members_count' => $this->members_count,
            'threads_count' => $this->threads_count,
            'last_activity_at' => $this->last_activity_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'creator' => new UserSimpleResource($this->whenLoaded('creator')),
            'is_member' => $this->when(
                auth()->check(),
                fn() => $this->isMember(auth()->user())
            ),
        ];
    }
}
