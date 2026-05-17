<?php

namespace App\Livewire\Concerns;

use App\Models\ReportCategory;
use App\Services\Survey\SurveyReportMetadataExtractor;
use Illuminate\Http\UploadedFile;

trait AppliesSurveyMetadata
{
    public bool $surveyMetadataApplied = false;

    public function applySurveyMetadata(
        ?string $title = null,
        ?string $description = null,
        ?string $locationName = null,
        ?string $categorySlug = null,
    ): void {
        $filled = false;

        if ($title && strlen(trim($this->title ?? '')) < 5) {
            $this->title = mb_substr(trim($title), 0, 255);
            $filled      = true;
        }

        if ($description && strlen(trim($this->description ?? '')) < 10) {
            $this->description = mb_substr(trim($description), 0, 5000);
            $filled            = true;
        }

        if ($locationName && trim($this->location_name ?? '') === '') {
            $this->location_name = mb_substr(trim($locationName), 0, 255);
            $filled              = true;
        }

        if ($categorySlug && (int) ($this->category_id ?? 0) === 0) {
            $categoryId = ReportCategory::where('slug', $categorySlug)->value('id');
            if ($categoryId) {
                $this->category_id = (int) $categoryId;
                $filled            = true;
            }
        }

        if ($filled) {
            $this->surveyMetadataApplied = true;
        }
    }

    /** @param  array<int, UploadedFile|mixed>  $files */
    protected function applyMetadataFromUploadedFiles(array $files): void
    {
        $extractor = app(SurveyReportMetadataExtractor::class);
        $merged    = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->getRealPath();
            if (! $path || ! is_readable($path)) {
                continue;
            }

            $sample = (string) file_get_contents($path, false, null, 0, 65536);
            $meta   = $extractor->extract(
                $file->getClientOriginalName(),
                $sample,
                $file->getMimeType()
            );
            $merged = $extractor->merge($merged, $meta);
        }

        if ($merged === []) {
            return;
        }

        $this->applySurveyMetadata(
            $merged['title'] ?? null,
            $merged['description'] ?? null,
            $merged['location_name'] ?? null,
            $merged['category_slug'] ?? null,
        );
    }
}
