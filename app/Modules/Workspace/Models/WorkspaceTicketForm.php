<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WorkspaceTicketForm extends Model
{
    public const STANDARD_FIELDS = [
        'name' => ['label' => 'Name', 'icon' => 'tabler--user'],
        'email' => ['label' => 'Email', 'icon' => 'tabler--mail'],
        'phone' => ['label' => 'Phone', 'icon' => 'tabler--phone'],
        'subject' => ['label' => 'Subject', 'icon' => 'tabler--heading'],
        'description' => ['label' => 'Description', 'icon' => 'tabler--align-left'],
        'department' => ['label' => 'Department', 'icon' => 'tabler--building'],
        'priority' => ['label' => 'Priority', 'icon' => 'tabler--flag'],
        'attachments' => ['label' => 'Attachments', 'icon' => 'tabler--paperclip'],
    ];

    protected $fillable = [
        'workspace_id',
        'name',
        'slug',
        'description',
        'success_message',
        'submit_button_text',
        'is_active',
        'show_name',
        'name_required',
        'show_email',
        'email_required',
        'show_phone',
        'phone_required',
        'show_subject',
        'subject_required',
        'show_description',
        'description_required',
        'show_department',
        'department_required',
        'show_priority',
        'priority_required',
        'show_attachments',
        'default_department_id',
        'default_priority_id',
        'logo_url',
        'primary_color',
        'background_color',
        'enable_captcha',
        'enable_honeypot',
        'field_order',
        // Confirmation settings
        'confirmation_type',
        'confirmation_headline',
        'confirmation_message',
        'redirect_url',
        // Spam protection
        'enable_rate_limiting',
        'rate_limit_per_hour',
        'block_disposable_emails',
        'blocked_emails',
        'blocked_domains',
        'blocked_ips',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'published_at' => 'datetime',
            'show_name' => 'boolean',
            'name_required' => 'boolean',
            'show_email' => 'boolean',
            'email_required' => 'boolean',
            'show_phone' => 'boolean',
            'phone_required' => 'boolean',
            'show_subject' => 'boolean',
            'subject_required' => 'boolean',
            'show_description' => 'boolean',
            'description_required' => 'boolean',
            'show_department' => 'boolean',
            'department_required' => 'boolean',
            'show_priority' => 'boolean',
            'priority_required' => 'boolean',
            'show_attachments' => 'boolean',
            'enable_captcha' => 'boolean',
            'enable_honeypot' => 'boolean',
            'field_order' => 'array',
            'enable_rate_limiting' => 'boolean',
            'block_disposable_emails' => 'boolean',
        ];
    }

    /**
     * Get the default field order (all standard fields + custom fields).
     */
    public function getDefaultFieldOrder(): array
    {
        $order = array_keys(self::STANDARD_FIELDS);
        foreach ($this->fields as $field) {
            $order[] = 'custom_' . $field->id;
        }
        return $order;
    }

    /**
     * Get ordered fields for display.
     * Returns array of field data with type (standard/custom) and properties.
     */
    public function getOrderedFields(): array
    {
        $order = $this->field_order ?? $this->getDefaultFieldOrder();
        $customFields = $this->fields->keyBy(fn($f) => 'custom_' . $f->id);
        $orderedFields = [];

        foreach ($order as $fieldKey) {
            if (str_starts_with($fieldKey, 'custom_')) {
                if (isset($customFields[$fieldKey])) {
                    $field = $customFields[$fieldKey];
                    $orderedFields[] = [
                        'key' => $fieldKey,
                        'type' => 'custom',
                        'field' => $field,
                    ];
                    $customFields->forget($fieldKey);
                }
            } elseif (isset(self::STANDARD_FIELDS[$fieldKey])) {
                $orderedFields[] = [
                    'key' => $fieldKey,
                    'type' => 'standard',
                    'label' => self::STANDARD_FIELDS[$fieldKey]['label'],
                    'icon' => self::STANDARD_FIELDS[$fieldKey]['icon'],
                ];
            }
        }

        // Add any new custom fields not in order
        foreach ($customFields as $key => $field) {
            $orderedFields[] = [
                'key' => $key,
                'type' => 'custom',
                'field' => $field,
            ];
        }

        return $orderedFields;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->workspace_id);
            }
        });
    }

    /**
     * Generate a unique slug for the form.
     */
    public static function generateUniqueSlug(int $workspaceId): string
    {
        do {
            $slug = Str::random(12);
        } while (static::where('slug', $slug)->exists());

        return $slug;
    }

    /**
     * Get the workspace that owns the form.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the default department.
     */
    public function defaultDepartment(): BelongsTo
    {
        return $this->belongsTo(WorkspaceDepartment::class, 'default_department_id');
    }

    /**
     * Get the default priority.
     */
    public function defaultPriority(): BelongsTo
    {
        return $this->belongsTo(WorkspacePriority::class, 'default_priority_id');
    }

    /**
     * Get custom form fields.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(WorkspaceTicketFormField::class, 'form_id')->orderBy('sort_order');
    }

    /**
     * Get the public URL for this form.
     */
    public function getPublicUrlAttribute(): string
    {
        return route('public.ticket-form', $this->slug);
    }

    /**
     * Get the iframe embed code.
     */
    public function getIframeEmbedCode(): string
    {
        $url = $this->public_url;
        return '<iframe src="' . $url . '" width="100%" height="600" frameborder="0" style="border: none;"></iframe>';
    }

    /**
     * Get the JavaScript embed code for modal/popup.
     */
    public function getJsEmbedCode(): string
    {
        $url = $this->public_url;
        $slug = $this->slug;
        return <<<HTML
<script src="{$url}/embed.js" data-form-id="{$slug}"></script>
<button onclick="openTicketForm_{$slug}()">Contact Support</button>
HTML;
    }

    /**
     * Get the direct link embed code.
     */
    public function getLinkEmbedCode(): string
    {
        return '<a href="' . $this->public_url . '" target="_blank">Submit a Ticket</a>';
    }

    /**
     * Check if the form is published (locked).
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }
}
