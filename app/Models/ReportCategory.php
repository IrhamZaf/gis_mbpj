<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportCategory extends Model
{
    protected $fillable = [
        'unit_id',
        'name',
        'slug',
        'code',
        'description',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'category_id');
    }

    public function attachmentTypes(): BelongsToMany
    {
        return $this->belongsToMany(AttachmentType::class, 'category_attachment_types', 'category_id', 'attachment_type_id')
            ->withPivot(['display_name', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUnit($query, int|string|null $unitId)
    {
        if ($unitId === null || $unitId === '') {
            return $query;
        }

        return $query->where('unit_id', $unitId);
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    public function casePrefix(): string
    {
        return match ($this->code) {
            'SINKHOLE' => 'SC',
            'CERUN_RUNTUH' => 'CR',
            default => 'CS',
        };
    }
}
