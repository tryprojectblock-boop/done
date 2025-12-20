<?php

declare(strict_types=1);

namespace App\Modules\Standup\Models;

use App\Models\User;
use App\Modules\Auth\Models\Company;
use App\Modules\Core\Traits\HasUuid;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class StandupTemplate extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid',
        'workspace_id',
        'company_id',
        'name',
        'questions',
        'reminder_time',
        'reminder_timezone',
        'reminder_enabled',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'reminder_enabled' => 'boolean',
            'is_active' => 'boolean',
            'reminder_time' => 'datetime:H:i',
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(StandupEntry::class, 'template_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get default template questions.
     */
    public static function getDefaultQuestions(): array
    {
        return [
            [
                'id' => (string) Str::uuid(),
                'question' => 'What did you work on yesterday?',
                'type' => 'yesterday',
                'required' => true,
                'order' => 1,
                'is_default' => true,
            ],
            [
                'id' => (string) Str::uuid(),
                'question' => 'What are you working on today?',
                'type' => 'today',
                'required' => true,
                'order' => 2,
                'is_default' => true,
            ],
            [
                'id' => (string) Str::uuid(),
                'question' => 'Is anything slowing you down?',
                'type' => 'blockers',
                'required' => true,
                'order' => 3,
                'is_default' => true,
            ],
            [
                'id' => (string) Str::uuid(),
                'question' => 'Anything else the team should know?',
                'type' => 'optional',
                'required' => false,
                'order' => 4,
                'is_default' => true,
            ],
            [
                'id' => (string) Str::uuid(),
                'question' => 'How do you feel today?',
                'type' => 'mood',
                'required' => false,
                'order' => 5,
                'is_default' => true,
            ],
        ];
    }

    /**
     * Create a default template for a workspace.
     */
    public static function createDefault(Workspace $workspace, User $creator): self
    {
        return self::create([
            'workspace_id' => $workspace->id,
            'company_id' => $creator->company_id,
            'name' => 'Default Template',
            'questions' => self::getDefaultQuestions(),
            'reminder_enabled' => false,
            'is_active' => true,
            'created_by' => $creator->id,
        ]);
    }

    /**
     * Add a custom question to the template.
     */
    public function addQuestion(string $question, string $type = 'custom', bool $required = false): void
    {
        $questions = $this->questions ?? [];
        $maxOrder = collect($questions)->max('order') ?? 0;

        $questions[] = [
            'id' => (string) Str::uuid(),
            'question' => $question,
            'type' => $type,
            'required' => $required,
            'order' => $maxOrder + 1,
            'is_default' => false,
        ];

        $this->update(['questions' => $questions]);
    }

    /**
     * Remove a question from the template.
     */
    public function removeQuestion(string $questionId): bool
    {
        $questions = collect($this->questions ?? []);
        $question = $questions->firstWhere('id', $questionId);

        // Don't allow removing default questions
        if ($question && ($question['is_default'] ?? false)) {
            return false;
        }

        $questions = $questions->reject(fn ($q) => $q['id'] === $questionId)->values()->toArray();
        $this->update(['questions' => $questions]);

        return true;
    }

    /**
     * Get questions sorted by order.
     */
    public function getOrderedQuestions(): array
    {
        return collect($this->questions ?? [])->sortBy('order')->values()->toArray();
    }
}
