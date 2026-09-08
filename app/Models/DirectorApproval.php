<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectorApproval extends Model
{
    protected $fillable = [
        'report_id',
        'director_user_id',
        'decision',
        'remarks',
        'signature',
        'designation',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_user_id');
    }
}
