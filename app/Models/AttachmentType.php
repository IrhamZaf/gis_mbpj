<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttachmentType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'required',
        'allowed_extensions',
        'max_size',
    ];

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'allowed_extensions' => 'array',
            'max_size' => 'integer',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ReportCategory::class, 'category_attachment_types', 'attachment_type_id', 'category_id')
            ->withPivot(['display_name', 'sort_order'])
            ->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ReportAttachment::class);
    }
}
