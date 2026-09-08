<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngineerVerification extends Model
{
    protected $fillable = [
        'report_id',
        'engineer_user_id',
        'remarks',
        'decision',
        'signature',
        'designation',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function engineer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'engineer_user_id');
    }
}
