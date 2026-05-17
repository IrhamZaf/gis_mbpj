<?php

namespace App\Services\Survey;

class SurveyReportMetadataExtractor
{
    public const CATEGORY_CERUN = 'cerun-tanah-runtuh';

    public const CATEGORY_SINKHOLE = 'sinkhole';

    public function __construct(
        private SurveyDocumentClassifier $classifier,
    ) {}

    /**
     * @return array{
     *   title: ?string,
     *   description: ?string,
     *   location_name: ?string,
     *   site_code: ?string,
     *   place_name: ?string,
     *   survey_dimension: ?string,
     *   category_slug: ?string,
     *   source_filename: ?string
     * }
     */
    public function extract(string $filename, ?string $content = null, ?string $mime = null): array
    {
        $documentType = $this->classifier->classify($filename, $mime, $content);
        $fromFilename   = $this->extractFromFilename($filename, $documentType);
        $fromContent    = $content ? $this->extractFromContent($content, $documentType) : [];

        $merged = $this->merge([], $fromFilename, $fromContent, [
            'source_filename' => $filename,
        ]);

        $merged['site_code'] ??= $this->guessSiteCodeFromFilename($filename);
        $merged['place_name'] ??= $this->guessPlaceFromFilename($filename);
        $merged['category_slug'] = $this->categorySlugFromSiteCode($merged['site_code'] ?? null);

        if (empty($merged['location_name']) && ! empty($merged['place_name'])) {
            $merged['location_name'] = $merged['place_name'];
        }

        return $merged;
    }

