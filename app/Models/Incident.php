<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    public const CATEGORY_SINKHOLE = 'sinkhole';

    public const CATEGORY_SLOPE = 'slope';

    /** Kod laporan cerun: CN1, CN2, … */
    public const CODE_PREFIX_SLOPE = 'CN';

    /** Kod laporan sinkhole: SH1, SH2, … */
    public const CODE_PREFIX_SINKHOLE = 'SH';

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
            $prefix = $incident->category === self::CATEGORY_SLOPE
                ? self::CODE_PREFIX_SLOPE
                : self::CODE_PREFIX_SINKHOLE;

            // Terima CN1, CN1-ATC5A, dll. — ambil digit selepas prefix untuk urutan.
            $regex = '/^'.preg_quote($prefix, '/').'(\d+)/';

            $maxSeq = 0;
            $candidates = static::query()
                ->where('category', $incident->category)
                ->where('incident_number', 'like', $prefix.'%')
                ->pluck('incident_number');

            foreach ($candidates as $num) {
                if (preg_match($regex, (string) $num, $m)) {
                    $maxSeq = max($maxSeq, (int) $m[1]);
                }
            }

            $incident->incident_number = $prefix.((string) ($maxSeq + 1));
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
        return $this->hasMany(SurveyData::class)->orderByDesc('version')->orderByDesc('id');
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
