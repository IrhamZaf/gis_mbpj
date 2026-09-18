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

    public function getDisplayNameAttribute(): string
    {
        return match ($this->code) {
            'SINKHOLE' => __('app.sinkhole'),
            'CERUN', 'CERUN_RUNTUH' => __('app.cerun'),
            'BOREHOLE' => __('app.borehole'),
            default => $this->name,
        };
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    public function casePrefix(): string
    {
        $unitCode = $this->relationLoaded('unit')
            ? ($this->unit?->code)
            : ($this->unit_id ? $this->unit()->value('code') : null);

        $catCode = $this->code === 'CERUN_RUNTUH' ? 'CERUN' : $this->code;

        // Preserve legacy Saliran & Cerun case number prefixes
        if ($unitCode === 'SAL-CERUN') {
            return match ($catCode) {
                'SINKHOLE' => 'SC',
                'CERUN' => 'CR',
                'BOREHOLE' => 'SB',
                default => 'CS',
            };
        }

        $unitPrefix = match ($unitCode) {
            'JLN' => 'JL',
            'STR' => 'ST',
            'ME' => 'ME',
            default => 'CS',
        };

        $catSuffix = match ($catCode) {
            'SINKHOLE' => 'SH',
            'CERUN' => 'CR',
            'BOREHOLE' => 'BH',
            default => '',
        };

        return $unitPrefix.$catSuffix;
    }

    /**
     * Ensure category belongs to the given unit (for report validation).
     */
    public function belongsToUnit(int $unitId): bool
    {
        return (int) $this->unit_id === (int) $unitId;
    }
}
