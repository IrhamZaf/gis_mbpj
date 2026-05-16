<?php

namespace App\Support;

use App\Models\SurveyUpload;

/**
 * Fail yang boleh dibuka dalam tab pelayar (inline) — PDF, imej ringkas, teks.
 */
final class SurveyUploadBrowserPreview
{
    public static function isPreviewable(SurveyUpload $upload): bool
    {
        $filename = $upload->original_name ?: basename($upload->file_path);
        $mime = strtolower(trim(explode(';', (string) ($upload->mime_type ?? ''))[0]));

        return self::matches($mime, $filename);
    }

    public static function matches(string $mime, string $filename): bool
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($ext === 'pdf' && ($mime === '' || $mime === 'application/octet-stream')) {
            return true;
        }

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)
            && (str_starts_with($mime, 'image/') || $mime === '' || $mime === 'application/octet-stream')) {
            return true;
        }

        if ($ext === 'txt' && ($mime === 'text/plain' || $mime === '')) {
            return true;
        }

        if ($mime === 'application/pdf') {
            return true;
        }

        if (str_starts_with($mime, 'image/') && $mime !== 'image/svg+xml') {
            return true;
        }

        if ($mime === 'text/plain') {
            return true;
        }

        return false;
    }
}
