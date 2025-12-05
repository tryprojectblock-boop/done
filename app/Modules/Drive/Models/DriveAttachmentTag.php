<?php

declare(strict_types=1);

namespace App\Modules\Drive\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DriveAttachmentTag extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'color',
    ];

    public function attachments(): BelongsToMany
    {
        return $this->belongsToMany(
            DriveAttachment::class,
            'drive_attachment_tag',
            'drive_attachment_tag_id',
            'drive_attachment_id'
        );
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
