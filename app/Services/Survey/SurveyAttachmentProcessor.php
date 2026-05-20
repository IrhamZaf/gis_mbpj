<?php

namespace App\Services\Survey;

use App\Models\ReportAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SurveyAttachmentProcessor
{
    public function __construct(
        private SurveyDocumentClassifier $classifier,
        private Survey3dCsvParser $parser3d,
        private Survey2dTxtParser $parser2d,
        private LocalToWgs84Transformer $transformer,
    ) {}

    public function classifyFile(UploadedFile $file): string
    {
        return $this->classifier->classify(
            $file->getClientOriginalName(),
            $file->getMimeType(),
            $this->readFileSample($file)
        );
    }

    private function readFileSample(UploadedFile $file): string
    {
        $path = $file->getRealPath();
        if (! $path || ! is_readable($path)) {
            return '';
        }

        return (string) file_get_contents($path, false, null, 0, 65536);
    }

    private function readFileContents(UploadedFile $file): string
    {
        $path = $file->getRealPath();
        if (! $path || ! is_readable($path)) {
            return '';
        }

        return (string) file_get_contents($path);
    }

    /**
     * Parse an uploaded file for map preview (before the report is saved).
     *
     * @return array{file_name: string, document_type: string, parse_status: string, parse_message: ?string, parsed_data: ?array}
     */
    public function preview(UploadedFile $file, ?float $anchorLat, ?float $anchorLng): array
    {
        $fileName = $file->getClientOriginalName();
        $sample   = $this->readFileSample($file);
        $type     = $this->classifier->classify($fileName, $file->getMimeType(), $sample);

        $result = [
            'file_name'     => $fileName,
            'document_type' => $type,
            'parse_status'  => 'ok',
            'parse_message' => null,
            'parsed_data'   => null,
        ];

        if (! $this->classifier->isSurveyType($type)) {
            return $result;
        }

        if ($type === SurveyDocumentClassifier::TYPE_1D) {
            $result['parsed_data'] = ['type' => '1d', 'previewable' => true];

            return $result;
        }

        [$anchorLat, $anchorLng] = $this->resolveAnchor($anchorLat, $anchorLng);

        try {
            $parsed = $this->parseContent($type, $this->readFileContents($file));
            $result['parsed_data'] = $this->applyGeoref($parsed, $anchorLat, $anchorLng);
        } catch (\Throwable $e) {
            $result['parse_status']  = 'failed';
            $result['parse_message'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * @return array{document_type: string, parsed_data: ?array, parse_status: string, parse_message: ?string}
     */
    public function process(
        UploadedFile $file,
        string $storedPath,
        ?float $anchorLat,
        ?float $anchorLng,
    ): array {
        $content = Storage::disk('public')->get($storedPath);
        $documentType = $this->classifier->classify(
            $file->getClientOriginalName(),
            $file->getMimeType(),
            $content
        );

        if (! $this->classifier->isSurveyType($documentType)) {
            return [
                'document_type' => $documentType,
                'parsed_data'   => null,
                'parse_status'  => 'ok',
                'parse_message' => null,
            ];
        }

        if ($documentType === SurveyDocumentClassifier::TYPE_1D) {
            return [
                'document_type' => $documentType,
                'parsed_data'   => ['type' => '1d', 'previewable' => true],
                'parse_status'  => 'ok',
                'parse_message' => null,
            ];
        }

        [$anchorLat, $anchorLng] = $this->resolveAnchor($anchorLat, $anchorLng);

        try {
            $parsed = $this->parseContent($documentType, $content);
            $parsed = $this->applyGeoref($parsed, $anchorLat, $anchorLng);

            return [
                'document_type' => $documentType,
                'parsed_data'   => $parsed,
                'parse_status'  => 'ok',
                'parse_message' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'document_type' => $documentType,
                'parsed_data'   => null,
                'parse_status'  => 'failed',
                'parse_message' => $e->getMessage(),
            ];
        }
    }

    public function reprocess(ReportAttachment $attachment, ?float $anchorLat, ?float $anchorLng): void
    {
        if (! $this->classifier->isSurveyType($attachment->document_type)) {
            return;
        }

        if ($attachment->document_type === SurveyDocumentClassifier::TYPE_1D) {
            $attachment->update([
                'parsed_data'   => ['type' => '1d', 'previewable' => true],
                'parse_status'  => 'ok',
                'parse_message' => null,
            ]);

            return;
        }

        [$anchorLat, $anchorLng] = $this->resolveAnchor($anchorLat, $anchorLng);

        try {
            $content = Storage::disk('public')->get($attachment->file_path);
            $parsed = $this->parseContent($attachment->document_type, $content);
            $parsed = $this->applyGeoref($parsed, $anchorLat, $anchorLng);

            $attachment->update([
                'parsed_data'   => $parsed,
                'parse_status'  => 'ok',
                'parse_message' => null,
            ]);
        } catch (\Throwable $e) {
            $attachment->update([
                'parse_status'  => 'failed',
                'parse_message' => $e->getMessage(),
            ]);
        }
    }

    /** @return array{0: float, 1: float} */
    private function resolveAnchor(?float $anchorLat, ?float $anchorLng): array
    {
        return [
            $anchorLat ?? (float) config('gis.default_latitude'),
            $anchorLng ?? (float) config('gis.default_longitude'),
        ];
    }

    private function parseContent(string $documentType, string $content): array
    {
        return match ($documentType) {
            SurveyDocumentClassifier::TYPE_3D => $this->parser3d->parse($content),
            SurveyDocumentClassifier::TYPE_2D => $this->parser2d->parse($content),
            default => throw new \InvalidArgumentException('Jenis dokumen tidak disokong.'),
        };
    }

    private function applyGeoref(array $parsed, ?float $anchorLat, ?float $anchorLng): array
    {
        if ($anchorLat === null || $anchorLng === null) {
            return $parsed;
        }

        if ($parsed['type'] === '3d') {
            $local = array_map(fn ($p) => ['xb' => $p['xb'], 'yb' => $p['yb'], 'id' => $p['id'], 'zb' => $p['zb']], $parsed['points']);
            $geo = $this->transformer->transform($local, $anchorLat, $anchorLng);
            $parsed['centroid_x'] = $geo['centroid_x'];
            $parsed['centroid_y'] = $geo['centroid_y'];
            $parsed['points'] = $geo['points'];

            return $parsed;
        }

        if ($parsed['type'] === '2d') {
            $byPoint = [];
            foreach ($parsed['records'] as $r) {
                $byPoint[$r['point']] = ['xb' => $r['xb'], 'yb' => $r['yb'], 'point' => $r['point']];
            }
            $geo = $this->transformer->transform(array_values($byPoint), $anchorLat, $anchorLng);
            $latLngMap = [];
            foreach ($geo['points'] as $p) {
                $key = $p['point'] ?? $p['id'] ?? null;
                if ($key !== null) {
                    $latLngMap[$key] = ['lat' => $p['lat'], 'lng' => $p['lng']];
                }
            }
            $parsed['centroid_x'] = $geo['centroid_x'];
            $parsed['centroid_y'] = $geo['centroid_y'];
            $parsed['records'] = array_map(function ($r) use ($latLngMap) {
                $ll = $latLngMap[$r['point']] ?? null;
                if ($ll) {
                    $r['lat'] = $ll['lat'];
                    $r['lng'] = $ll['lng'];
                }

                return $r;
            }, $parsed['records']);

            return $parsed;
        }

        return $parsed;
    }
}
