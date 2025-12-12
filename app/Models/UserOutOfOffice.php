<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOutOfOffice extends Model
{
    use HasFactory;

    protected $table = 'user_out_of_office';

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'message',
        'auto_respond_message',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the out of office is currently active (within date range).
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $today = now()->startOfDay();
        return $today->between($this->start_date, $this->end_date);
    }

    /**
     * Scope to get active out of office entries for a user.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get currently effective out of office entries.
     */
    public function scopeCurrentlyEffective($query)
    {
        $today = now()->toDateString();
        return $query->where('is_active', true)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }
}
