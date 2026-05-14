<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentMedia extends Model
{
    protected $table = 'incident_media';

    protected $fillable = [
        'incident_id',
        'type',
        'file_path',
        'caption',
        'upload_phase',
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }
}
