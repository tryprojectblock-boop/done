<?php

declare(strict_types=1);

namespace App\Modules\Standup\Models;

use App\Models\User;
use App\Modules\Core\Traits\HasUuid;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberTracker extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid',
        'workspace_id',
        'user_id',
        'is_on_track',
        'off_track_reason',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_on_track' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOnTrack($query)
    {
        return $query->where('is_on_track', true);
    }

    public function scopeOffTrack($query)
    {
        return $query->where('is_on_track', false);
    }

    public function scopeForWorkspace($query, int $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Mark member as on track.
     */
    public function markOnTrack(User $updatedBy): void
    {
        $this->update([
            'is_on_track' => true,
            'off_track_reason' => null,
            'updated_by' => $updatedBy->id,
        ]);
    }

    /**
     * Mark member as off track with reason.
     */
    public function markOffTrack(string $reason, User $updatedBy): void
    {
        $this->update([
            'is_on_track' => false,
            'off_track_reason' => $reason,
            'updated_by' => $updatedBy->id,
        ]);
    }

    /**
     * Get or create tracker for a workspace member.
     */
    public static function getOrCreateForMember(Workspace $workspace, User $user): self
    {
        return self::firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
            ],
            [
                'is_on_track' => true,
            ]
        );
    }

    /**
     * Get on-track percentage for a workspace.
     */
    public static function getOnTrackPercentage(int $workspaceId): float
    {
        $total = self::forWorkspace($workspaceId)->count();

        if ($total === 0) {
            return 100.0;
        }

        $onTrack = self::forWorkspace($workspaceId)->onTrack()->count();

        return round(($onTrack / $total) * 100, 1);
    }

    /**
     * Get on-track stats for a workspace.
     */
    public static function getStats(int $workspaceId): array
    {
        $total = self::forWorkspace($workspaceId)->count();
        $onTrack = self::forWorkspace($workspaceId)->onTrack()->count();
        $offTrack = $total - $onTrack;

        return [
            'total' => $total,
            'on_track' => $onTrack,
            'off_track' => $offTrack,
            'percentage' => $total > 0 ? round(($onTrack / $total) * 100, 1) : 100.0,
        ];
    }
}
