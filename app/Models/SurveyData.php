<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyData extends Model
{
    public const REVIEW_PENDING = 'pending_engineer_review';

    public const REVIEW_APPROVED = 'approved_by_engineer';

    public const REVIEW_REJECTED = 'rejected_by_engineer';

    protected $table = 'survey_data';

    protected $fillable = [
        'incident_id',
        'surveyor_id',
        'vendor_name',
        'surveyor_name',
        'survey_date',
        'survey_type',
        'gps_coordinates',
        'geojson_data',
        'gis_metadata',
        'converted_coordinates',
        'notes',
        'technical_notes',
        'original_filename',
        'review_status',
        'version',
        'parent_survey_id',
    ];

    protected function casts(): array
    {
        return [
            'survey_date' => 'date',
            'gps_coordinates' => 'array',
            'geojson_data' => 'array',
            'gis_metadata' => 'array',
            'converted_coordinates' => 'array',
        ];
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function surveyor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surveyor_id');
    }

    public function parentSurvey(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_survey_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_survey_id')->orderBy('version');
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(SurveyUpload::class, 'survey_data_id')->orderBy('id');
    }

    public function reviewStatusLabel(): string
    {
        return match ($this->review_status) {
            self::REVIEW_APPROVED => 'Diluluskan jurutera',
            self::REVIEW_REJECTED => 'Ditolak jurutera',
            default => 'Menunggu semakan jurutera',
        };
    }
}
