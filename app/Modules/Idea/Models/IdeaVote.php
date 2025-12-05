<?php

declare(strict_types=1);

namespace App\Modules\Idea\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdeaVote extends Model
{
    protected $fillable = [
        'idea_id',
        'user_id',
        'vote',
    ];

    protected function casts(): array
    {
        return [
            'vote' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (IdeaVote $vote) {
            $vote->idea->updateVotesCount();
        });

        static::updated(function (IdeaVote $vote) {
            $vote->idea->updateVotesCount();
        });

        static::deleted(function (IdeaVote $vote) {
            $vote->idea->updateVotesCount();
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
