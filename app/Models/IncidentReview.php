<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentReview extends Model
{
    protected $fillable = [
        'incident_id',
        'engineer_id',
        'risk_assessment',
        'recommendation',
        'is_approved',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
        ];
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'engineer_id');
    }
}
