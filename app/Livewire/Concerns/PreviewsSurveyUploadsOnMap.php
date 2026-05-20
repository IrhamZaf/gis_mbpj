<?php

namespace App\Livewire\Concerns;

use App\Services\Survey\SurveyAttachmentProcessor;
use Illuminate\Http\UploadedFile;

trait PreviewsSurveyUploadsOnMap
{
    /** Lightweight metadata only — keeps Livewire payloads small so submit works. */
    public array $mapPreviewLayers = [];

    /**
     * @param  array<string, mixed>  $layer
     * @return array<string, mixed>
     */
    protected function layerMeta(array $layer): array
    {
        return [
            'file_name'     => $layer['file_name'] ?? '',
            'document_type' => $layer['document_type'] ?? '',
            'parse_status'  => $layer['parse_status'] ?? '',
            'parse_message' => $layer['parse_message'] ?? null,
        ];
    }

    /**
     * @param  array<int, UploadedFile|mixed>  $files
     * @param  array<int, array<string, mixed>>  $existingLayers
     */
    protected function refreshMapPreviewLayers(array $files, array $existingLayers = []): void
    {
        $processor = app(SurveyAttachmentProcessor::class);
        [$lat, $lng] = $this->resolvedReportAnchor($this->latitude, $this->longitude);

        $layersForMap = $existingLayers;
        $this->mapPreviewLayers = array_map(fn (array $layer) => $this->layerMeta($layer), $existingLayers);

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $full = $processor->preview($file, $lat, $lng);
                $layersForMap[] = $full;
                $this->mapPreviewLayers[] = $this->layerMeta($full);
            }
        }

        $this->dispatch('preview-survey-layers', layers: $layersForMap);
    }
}
