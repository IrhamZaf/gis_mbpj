<?php

namespace App\Services\Survey;

class SurveyDocumentClassifier
{
    public const TYPE_3D = 'survey_3d';
    public const TYPE_2D = 'survey_2d';
    public const TYPE_1D = 'survey_1d';
    public const TYPE_OTHER = 'other';

    public function classify(string $filename, ?string $mime = null, ?string $content = null): string
    {
        if ($content !== null && $content !== '') {
            $fromContent = $this->detectFromContent($content);
            if ($fromContent !== null) {
                return $fromContent;
            }
        }

        $name = strtoupper($filename);
        $ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (str_contains($name, '3DILAPXYZ')) {
            return self::TYPE_3D;
        }
        if (str_contains($name, '2DILAPXYZ')) {
            return self::TYPE_2D;
        }
        if (str_contains($name, '1DILAPXYZ')) {
            return self::TYPE_1D;
        }

        return $this->classifyByExtensionAndMime($ext, $mime);
    }

    public function detectFromContent(string $content): ?string
    {
        $sample = substr(ltrim($content), 0, 4096);

        if (str_starts_with($sample, '%PDF')) {
            return self::TYPE_1D;
        }

        $header = $this->firstRowColumns($sample);
        if ($header === []) {
            return null;
        }

        $upper = array_map('strtoupper', $header);

        $hasDay   = in_array('DAY', $upper, true);
        $hasPoint = in_array('POINT', $upper, true);
        $hasXb    = $this->hasColumn($upper, ['XB']);
        $hasYb    = $this->hasColumn($upper, ['YB']);
        $hasZb    = $this->hasColumn($upper, ['ZB']);
        $hasDxy   = $this->headerContains($header, 'DXY');

        if ($hasDay && $hasPoint && $hasXb && $hasYb) {
            return self::TYPE_2D;
        }

        if ($hasXb && $hasYb && $hasZb && ! $hasDay) {
            return self::TYPE_3D;
        }

        if ($hasXb && $hasYb && $hasDxy) {
            return self::TYPE_2D;
        }

        return null;
    }

    public function isSurveyType(string $type): bool
    {
        return in_array($type, [self::TYPE_3D, self::TYPE_2D, self::TYPE_1D], true);
    }

    public function requiresAnchor(string $type): bool
    {
        return in_array($type, [self::TYPE_3D, self::TYPE_2D], true);
    }

    private function classifyByExtensionAndMime(string $ext, ?string $mime): string
    {
        $mime = $mime ? strtolower($mime) : '';

        if ($ext === 'pdf' || str_contains($mime, 'pdf')) {
            return self::TYPE_1D;
        }

        if ($ext === 'csv' || str_contains($mime, 'csv')) {
            return self::TYPE_3D;
        }

        if ($ext === 'txt' || str_contains($mime, 'text/plain')) {
            return self::TYPE_2D;
        }

        return self::TYPE_OTHER;
    }

    /** @return array<int, string> */
    private function firstRowColumns(string $content): array
    {
        $line = strtok($content, "\r\n");
        if ($line === false || trim($line) === '') {
            return [];
        }

        return array_map('trim', str_getcsv($line));
    }

    /** @param  array<int, string>  $upperHeader */
    private function hasColumn(array $upperHeader, array $names): bool
    {
        foreach ($names as $name) {
            if (in_array($name, $upperHeader, true)) {
                return true;
            }
        }

        return false;
    }

    /** @param  array<int, string>  $header */
    private function headerContains(array $header, string $needle): bool
    {
        foreach ($header as $col) {
            if (stripos($col, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
