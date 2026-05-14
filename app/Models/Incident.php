<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    public const CATEGORY_SINKHOLE = 'sinkhole';

    public const CATEGORY_SLOPE = 'slope';

    public const RISK_SAFE = 'selamat';

    public const RISK_MONITOR = 'pemantauan';

    public const RISK_CRITICAL = 'kritikal';

    protected $fillable = [
        'incident_number',
        'category',
        'date_reported',
        'latitude',
        'longitude',
        'address',
        'risk_level',
        'status',
        'description',
        'reported_by',
        'assigned_engineer',
    ];

    protected function casts(): array
    {
        return [
            'date_reported' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Incident $incident) {
            if ($incident->incident_number) {
                return;
            }
            $prefix = $incident->category === self::CATEGORY_SLOPE ? 'CR' : 'SH';
            $year = $incident->date_reported
                ? (\is_string($incident->date_reported)
                    ? substr($incident->date_reported, 0, 4)
                    : $incident->date_reported->format('Y'))
                : date('Y');
            $pattern = "{$prefix}-{$year}-";
            $last = static::query()
                ->where('incident_number', 'like', $pattern.'%')
                ->orderByDesc('incident_number')
                ->value('incident_number');
            $next = 1;
            if ($last && preg_match('/-(\d+)$/', $last, $m)) {
                $next = (int) $m[1] + 1;
            }
            $incident->incident_number = $pattern.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_engineer');
    }

    public function media(): HasMany
    {
        return $this->hasMany(IncidentMedia::class);
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(SurveyData::class);
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(IncidentTimeline::class)->orderByDesc('created_at');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(IncidentReview::class)->orderByDesc('created_at');
    }

    public function heatIntensity(): float
    {
        return match ($this->risk_level) {
            self::RISK_CRITICAL => 1.0,
            self::RISK_MONITOR => 0.55,
            default => 0.2,
        };
    }
}
