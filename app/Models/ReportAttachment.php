<?php

namespace App\Models;

use App\Services\Survey\SurveyDocumentClassifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ReportAttachment extends Model
{
    protected $fillable = [
        'report_id',
        'attachment_type_id',
        'file_name',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by',
        'uploaded_at',
        'version',
        'is_current',
        'document_type',
        'parsed_data',
        'parse_status',
        'parse_message',
    ];

    protected function casts(): array
    {
        return [
            'parsed_data' => 'array',
            'uploaded_at' => 'datetime',
            'is_current' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function attachmentType(): BelongsTo
    {
        return $this->belongsTo(AttachmentType::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function isSurvey3d(): bool
    {
        return $this->document_type === SurveyDocumentClassifier::TYPE_3D;
    }

    public function isSurvey2d(): bool
    {
        return $this->document_type === SurveyDocumentClassifier::TYPE_2D;
    }

    public function isSurvey1d(): bool
    {
        return $this->document_type === SurveyDocumentClassifier::TYPE_1D;
    }

    public function isSurvey(): bool
    {
        return in_array($this->document_type, [
            SurveyDocumentClassifier::TYPE_3D,
            SurveyDocumentClassifier::TYPE_2D,
            SurveyDocumentClassifier::TYPE_1D,
        ], true);
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        if ($this->attachmentType) {
            return $this->attachmentType->name;
        }

        return match ($this->document_type) {
            SurveyDocumentClassifier::TYPE_3D => '3D ILAPXYZ',
            SurveyDocumentClassifier::TYPE_2D => '2D ILAPXYZ',
            SurveyDocumentClassifier::TYPE_1D => '1D Laporan',
            default => 'Lain-lain',
        };
    }

    public function getDocumentTypeBadgeAttribute(): string
    {
        if ($this->attachmentType) {
            return '<span class="badge bg-label-primary">'.e($this->attachmentType->code).'</span>';
        }

        $label = match ($this->document_type) {
            SurveyDocumentClassifier::TYPE_3D => '3D',
            SurveyDocumentClassifier::TYPE_2D => '2D',
            SurveyDocumentClassifier::TYPE_1D => '1D',
            default => 'Fail',
        };
        $class = match ($this->document_type) {
            SurveyDocumentClassifier::TYPE_3D => 'bg-label-info',
            SurveyDocumentClassifier::TYPE_2D => 'bg-label-warning',
            SurveyDocumentClassifier::TYPE_1D => 'bg-label-danger',
            default => 'bg-label-secondary',
        };

        return '<span class="badge '.$class.'">'.$label.'</span>';
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }

    public function getFileIconAttribute(): string
    {
        return match (true) {
            $this->isSurvey1d() || str_contains((string) $this->file_type, 'pdf') => 'ti tabler-file-type-pdf text-danger',
            $this->isSurvey3d() || str_contains((string) $this->file_type, 'csv') => 'ti tabler-file-spreadsheet text-success',
            $this->isSurvey2d() || str_contains((string) $this->file_type, 'text') => 'ti tabler-file-text text-warning',
            str_contains((string) $this->file_type, 'image') => 'ti tabler-photo text-info',
            default => 'ti tabler-file text-primary',
        };
    }
}
