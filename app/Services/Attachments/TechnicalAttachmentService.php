<?php

namespace App\Services\Attachments;

use App\Models\AttachmentType;
use App\Models\Report;
use App\Models\ReportAttachment;
use App\Models\User;
use App\Models\WorkflowHistory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TechnicalAttachmentService
{
    public function upload(Report $report, AttachmentType $type, UploadedFile $file, User $user): ReportAttachment
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $allowed = array_map('strtolower', $type->allowed_extensions ?? []);
        if ($allowed && ! in_array($ext, $allowed, true)) {
            throw ValidationException::withMessages([
                'file' => 'Format .'.$ext.' tidak dibenarkan untuk '.$type->name.'. Dibenarkan: '.implode(', ', $allowed),
            ]);
        }

        $maxKb = (int) ($type->max_size ?: 51200);
        if ($file->getSize() > $maxKb * 1024) {
            throw ValidationException::withMessages([
                'file' => 'Saiz fail melebihi had '.$maxKb.' KB.',
            ]);
        }

        $current = ReportAttachment::where('report_id', $report->id)
            ->where('attachment_type_id', $type->id)
            ->where('is_current', true)
            ->first();

        $nextVersion = $current ? ((int) $current->version + 1) : 1;

        if ($current) {
            $current->update(['is_current' => false]);
        }

        $stored = Str::uuid()->toString().'.'.$ext;
        $path = $file->storeAs('reports/'.$report->id.'/technical', $stored, 'public');

        $attachment = ReportAttachment::create([
            'report_id' => $report->id,
            'attachment_type_id' => $type->id,
            'file_name' => $file->getClientOriginalName(),
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $stored,
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $user->id,
            'uploaded_at' => now(),
            'version' => $nextVersion,
            'is_current' => true,
            'document_type' => 'other',
            'parse_status' => 'skipped',
        ]);

        WorkflowHistory::create([
            'report_id' => $report->id,
            'user_id' => $user->id,
            'role' => $user->role,
            'action' => 'document_uploaded',
            'from_status' => $report->workflow_status,
            'to_status' => $report->workflow_status,
            'remarks' => $type->code.' v'.$nextVersion.' — '.$file->getClientOriginalName(),
            'ip_address' => request()->ip(),
        ]);

        return $attachment;
    }
}
