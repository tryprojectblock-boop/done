<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WorkspaceTicketFormField extends Model
{
    protected $fillable = [
        'form_id',
        'type',
        'label',
        'name',
        'placeholder',
        'help_text',
        'is_required',
        'options',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'options' => 'array',
        ];
    }

    // Field types
    public const TYPE_TEXT = 'text';
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_EMAIL = 'email';
    public const TYPE_PHONE = 'phone';
    public const TYPE_DATE = 'date';
    public const TYPE_SELECT = 'select';
    public const TYPE_FILE = 'file';

    /**
     * Get available field types.
     */
    public static function getFieldTypes(): array
    {
        return [
            self::TYPE_TEXT => [
                'label' => 'Input Field',
                'icon' => 'tabler--forms',
                'description' => 'Single line text input',
            ],
            self::TYPE_TEXTAREA => [
                'label' => 'Text Area',
                'icon' => 'tabler--align-left',
                'description' => 'Multi-line text area',
            ],
            self::TYPE_EMAIL => [
                'label' => 'Email Field',
                'icon' => 'tabler--mail',
                'description' => 'Email input with validation',
            ],
            self::TYPE_PHONE => [
                'label' => 'Phone Field',
                'icon' => 'tabler--phone',
                'description' => 'Phone number input',
            ],
            self::TYPE_DATE => [
                'label' => 'Date Picker',
                'icon' => 'tabler--calendar',
                'description' => 'Date selection field',
            ],
            self::TYPE_SELECT => [
                'label' => 'Dropdown',
                'icon' => 'tabler--selector',
                'description' => 'Dropdown with searchable options',
            ],
            self::TYPE_FILE => [
                'label' => 'File Upload',
                'icon' => 'tabler--upload',
                'description' => 'File attachment field',
            ],
        ];
    }

    /**
     * Get the form that owns the field.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(WorkspaceTicketForm::class, 'form_id');
    }

    /**
     * Generate unique field name from label.
     */
    public static function generateFieldName(string $label): string
    {
        return 'custom_' . Str::slug($label, '_');
    }
}
