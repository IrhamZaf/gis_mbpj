<?php

namespace App\Http\Controllers\Concerns;

use App\Models\SurveyUpload;
use App\Support\SurveyUploadBrowserPreview;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait ServesSurveyUploadFiles
{
    protected function surveyUploadInlineResponse(SurveyUpload $upload): BinaryFileResponse
    {
        if (! Storage::disk('public')->exists($upload->file_path)) {
            abort(404);
        }

        $absolute = Storage::disk('public')->path($upload->file_path);
        $mimeGuess = @mime_content_type($absolute);
        $mime = strtolower(trim((string) ($upload->mime_type ?: $mimeGuess ?: '')));
        $mime = explode(';', $mime)[0] ?? '';

        $displayName = $upload->original_name ?: basename($upload->file_path);

        if (! SurveyUploadBrowserPreview::matches($mime, $displayName)) {
            abort(415, 'Fail ini tidak boleh dipaparkan dalam pelayar. Gunakan muat turun.');
        }

        $lowerName = strtolower($displayName);
        $contentType = match (true) {
            str_ends_with($lowerName, '.pdf') || $mime === 'application/pdf' => 'application/pdf',
            str_starts_with($mime, 'image/') => $mime !== '' ? $mime : 'image/jpeg',
            $mime === 'text/plain' || str_ends_with($lowerName, '.txt') => 'text/plain; charset=UTF-8',
            default => 'application/octet-stream',
        };

        $response = new BinaryFileResponse($absolute, 200, [], true, ResponseHeaderBag::DISPOSITION_INLINE);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $displayName);
        $response->headers->set('Content-Type', $contentType);
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
