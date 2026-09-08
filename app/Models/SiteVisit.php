<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteVisit extends Model
{
    protected $fillable = [
        'report_id',
        'ta_user_id',
        'file_number',
        'reference',
        'visit_date',
        'visit_time',
        'latitude',
        'longitude',
        'gps_accuracy',
        'laporan_pj_pjk',
        'visit_notes',
        'ta_designation',
        'ta_signature',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'visit_date'   => 'date',
            'submitted_at' => 'datetime',
            'latitude'     => 'decimal:7',
            'longitude'    => 'decimal:7',
            'gps_accuracy' => 'decimal:2',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function ta(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ta_user_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(SiteVisitPhoto::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }
}
