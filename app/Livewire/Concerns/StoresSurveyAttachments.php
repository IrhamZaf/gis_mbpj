<?php

namespace App\Livewire\Concerns;

use App\Models\Report;
use App\Models\ReportAttachment;
use App\Services\Survey\SurveyAttachmentProcessor;
use App\Services\Survey\SurveyDocumentClassifier;
use Illuminate\Http\UploadedFile;

trait StoresSurveyAttachments
{
    /** @return array{0: float, 1: float} */
    protected function resolvedReportAnchor(?float $latitude = null, ?float $longitude = null): array
    {
        return [
            $latitude ?? (float) config('gis.default_latitude'),
            $longitude ?? (float) config('gis.default_longitude'),
        ];
    }

    protected function validateSurveyFiles(array $files, ?float $latitude, ?float $longitude): void
    {
        $classifier = app(SurveyDocumentClassifier::class);
        $typesSeen = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $sample = @file_get_contents($file->getRealPath(), false, null, 0, 65536) ?: '';
            $type = $classifier->classify(
                $file->getClientOriginalName(),
                $file->getMimeType(),
                $sample
            );

            if ($classifier->isSurveyType($type)) {
                if (isset($typesSeen[$type])) {
                    session()->flash('warning', 'Lebih daripada satu fail jenis ' . $type . ' dimuat naik. Hanya yang terakhir akan dipaparkan pada peta.');
                }
                $typesSeen[$type] = true;
            }
        }
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    protected function storeAttachments(Report $report, array $files, ?float $latitude, ?float $longitude): void
    {
        $processor = app(SurveyAttachmentProcessor::class);

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('reports/' . $report->id, 'public');
            $meta = $processor->process($file, $path, $latitude, $longitude);

            ReportAttachment::create([
                'report_id'     => $report->id,
                'file_name'     => $file->getClientOriginalName(),
                'file_path'     => $path,
                'file_type'     => $file->getMimeType() ?? 'application/octet-stream',
                'file_size'     => $file->getSize(),
                'document_type' => $meta['document_type'],
                'parsed_data'   => $meta['parsed_data'],
                'parse_status'  => $meta['parse_status'],
                'parse_message' => $meta['parse_message'],
            ]);
        }
    }
}