    public function guessPlaceFromFilename(string $filename): ?string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);

        if (preg_match('/\b(ATC\d+[A-Z]?)\b/i', $base, $m)) {
            return strtoupper($m[1]);
        }

        return null;
    }

    public function categorySlugFromSiteCode(?string $siteCode): ?string
    {
        if ($siteCode === null || $siteCode === '') {
            return null;
        }

        $upper = strtoupper($siteCode);

        if (str_starts_with($upper, 'CN')) {
            return self::CATEGORY_CERUN;
        }

        if (str_starts_with($upper, 'SH')) {
            return self::CATEGORY_SINKHOLE;
        }

        return null;
    }

    public function guessSiteCodeFromFilename(string $filename): ?string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);

        if (preg_match('/\b(CN\d*[A-Z0-9]*)\b/i', $base, $m)) {
            return strtoupper($m[1]);
        }

        if (preg_match('/\b(SH\d*[A-Z0-9]*)\b/i', $base, $m)) {
            return strtoupper($m[1]);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  ...$overrides
     * @return array<string, mixed>
     */
    public function merge(array $base, array ...$overrides): array
    {
        $merged = $base;

        foreach ($overrides as $patch) {
            foreach ($patch as $key => $value) {
                if ($value !== null && $value !== '') {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractFromFilename(string $filename, string $documentType): array
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $meta = [
            'site_code'        => null,
            'place_name'       => null,
            'survey_dimension' => $this->dimensionLabel($documentType),
        ];

        if (preg_match('/^([A-Z0-9]+)[\s_]+([A-Z0-9]+)[\s_]+[123]DILAPXYZ/i', $base, $m)) {
            $first  = strtoupper($m[1]);
            $second = strtoupper($m[2]);

            if ($this->isProjectCode($first)) {
                $meta['site_code']  = $first;
                $meta['place_name'] = $second;
            } elseif ($this->isPlaceName($first)) {
                $meta['place_name'] = $first;
                if ($this->isProjectCode($second)) {
                    $meta['site_code'] = $second;
                }
            }
        } elseif (preg_match('/^([A-Z0-9]+)[\s_]+[123]DILAPXYZ/i', $base, $m)) {
            $token = strtoupper($m[1]);
            if ($this->isProjectCode($token)) {
                $meta['site_code'] = $token;
            } elseif ($this->isPlaceName($token)) {
                $meta['place_name'] = $token;
            }
        }

        $place = $meta['place_name'];
        $site  = $meta['site_code'];
        $dim   = $meta['survey_dimension'];

        if ($place !== null) {
            $meta['location_name'] = $place;
            $meta['title']         = $this->defaultTitle($place, $dim);
            $meta['description']   = $this->defaultDescription($place, $dim, $filename, $site);
        } elseif ($site !== null && $dim !== null) {
            $meta['title']       = $this->defaultTitle($site, $dim);
            $meta['description'] = $this->defaultDescription($site, $dim, $filename, null);
        } elseif ($dim !== null) {
            $clean = preg_replace('/\s+/', ' ', trim(str_replace(['_', '-'], ' ', $base))) ?? $base;
            $meta['title']       = 'Laporan Survei ' . $dim . ' — ' . $clean;
            $meta['description'] = 'Data survei ' . $dim . ' daripada fail ' . $filename . '.';
        }

        if (empty($meta['site_code'])) {
            $meta['site_code'] = $this->guessSiteCodeFromFilename($filename);
        }

        if (empty($meta['place_name'])) {
            $meta['place_name'] = $this->guessPlaceFromFilename($filename);
            if ($meta['place_name'] && empty($meta['location_name'])) {
                $meta['location_name'] = $meta['place_name'];
            }
        }

        $meta['category_slug'] = $this->categorySlugFromSiteCode($meta['site_code']);

        return $meta;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractFromContent(string $content, string $documentType): array
    {
        $meta = [];

        if (str_starts_with(ltrim($content), '%PDF')) {
            return $this->extractFromPdfStrings($content, $documentType);
        }

        foreach ($this->preambleLines($content) as $line) {
            $this->applyLineMetadata($meta, $line);
        }

        if ($meta !== [] && empty($meta['description']) && $documentType !== SurveyDocumentClassifier::TYPE_OTHER) {
            $dim = $this->dimensionLabel($documentType);
            if ($dim) {
                $place = $meta['place_name'] ?? $meta['location_name'] ?? 'lokasi survei';
                $meta['description'] = $this->defaultDescription($place, $dim, 'fail', $meta['site_code'] ?? null);
            }
        }

        return $meta;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractFromPdfStrings(string $content, string $documentType): array
    {
        $meta = [];
        preg_match_all('/[\x20-\x7E]{4,}/', substr($content, 0, 131072), $matches);
        $text = implode("\n", $matches[0] ?? []);

        $patterns = [
            'title'         => '/(?:tajuk\s*laporan|report\s*title|project\s*name|project)\s*[:=]\s*([^\r\n]{5,200})/i',
            'description'   => '/(?:keterangan|description|remarks|catatan)\s*[:=]\s*([^\r\n]{10,500})/i',
            'location_name' => '/(?:nama\s*lokasi|location\s*name|site\s*name|site|lokasi)\s*[:=]\s*([^\r\n]{3,200})/i',
            'site_code'     => '/(?:site\s*code|kod\s*tapak)\s*[:=]\s*([A-Z0-9]{2,12})/i',
            'place_name'    => '/(?:tempat|place|lokasi\s*tapak)\s*[:=]\s*([A-Z0-9]{2,12})/i',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $meta[$key] = trim($m[1]);
            }
        }

        if (empty($meta['title']) && preg_match('/1DILAPXYZ/i', $text)) {
            $meta['survey_dimension'] = '1D';
        }

        if (empty($meta['description']) && $documentType === SurveyDocumentClassifier::TYPE_1D) {
            $place = $meta['place_name'] ?? $meta['location_name'] ?? 'lokasi survei';
            $meta['description'] = $this->defaultDescription($place, '1D', 'laporan PDF', $meta['site_code'] ?? null);
        }

        return $meta;
    }

    /** @return array<int, string> */
    private function preambleLines(string $content): array
    {
        $lines = preg_split('/\r\n|\n|\r/', $content) ?: [];
        $preamble = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }
            if ($this->isDataHeaderLine($trimmed)) {
                break;
            }
            $preamble[] = $trimmed;
            if (count($preamble) >= 40) {
                break;
            }
        }

        return $preamble;
    }

    private function isDataHeaderLine(string $line): bool
    {
        $upper = strtoupper($line);

        return (str_contains($upper, 'XB') && str_contains($upper, 'YB'))
            || (str_contains($upper, 'DAY') && str_contains($upper, 'POINT'));
    }

    /** @param  array<string, mixed>  $meta */
    private function applyLineMetadata(array &$meta, string $line): void
    {
        if (preg_match('/^(?:#\s*)?(tajuk\s*laporan|title|project|report)\s*[:=]\s*(.+)$/iu', $line, $m)) {
            $meta['title'] = trim($m[2], " \t\"'");

            return;
        }

        if (preg_match('/^(?:#\s*)?(keterangan|description|remarks|catatan)\s*[:=]\s*(.+)$/iu', $line, $m)) {
            $meta['description'] = trim($m[2], " \t\"'");

            return;
        }

        if (preg_match('/^(?:#\s*)?(nama\s*lokasi|location|lokasi|site)\s*[:=]\s*(.+)$/iu', $line, $m)) {
            $meta['location_name'] = trim($m[2], " \t\"'");

            return;
        }

        if (preg_match('/^(site|kod\s*tapak)\s*[,;=]\s*([A-Z0-9]+)/iu', $line, $m)) {
            $meta['site_code'] = strtoupper($m[2]);

            return;
        }

        if (preg_match('/^(?:tempat|place|lokasi\s*tapak)\s*[,;=]\s*([A-Z0-9]+)/iu', $line, $m)) {
            $meta['place_name'] = strtoupper($m[1]);

            return;
        }

        $cols = str_getcsv($line);
        if (count($cols) === 2) {
            $key = strtolower(trim($cols[0]));
            $val = trim($cols[1], " \t\"'");
            $map = [
                'tajuk' => 'title', 'title' => 'title', 'project' => 'title',
                'keterangan' => 'description', 'description' => 'description', 'remarks' => 'description',
                'lokasi' => 'location_name', 'location' => 'location_name', 'nama lokasi' => 'location_name', 'site' => 'location_name',
                'site_code' => 'site_code', 'tempat' => 'place_name', 'place' => 'place_name',
            ];
            if (isset($map[$key]) && $val !== '') {
                $meta[$map[$key]] = in_array($key, ['site_code', 'tempat', 'place'], true) ? strtoupper($val) : $val;
            }
        }
    }

    private function dimensionLabel(string $documentType): ?string
    {
        return match ($documentType) {
            SurveyDocumentClassifier::TYPE_3D => '3D',
            SurveyDocumentClassifier::TYPE_2D => '2D',
            SurveyDocumentClassifier::TYPE_1D => '1D',
            default => null,
        };
    }

    private function isProjectCode(string $code): bool
    {
        return (bool) preg_match('/^(CN|SH)\d*/i', $code);
    }

    private function isPlaceName(string $code): bool
    {
        return (bool) preg_match('/^ATC\d+[A-Z]?$/i', $code);
    }

    private function defaultTitle(string $place, ?string $dim): string
    {
        $label = $dim ? "Survei {$dim} ILAPXYZ" : 'Survei ILAPXYZ';

        return "{$label} — {$place}";
    }

    private function defaultDescription(string $place, ?string $dim, string $source, ?string $projectCode = null): string
    {
        $proj = $projectCode ? " (projek {$projectCode})" : '';

        return match ($dim) {
            '3D' => "Data titik survei 3D (Xb, Yb, Zb) untuk lokasi {$place}{$proj}. Sumber: {$source}.",
            '2D' => "Data pemantauan sesaran 2D mengikut hari (DXY/DZ) untuk lokasi {$place}{$proj}. Sumber: {$source}.",
            '1D' => "Laporan graf dan analisis 1D untuk lokasi {$place}{$proj}. Sumber: {$source}.",
            default => "Dokumen survei untuk lokasi {$place}{$proj}. Sumber: {$source}.",
        };
    }
}
