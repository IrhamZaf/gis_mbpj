<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyData extends Model
{
    protected $table = 'survey_data';

    protected $fillable = [
        'incident_id',
        'surveyor_id',
        'survey_date',
        'gps_coordinates',
        'geojson_data',
        'notes',
        'original_filename',
    ];

    protected function casts(): array
    {
        return [
            'survey_date' => 'date',
            'gps_coordinates' => 'array',
            'geojson_data' => 'array',
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
}
