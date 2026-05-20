<?php

namespace App\Http\Controllers;

use App\Models\ReportAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Download the attachment with its original file name.
     */
    public function download(ReportAttachment $attachment)
    {
        $path = $attachment->file_path;
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Fail tidak dijumpai.');
        }

        return Storage::disk('public')->download($path, $attachment->file_name);
    }

    /**
     * View the attachment inline with its original file name.
     */
    public function view(ReportAttachment $attachment)
    {
        $path = $attachment->file_path;
        
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Fail tidak dijumpai.');
        }

        $mimeType = Storage::disk('public')->mimeType($path);

        // Serve the file inline so it opens in the browser instead of downloading immediately.
        return response()->make(Storage::disk('public')->get($path), 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . str_replace('"', '\"', $attachment->file_name) . '"'
        ]);
    }
}
