<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    protected $fillable = [
        'mailable_class',
        'subject',
        'to',
        'cc',
        'bcc',
        'from_address',
        'from_name',
        'html_body',
        'text_body',
        'headers',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'to' => 'array',
            'cc' => 'array',
            'bcc' => 'array',
            'headers' => 'array',
            'attachments' => 'array',
        ];
    }

    public function getToListAttribute(): string
    {
        if (empty($this->to)) {
            return '';
        }
        return collect($this->to)->map(fn($item) => $item['address'] ?? $item)->implode(', ');
    }
}