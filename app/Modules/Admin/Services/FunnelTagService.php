<?php

declare(strict_types=1);

namespace App\Modules\Admin\Services;

use App\Models\User;
use App\Modules\Admin\Models\Funnel;
use App\Modules\Admin\Models\FunnelSubscriber;
use App\Modules\Admin\Models\FunnelTag;
use Illuminate\Support\Carbon;

class FunnelTagService
{
    /**
     * Add a tag to a user and trigger any associated funnels.
     */
    public function addTag(User $user, string $tagName): bool
    {
        $tag = FunnelTag::where('name', $tagName)->first();

        if (!$tag) {
            return false;
        }

        // Check if user already has this tag
        if ($this->hasTag($user, $tagName)) {
            return true;
        }

        // Attach the tag
        $user->funnelTags()->attach($tag->id, [
            'tagged_at' => now(),
        ]);

        // Trigger any funnels that use this tag as a trigger
        $this->triggerFunnels($user, $tag);

        return true;
    }

    /**
     * Remove a tag from a user.
     */
    public function removeTag(User $user, string $tagName): bool
    {
        $tag = FunnelTag::where('name', $tagName)->first();

        if (!$tag) {
            return false;
        }

        $user->funnelTags()->detach($tag->id);

        return true;
    }

    /**
     * Check if a user has a specific tag.
     */
    public function hasTag(User $user, string $tagName): bool
    {
        return $user->funnelTags()
            ->where('name', $tagName)
            ->exists();
    }

    /**
     * Get all tags for a user.
     */
    public function getUserTags(User $user): array
    {
        return $user->funnelTags()
            ->pluck('name')
            ->toArray();
    }

    /**
     * Add multiple tags to a user.
     */
    public function addTags(User $user, array $tagNames): void
    {
        foreach ($tagNames as $tagName) {
            $this->addTag($user, $tagName);
        }
    }

    /**
     * Sync user tags (removes existing, adds new).
     */
    public function syncTags(User $user, array $tagNames): void
    {
        $tags = FunnelTag::whereIn('name', $tagNames)->get();

        $syncData = [];
        foreach ($tags as $tag) {
            $syncData[$tag->id] = ['tagged_at' => now()];
        }

        $user->funnelTags()->sync($syncData);
    }

    /**
     * Trigger funnels that use this tag as their trigger.
     */
    protected function triggerFunnels(User $user, FunnelTag $tag): void
    {
        $funnels = Funnel::where('trigger_tag_id', $tag->id)
            ->where('is_active', true)
            ->get();

        foreach ($funnels as $funnel) {
            // Check if user is already subscribed to this funnel
            $existingSubscription = FunnelSubscriber::where('user_id', $user->id)
                ->where('funnel_id', $funnel->id)
                ->first();

            if (!$existingSubscription) {
                FunnelSubscriber::create([
                    'user_id' => $user->id,
                    'funnel_id' => $funnel->id,
                    'current_step' => 0,
                    'subscribed_at' => now(),
                    'status' => 'active',
                ]);
            }
        }
    }

    /**
     * Get or create a tag by name.
     */
    public function getOrCreateTag(string $name, ?string $displayName = null): FunnelTag
    {
        return FunnelTag::firstOrCreate(
            ['name' => $name],
            [
                'display_name' => $displayName ?? ucwords(str_replace('_', ' ', $name)),
                'is_system' => false,
            ]
        );
    }

    /**
     * Get tag by name.
     */
    public function getTag(string $name): ?FunnelTag
    {
        return FunnelTag::where('name', $name)->first();
    }

    /**
     * Get users who have a specific tag.
     */
    public function getUsersWithTag(string $tagName): \Illuminate\Database\Eloquent\Collection
    {
        $tag = $this->getTag($tagName);

        if (!$tag) {
            return collect();
        }

        return $tag->users;
    }

    /**
     * Get the timestamp when user received a tag.
     */
    public function getTaggedAt(User $user, string $tagName): ?Carbon
    {
        $tag = $user->funnelTags()
            ->where('name', $tagName)
            ->first();

        if (!$tag) {
            return null;
        }

        return Carbon::parse($tag->pivot->tagged_at);
    }
}
