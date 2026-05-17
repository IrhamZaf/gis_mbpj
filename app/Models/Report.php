<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Report extends Model
{
    protected $fillable = [
        'report_number',
        'category_id',
        'user_id',
        'title',
        'description',
        'status',
        'latitude',
        'longitude',
        'location_name',
        'gis_data',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'gis_data'     => 'array',
            'latitude'     => 'decimal:7',
            'longitude'    => 'decimal:7',
            'submitted_at' => 'datetime',
        ];
    }

    // ── Auto-generate report number ─────────────────────
    protected static function booted(): void
    {
        static::creating(function ($report) {
            if (empty($report->report_number)) {
                $date  = Carbon::now()->format('Ymd');
                $count = static::whereDate('created_at', Carbon::today())->count() + 1;
                $report->report_number = 'RPT-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // ── Relationships ───────────────────────────────────
    public function category()
    {
        return $this->belongsTo(ReportCategory::class, 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(ReportAttachment::class);
    }

    // ── Scopes ──────────────────────────────────────────
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // ── Helpers ─────────────────────────────────────────
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft'     => '<span class="badge bg-label-warning">Draf</span>',
            'submitted' => '<span class="badge bg-label-success">Dihantar</span>',
            default     => '<span class="badge bg-label-secondary">' . ucfirst($this->status) . '</span>',
        };
    }
}
