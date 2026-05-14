<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GisLayer extends Model
{
    protected $fillable = [
        'name',
        'type',
        'file_path',
        'geojson_data',
        'metadata',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'geojson_data' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
