<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportAttachment extends Model
{
    protected $fillable = [
        'report_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public function getFileIconAttribute(): string
    {
        return match (true) {
            str_contains($this->file_type, 'pdf')   => 'ti tabler-file-type-pdf text-danger',
            str_contains($this->file_type, 'image') => 'ti tabler-photo text-info',
            str_contains($this->file_type, 'excel'),
            str_contains($this->file_type, 'spreadsheet') => 'ti tabler-file-spreadsheet text-success',
            str_contains($this->file_type, 'text')  => 'ti tabler-file-text text-secondary',
            default                                   => 'ti tabler-file text-primary',
        };
    }
}
