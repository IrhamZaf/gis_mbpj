<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SiteVisitPhoto extends Model
{
    protected $fillable = [
        'site_visit_id',
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'caption',
        'latitude',
        'longitude',
        'taken_at',
    ];

    protected function casts(): array
    {
        return [
            'taken_at'  => 'datetime',
            'latitude'  => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function siteVisit(): BelongsTo
    {
        return $this->belongsTo(SiteVisit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
