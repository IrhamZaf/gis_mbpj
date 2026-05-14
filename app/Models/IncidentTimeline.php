<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentTimeline extends Model
{
    protected $table = 'incident_timeline';

    protected $fillable = [
        'incident_id',
        'action',
        'description',
        'performed_by',
        'status_from',
        'status_to',
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
