<?php

declare(strict_types=1);

namespace App\Modules\Standup\Models;

use App\Models\User;
use App\Modules\Core\Traits\HasUuid;
use App\Modules\Standup\Enums\MoodType;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandupEntry extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid',
        'workspace_id',
        'user_id',
        'template_id',
        'standup_date',
        'responses',
        'mood',
        'has_blockers',
    ];

    protected function casts(): array
    {
        return [
            'responses' => 'array',
            'standup_date' => 'date',
            'has_blockers' => 'boolean',
            'mood' => MoodType::class,
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(StandupTemplate::class, 'template_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('standup_date', $date);
    }

    public function scopeWithBlockers($query)
    {
        return $query->where('has_blockers', true);
    }

    public function scopeForWorkspace($query, int $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the response for a specific question type.
     */
    public function getResponseByType(string $type): ?string
    {
        $responses = $this->responses ?? [];
        $response = collect($responses)->firstWhere('type', $type);

        return $response['answer'] ?? null;
    }

    /**
     * Get yesterday's work response.
     */
    public function getYesterdayResponse(): ?string
    {
        return $this->getResponseByType('yesterday');
    }

    /**
     * Get today's plan response.
     */
    public function getTodayResponse(): ?string
    {
        return $this->getResponseByType('today');
    }

    /**
     * Get blockers response.
     */
    public function getBlockersResponse(): ?string
    {
        return $this->getResponseByType('blockers');
    }

    /**
     * Get optional notes response.
     */
    public function getOptionalResponse(): ?string
    {
        return $this->getResponseByType('optional');
    }

    /**
     * Get mood emoji.
     */
    public function getMoodEmoji(): string
    {
        return $this->mood?->emoji() ?? '😐';
    }

    /**
     * Get mood label.
     */
    public function getMoodLabel(): string
    {
        return $this->mood?->label() ?? 'Okay';
    }

    /**
     * Check if user has already submitted standup for a date.
     */
    public static function hasSubmittedForDate(int $workspaceId, int $userId, $date): bool
    {
        return self::query()
            ->forWorkspace($workspaceId)
            ->forUser($userId)
            ->forDate($date)
            ->exists();
    }
}
