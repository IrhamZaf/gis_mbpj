<?php

namespace App\Console\Commands;

use App\Models\Incident;
use App\Models\SurveyData;
use App\Models\SurveyUpload;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ImportMbSurveyReportsCommand extends Command
{
    protected $signature = 'mbpj:import-surveyor-reports
                            {path : Folder penuh (contoh: C:\Users\User\Downloads\MBPJ\MBPJ)}
                            {--user=vendor@mbpj.gov.my : E-mel pengguna untuk surveyor_id}
                            {--dry-run : Papar fail yang dikenali tanpa menyimpan}';

    protected $description = 'Import fail surveyor (PDF, TXT, Excel, GeoJSON, dll.) daripada folder; padanan kod CN/SH pada nama folder atau nama fail';

    /** @var list<string> */
    protected array $allowedExt = ['pdf', 'txt', 'xlsx', 'xls', 'json', 'geojson', 'kml', 'kmz', 'zip', 'tif', 'tiff'];

    public function handle(): int
    {
        $rawPath = $this->argument('path');
        $path = $this->normalizeWindowsPath($rawPath);
        if (! is_dir($path)) {
            $this->error('Folder tidak wujud atau tidak boleh dibaca: '.$path);

            return self::FAILURE;
        }

        $user = User::query()->where('email', (string) $this->option('user'))->first();
        if (! $user) {
            $this->error('Pengguna tidak dijumpai untuk --user='.((string) $this->option('user')));

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $rootReal = realpath($path);
        if ($rootReal === false) {
            $this->error('Tidak dapat resolve path.');

            return self::FAILURE;
        }

        $files = $this->collectFiles($rootReal);
        if ($files === []) {
            $this->warn('Tiada fail dibenarkan dijumpai dalam folder (sambungan: '.implode(', ', $this->allowedExt).').');

            return self::SUCCESS;
        }

        $grouped = [];
        foreach ($files as $full) {
            $fullNorm = str_replace('\\', '/', $full);
            $rootNorm = rtrim(str_replace('\\', '/', $rootReal), '/');
            if (! str_starts_with($fullNorm, $rootNorm)) {
                $this->warn('Laluan luar root: '.$full);

                continue;
            }
            $rel = ltrim(substr($fullNorm, strlen($rootNorm)), '/');
            $code = $this->detectIncidentCode($rel);
            if ($code === null) {
                $this->line('Langkau (tiada kod CN/SH): '.$rel);

                continue;
            }
            $grouped[$code][] = $full;
        }

        if ($grouped === []) {
            $this->warn('Tiada fail dengan kod CN# atau SH# pada laluan relatif. Gunakan subfolder <cyan>CN1</> / <cyan>SH2</> atau awalan fail seperti <cyan>CN1_laporan.pdf</>.');

            return self::SUCCESS;
        }

        foreach ($grouped as $code => $paths) {
            $incident = Incident::query()->where('incident_number', $code)->first();
            if (! $incident) {
                $this->error("Insiden '{$code}' tidak wujud dalam sistem. Jalankan `php artisan mbpj:migrate-incident-codes` dahulu jika kod masih format lama.");

                continue;
            }
            if (! $this->categoryMatchesCode($incident, $code)) {
                $this->error("Kod '{$code}' tidak sepadan kategori insiden #{$incident->id} ({$incident->category}).");

                continue;
            }

            $this->info("{$code}: ".count($paths).' fail');

            if ($dry) {
                foreach ($paths as $p) {
                    $this->line('  [dry-run] '.basename($p));
                }

                continue;
            }

            $survey = SurveyData::query()->create([
                'incident_id' => $incident->id,
                'surveyor_id' => $user->id,
                'vendor_name' => 'Import folder surveyor',
                'surveyor_name' => $user->name,
                'survey_date' => now()->toDateString(),
                'survey_type' => 'Import laporan (MBPJ)',
                'gps_coordinates' => [
                    'lat' => $incident->latitude,
                    'lng' => $incident->longitude,
                ],
                'geojson_data' => null,
                'gis_metadata' => [
                    'source' => 'mbpj:import-surveyor-reports',
                    'import_root' => $rootReal,
                    'imported_at' => now()->toIso8601String(),
                ],
                'converted_coordinates' => [
                    'wgs84' => [
                        'lat' => $incident->latitude,
                        'lng' => $incident->longitude,
                        'crs' => 'EPSG:4326',
                    ],
                    'source' => 'incident_centre',
                ],
                'notes' => 'Import automatik daripada folder.',
                'technical_notes' => null,
                'original_filename' => null,
                'review_status' => SurveyData::REVIEW_PENDING,
                'version' => $this->nextSurveyVersion($incident->id),
                'parent_survey_id' => null,
            ]);

            $categories = [];
            $firstName = null;
            foreach ($paths as $fullPath) {
                $cat = $this->guessUploadCategory($fullPath);
                $stored = $this->storeAndAttach($survey, $fullPath, $cat, $user->id);
                if ($stored) {
                    $categories[] = $cat;
                    $firstName ??= basename($fullPath);
                }
            }

            $survey->update([
                'gis_metadata' => array_merge($survey->gis_metadata ?? [], [
                    'file_categories' => array_values(array_unique($categories)),
                    'upload_count' => $survey->uploads()->count(),
                ]),
                'original_filename' => $firstName,
            ]);
        }

        if ($dry) {
            $this->warn('Dry-run: tiada SurveyData/SurveyUpload dicipta.');
        } else {
            $this->info('Import selesai.');
        }

        return self::SUCCESS;
    }

    protected function normalizeWindowsPath(string $path): string
    {
        $path = trim($path, " \t\"'");

        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    /**
     * @return list<string>
     */
    protected function collectFiles(string $rootReal): array
    {
        $out = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootReal, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $info */
        foreach ($iterator as $info) {
            if (! $info->isFile()) {
                continue;
            }
            $ext = strtolower($info->getExtension());
            if (! in_array($ext, $this->allowedExt, true)) {
                continue;
            }
            $out[] = $info->getPathname();
        }

        return $out;
    }

    protected function detectIncidentCode(string $relativePath): ?string
    {
        $relativePath = str_replace('\\', '/', $relativePath);
        $segments = preg_split('#/#', $relativePath, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($segments as $seg) {
            if (preg_match('/^(CN|SH)(\d+)$/i', $seg, $m)) {
                return strtoupper($m[1]).(int) $m[2];
            }
        }

        $leaf = end($segments) ?: '';
        $base = (string) pathinfo($leaf, PATHINFO_FILENAME);
        if (preg_match('/^(CN|SH)(\d+)/i', $base, $m)) {
            return strtoupper($m[1]).(int) $m[2];
        }
        if (preg_match('/(?:^|[_\-\s])(CN|SH)(\d+)/i', $base, $m)) {
            return strtoupper($m[1]).(int) $m[2];
        }

        return null;
    }

    protected function categoryMatchesCode(Incident $incident, string $code): bool
    {
        if (str_starts_with($code, 'CN')) {
            return $incident->category === Incident::CATEGORY_SLOPE;
        }
        if (str_starts_with($code, 'SH')) {
            return $incident->category === Incident::CATEGORY_SINKHOLE;
        }

        return false;
    }

    protected function nextSurveyVersion(int $incidentId): int
    {
        $max = (int) SurveyData::query()->where('incident_id', $incidentId)->max('version');

        return $max + 1;
    }

    protected function guessUploadCategory(string $fullPath): string
    {
        $base = strtolower(basename($fullPath));
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => str_contains($base, 'geotek') || str_contains($base, 'geotech')
                ? SurveyUpload::CAT_DOC_PDF_GEOTECH
                : (str_contains($base, 'inspeksi') || str_contains($base, 'inspection')
                    ? SurveyUpload::CAT_DOC_INSPECTION
                    : SurveyUpload::CAT_DOC_PDF_SURVEY),
            'txt' => SurveyUpload::CAT_DOC_SURVEY_TXT_READINGS,
            'xlsx', 'xls' => SurveyUpload::CAT_DOC_EXCEL_ANALYTICS,
            'json', 'geojson' => SurveyUpload::CAT_GIS_GEOJSON,
            'kml', 'kmz' => SurveyUpload::CAT_GIS_KML,
            'zip' => SurveyUpload::CAT_GIS_SHP,
            'tif', 'tiff' => SurveyUpload::CAT_GIS_GEOTIFF,
            default => SurveyUpload::CAT_GIS_CONTOUR,
        };
    }

    protected function storeAndAttach(SurveyData $survey, string $fullPath, string $category, int $userId): bool
    {
        $disk = Storage::disk('public');
        $sub = match ($category) {
            SurveyUpload::CAT_GIS_GEOJSON, SurveyUpload::CAT_GIS_KML, SurveyUpload::CAT_GIS_SHP,
            SurveyUpload::CAT_GIS_GEOTIFF, SurveyUpload::CAT_GIS_CONTOUR => 'gis',
            SurveyUpload::CAT_MEDIA_DRONE_IMAGE, SurveyUpload::CAT_MEDIA_DRONE_VIDEO,
            SurveyUpload::CAT_MEDIA_BEFORE, SurveyUpload::CAT_MEDIA_AFTER => 'media',
            default => 'docs',
        };

        $name = basename($fullPath);
        $dest = 'imports/mbpj/'.$survey->incident_id.'/'.uniqid('', true).'_'.$name;

        try {
            $disk->put($dest, (string) file_get_contents($fullPath));
        } catch (\Throwable $e) {
            $this->error('Gagal menyalin: '.$name.' — '.$e->getMessage());

            return false;
        }

        SurveyUpload::query()->create([
            'survey_data_id' => $survey->id,
            'category' => $category,
            'file_path' => $dest,
            'original_name' => $name,
            'mime_type' => @mime_content_type($fullPath) ?: null,
            'file_size' => @filesize($fullPath) ?: null,
            'version' => $survey->version,
            'uploaded_by' => $userId,
        ]);

        $this->line('  Disimpan: '.$name.' → '.$category);

        return true;
    }
}
