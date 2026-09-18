<?php

namespace App\Http\Controllers;

use App\Models\ReportAttachment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Download the attachment with its original file name.
     */
    public function download(ReportAttachment $attachment)
    {
        $attachment->loadMissing('report');
        Gate::authorize('view', $attachment->report);

        $path = $attachment->file_path;

        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'Fail tidak dijumpai.');
        }

        return Storage::disk('public')->download($path, $attachment->file_name);
    }

    /**
     * View the attachment inline with its original file name.
     */
    public function view(ReportAttachment $attachment)
    {
        $attachment->loadMissing('report');
        Gate::authorize('view', $attachment->report);

        $path = $attachment->file_path;

        if (! Storage::disk('public')->exists($path)) {
            abort(404, 'Fail tidak dijumpai.');
        }

        $mimeType = Storage::disk('public')->mimeType($path);

        return response()->make(Storage::disk('public')->get($path), 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '\"', $attachment->file_name).'"',
        ]);
    }
}
