<?php

namespace Database\Seeders;

use App\Models\AttachmentType;
use App\Models\Report;
use App\Models\ReportAttachment;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds GIS reports from D:\mbpj\MBSJ EMAIL DATA by site title (CN / SH folders).
 *
 * Override source path with env MBSJ_EMAIL_DATA_PATH if needed.
 */
class MbsjEmailDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UnitCategorySeeder::class);
        $this->call(SaliranCerunStaffSeeder::class);

        $root = env('MBSJ_EMAIL_DATA_PATH', 'D:\\mbpj\\MBSJ EMAIL DATA\\MBSJ EMAIL DATA');
        if (! is_dir($root)) {
            $this->command?->error("Email data folder not found: {$root}");

            return;
        }

        $unit = Unit::where('code', 'SAL-CERUN')->firstOrFail();
        $sinkhole = ReportCategory::where('unit_id', $unit->id)->where('code', 'SINKHOLE')->firstOrFail();
        $cerun = ReportCategory::where('unit_id', $unit->id)->where('code', 'CERUN')->firstOrFail();

        $consultant = User::where('email', 'consultant@mbsj.gov.my')->first()
            ?? User::where('role', 'consultant')->first()
            ?? User::where('email', 'surveyor@mbsj.gov.my')->firstOrFail();

        $types = AttachmentType::query()->get()->keyBy('code');

        $cases = [
            // ── CERUN (CN folders) ──────────────────────────────────────
            [
                'report_number' => 'EMAIL-CN1-2026',
                'file_number' => 'JW4734/PC01/L1',
                'category_id' => $cerun->id,
                'title' => 'Cerun — Pangsapuri D Palma (CN1)',
                'description' => 'Data ukur cerun dari MBSJ EMAIL DATA. Lokasi: Pangsapuri D Palma. Termasuk analisis FLA, flood survey dan laporan borehole.',
                'location_name' => 'Pangsapuri D Palma',
                'address' => 'Pangsapuri D Palma, Selangor',
                'latitude' => 3.0585,
                'longitude' => 101.6150,
                'source_dir' => $root.DIRECTORY_SEPARATOR.'CERUN'.DIRECTORY_SEPARATOR.'CN1',
                'attachments' => [
                    'MAIN_REPORT' => ['CN1 REPORT'.DIRECTORY_SEPARATOR.'CN1 DPALMA CERUN 3 FINAL_FLA_ANALYSIS.pdf'],
                    'BOREHOLE' => ['JW4734 - PC01 L1 PANGSAPURI D PALMA 28012026 - BH.pdf'],
                    'LIDAR' => ['CN1 REPORT'.DIRECTORY_SEPARATOR.'CN1 DPALMA CERUN 3 FINAL_FLA_ANALYSIS.xlsx'],
                    'UMAP' => ['CN1 REPORT'.DIRECTORY_SEPARATOR.'CN1 FLOOD DATA SURVEY_ Summary.pdf'],
                ],
            ],
            [
                'report_number' => 'EMAIL-CN2-2026',
                'file_number' => 'JW4734/PC01/L2',
                'category_id' => $cerun->id,
                'title' => 'Cerun — Pangsapuri Desa Tanjung (CN2)',
                'description' => 'Data ukur cerun dari MBSJ EMAIL DATA. Lokasi: Pangsapuri Desa Tanjung. Termasuk analisis FLA, flood survey dan laporan borehole.',
                'location_name' => 'Pangsapuri Desa Tanjung',
                'address' => 'Pangsapuri Desa Tanjung, Selangor',
                'latitude' => 3.0550,
                'longitude' => 101.6200,
                'source_dir' => $root.DIRECTORY_SEPARATOR.'CERUN'.DIRECTORY_SEPARATOR.'CN2',
                'attachments' => [
                    'MAIN_REPORT' => ['CN2 REPORT'.DIRECTORY_SEPARATOR.'CN2 DESA TANJUNG CERUN 3 FINAL_FLA_ANALYSIS.pdf'],
                    'BOREHOLE' => ['JW4734 -PC01  L2 PANGSAPURI DESA TANJUNG 28012026 - BH.pdf'],
                    'LIDAR' => ['CN2 REPORT'.DIRECTORY_SEPARATOR.'CN2 DESA TANJUNG CERUN 3 FINAL_FLA_ANALYSIS.xlsx'],
                    'UMAP' => ['CN2 REPORT'.DIRECTORY_SEPARATOR.'CN2 FLOOD DATA SURVEY_ Summary.pdf'],
                ],
            ],
            [
                'report_number' => 'EMAIL-CN3-2026',
                'file_number' => 'JW4734/PC01/L3',
                'category_id' => $cerun->id,
                'title' => 'Cerun — Jalan 16 Bukit Kuchai, Puchong (CN3)',
                'description' => 'Data ukur cerun dari MBSJ EMAIL DATA. Lokasi: Jalan 16 Bukit Kuchai, Puchong. Termasuk analisis FLA, flood survey dan laporan borehole.',
                'location_name' => 'Jalan 16 Bukit Kuchai, Puchong',
                'address' => 'Jalan 16, Bukit Kuchai, Puchong',
                'latitude' => 3.0490,
                'longitude' => 101.6400,
                'source_dir' => $root.DIRECTORY_SEPARATOR.'CERUN'.DIRECTORY_SEPARATOR.'CN3',
                'attachments' => [
                    'MAIN_REPORT' => ['CN3 REPORT'.DIRECTORY_SEPARATOR.'CN3 BUKIT KUCHAI CERUN 3 FINAL_FLA_ANALYSIS.pdf'],
                    'BOREHOLE' => ['JW4734 - PC01 L3 JALAN 16 BUKIT KUCHAI, PUCHONG 28012026 -BH.pdf'],
                    'LIDAR' => ['CN3 REPORT'.DIRECTORY_SEPARATOR.'CN3 BUKIT KUCHAI CERUN 3 FINAL_FLA_ANALYSIS.xlsx'],
                    'UMAP' => ['CN3 REPORT'.DIRECTORY_SEPARATOR.'CN3 FLOOD DATA SURVEY_ Summary.pdf'],
                ],
            ],
            [
                'report_number' => 'EMAIL-CN4-2026',
                'file_number' => 'JW4734/PC01/L4',
                'category_id' => $cerun->id,
                'title' => 'Cerun — Pangsapuri Saraka (CN4)',
                'description' => 'Data ukur cerun dari MBSJ EMAIL DATA. Lokasi: Pangsapuri Saraka / Sarakah. Termasuk analisis FLA, flood survey dan laporan borehole.',
                'location_name' => 'Pangsapuri Saraka',
                'address' => 'Pangsapuri Saraka, Selangor',
                'latitude' => 3.0620,
                'longitude' => 101.6080,
                'source_dir' => $root.DIRECTORY_SEPARATOR.'CERUN'.DIRECTORY_SEPARATOR.'CN4',
                'attachments' => [
                    'MAIN_REPORT' => ['CN4 REPORT'.DIRECTORY_SEPARATOR.'CN4 SARAKAH CERUN 3 FINAL_FLA_ANALYSIS.pdf'],
                    'BOREHOLE' => ['JW4734 - PC01 L4 PANGSAPURI SARAKA 28012026 - BH.pdf'],
                    'LIDAR' => ['CN4 REPORT'.DIRECTORY_SEPARATOR.'CN4 SARAKAH CERUN 3 FINAL_FLA_ANALYSIS.xlsx'],
                    'UMAP' => ['CN4 REPORT'.DIRECTORY_SEPARATOR.'CN4 FLOOD DATA SURVEY_ Summary.pdf'],
                ],
            ],
            [
                'report_number' => 'EMAIL-CN6-2026',
                'file_number' => 'JW4734/PC01/L6',
                'category_id' => $cerun->id,
                'title' => 'Cerun — Pangsapuri Sri Penaga (CN6)',
                'description' => 'Data ukur cerun dari MBSJ EMAIL DATA. Lokasi: Pangsapuri Sri Penaga. Termasuk analisis FLA, flood survey dan laporan borehole.',
                'location_name' => 'Pangsapuri Sri Penaga',
                'address' => 'Pangsapuri Sri Penaga, Selangor',
                'latitude' => 3.0700,
                'longitude' => 101.6000,
                'source_dir' => $root.DIRECTORY_SEPARATOR.'CERUN'.DIRECTORY_SEPARATOR.'CN6',
                'attachments' => [
                    'MAIN_REPORT' => ['CN6 REPORT'.DIRECTORY_SEPARATOR.'CN6 SERI PENAGA CERUN 3 FINAL_FLA_ANALYSIS.pdf'],
                    'BOREHOLE' => ['JW4734 - PC01 L6 PANGSAPURI SRI PENAGA 28012026 - BH.pdf'],
                    'LIDAR' => ['CN6 REPORT'.DIRECTORY_SEPARATOR.'CN6 SERI PENAGA CERUN 3 FINAL_FLA_ANALYSIS.xlsx'],
                    'UMAP' => ['CN6 REPORT'.DIRECTORY_SEPARATOR.'CN6 FLOOD DATA SURVEY_ Summary.pdf'],
                ],
            ],

            // ── SINKHOLE (SH folders) ───────────────────────────────────
            [
                'report_number' => 'EMAIL-SH1-2026',
                'file_number' => 'NZ4735/SH1/PLD01',
                'category_id' => $sinkhole->id,
                'title' => 'Sinkhole — SS15 Subang Jaya (SH1)',
                'description' => 'Data sinkhole dari MBSJ EMAIL DATA. Lokasi: SS15 Subang Jaya. Termasuk LiDAR FLA analysis, slope surface/category, UMAP dan index borehole.',
                'location_name' => 'SS15 Subang Jaya',
                'address' => 'SS15, Subang Jaya, Selangor',
                'latitude' => 3.0748,
                'longitude' => 101.5865,
                'source_dir' => $root.DIRECTORY_SEPARATOR.'SINKHOLE'.DIRECTORY_SEPARATOR.'SH 1',
                'attachments' => [
                    'MAIN_REPORT' => ['1 SH1 SS15 SUBANG TOPO NEW LIDAR_FLA_ANALYSIS.pdf'],
                    'LIDAR' => ['1 SH1 SS15 SUBANG TOPO NEW LIDAR_FLA_ANALYSIS.xlsx'],
                    'UMAP' => ['SH1 Umap 15062026 - SH1.PDF'],
                    'BOREHOLE' => ['SH1 SS15 SUBANG JAYA INDEX BH 22062026.pdf'],
                    'SEISMIC' => ['2 SH1_MBSJ_SLOPE_SURFACE_ANALYSIS.pdf'],
                ],
            ],
            [
                'report_number' => 'EMAIL-SH2-2026',
                'file_number' => 'NZ4735/SH2/PLD01',
                'category_id' => $sinkhole->id,
                'title' => 'Sinkhole — Seri Kembangan (SH2)',
                'description' => 'Data sinkhole dari MBSJ EMAIL DATA. Lokasi: Seri Kembangan (SH2A & SH2B Pasar Borong). Termasuk FLA analysis, slope surface/category dan UMAP.',
                'location_name' => 'Seri Kembangan',
                'address' => 'Seri Kembangan, Selangor',
                'latitude' => 3.0278,
                'longitude' => 101.7078,
                'source_dir' => $root.DIRECTORY_SEPARATOR.'SINKHOLE'.DIRECTORY_SEPARATOR.'SH2',
                'attachments' => [
                    'MAIN_REPORT' => ['1 SH2A SERI KEMBANGAN 2 FINAL_FLA_ANALYSIS.pdf'],
                    'LIDAR' => ['1 SH2A SERI KEMBANGAN 2 FINAL_FLA_ANALYSIS.xlsx'],
                    'UMAP' => ['SH2 Umap 15062026 - SH2 - Copy.PDF', 'SH2 Umap 15062026 - SH2.PDF'],
                    'BOREHOLE' => ['SH2 JLN PP40 PP50 INDEX BH 22062026.pdf'],
                    'SEISMIC' => ['2 SH2A_MBSJ_SLOPE_SURFACE_ANALYSIS.pdf'],
                ],
            ],
            [
                'report_number' => 'EMAIL-SH3-2026',
                'file_number' => 'NZ4735/SH3/PLD01',
                'category_id' => $sinkhole->id,
                'title' => 'Sinkhole — Batu 14, Puchong (SH3)',
                'description' => 'Data sinkhole dari MBSJ EMAIL DATA. Lokasi: Batu 14, Puchong. Termasuk FLA analysis, slope surface/category, UMAP dan index borehole.',
                'location_name' => 'Batu 14, Puchong',
                'address' => 'Batu 14, Puchong, Selangor',
                'latitude' => 3.0400,
                'longitude' => 101.6500,
                'source_dir' => $root.DIRECTORY_SEPARATOR.'SINKHOLE'.DIRECTORY_SEPARATOR.'SH3',
                'attachments' => [
                    'MAIN_REPORT' => ['1 SH3 BATU 14 FINAL_FLA_ANALYSIS.pdf'],
                    'LIDAR' => ['1 SH3 BATU 14 FINAL_FLA_ANALYSIS.xlsx'],
                    'UMAP' => ['SH3 Umap 15062026 - SH3.PDF'],
                    'BOREHOLE' => ['SH3 BATU 14 PUCHONG INDEX BH 22062026.pdf'],
                    'SEISMIC' => ['2 SH3_MBSJ_SLOPE_SURFACE_ANALYSIS.pdf'],
                ],
            ],
            [
                'report_number' => 'EMAIL-SH4-2026',
                'file_number' => 'NZ4735/SH4/PLD01',
                'category_id' => $sinkhole->id,
                'title' => 'Sinkhole — IOI Mall, Puchong (SH4)',
                'description' => 'Data sinkhole dari MBSJ EMAIL DATA. Lokasi: IOI Mall, Puchong. Termasuk FLA analysis, slope surface/category, UMAP dan index borehole.',
                'location_name' => 'IOI Mall, Puchong',
                'address' => 'IOI Mall, Puchong, Selangor',
                'latitude' => 3.0469,
                'longitude' => 101.6185,
                'source_dir' => $root.DIRECTORY_SEPARATOR.'SINKHOLE'.DIRECTORY_SEPARATOR.'SH4',
                'attachments' => [
                    'MAIN_REPORT' => ['1 SH4 IOI MALL FINAL_FLA_ANALYSIS.pdf'],
                    'LIDAR' => ['1 SH4 IOI MALL FINAL_FLA_ANALYSIS.xlsx'],
                    'UMAP' => ['SH4 Umap15062026 -SH4.PDF'],
                    'BOREHOLE' => ['SH4 IOI MALL PUCHONG INDEX BH 22062026.pdf'],
                    'SEISMIC' => ['2 SH4B_MBSJ_SLOPE_SURFACE_ANALYSIS.pdf'],
                ],
            ],
            [
                'report_number' => 'EMAIL-SH5-2026',
                'file_number' => 'NZ4735/SH5/PLD01',
                'category_id' => $sinkhole->id,
                'title' => 'Sinkhole — Pusat Dagangan Seri Kembangan (SH5)',
                'description' => 'Data sinkhole dari MBSJ EMAIL DATA. Lokasi: Pusat Dagangan / Pusat Komersial Seri Kembangan. Termasuk FLA analysis, slope, UMAP dan index borehole.',
                'location_name' => 'Pusat Dagangan Seri Kembangan',
                'address' => 'Pusat Dagangan Seri Kembangan, Selangor',
                'latitude' => 3.0300,
                'longitude' => 101.7050,
                'source_dir' => $root.DIRECTORY_SEPARATOR.'SINKHOLE'.DIRECTORY_SEPARATOR.'SH5',
                'attachments' => [
                    'MAIN_REPORT' => ['1 SH5 SERI KEMBANGAN PUSAT KOMERSIAL FINAL_FLA_ANALYSIS.pdf'],
                    'LIDAR' => ['1 SH5 SERI KEMBANGAN PUSAT KOMERSIAL FINAL_FLA_ANALYSIS.xlsx'],
                    'UMAP' => ['SH5 Umap 15062026 - SH5.PDF'],
                    'BOREHOLE' => ['SH5 PUSAT DAGANGAN SERI KEMBANGAN INDEX BH 22062026.pdf'],
                    'SEISMIC' => ['2 SH5_MBSJ_SLOPE_SURFACE_ANALYSIS.pdf'],
                ],
            ],
            [
                'report_number' => 'EMAIL-SH6-2026',
                'file_number' => 'NZ4735/SH6/PLD01',
                'category_id' => $sinkhole->id,
                'title' => 'Sinkhole — Bukit Wawasan (SH6)',
                'description' => 'Data sinkhole dari MBSJ EMAIL DATA. Lokasi: Bukit Wawasan. Termasuk FLA analysis, slope surface/category, UMAP dan index borehole.',
                'location_name' => 'Bukit Wawasan',
                'address' => 'Bukit Wawasan, Selangor',
                'latitude' => 3.0420,
                'longitude' => 101.6250,
                'source_dir' => $root.DIRECTORY_SEPARATOR.'SINKHOLE'.DIRECTORY_SEPARATOR.'SH6',
                'attachments' => [
                    'MAIN_REPORT' => ['1 SH6 BUKIT WAWASAN FINAL_FLA_ANALYSIS.pdf'],
                    'LIDAR' => ['1 SH6 BUKIT WAWASAN FINAL_FLA_ANALYSIS.xlsx'],
                    'UMAP' => ['SH6 Umap 15062026 - SH6.PDF'],
                    'BOREHOLE' => ['SH6 BUKIT WAWASAN INDEX BH 22062026.pdf'],
                    'SEISMIC' => ['2 SH6_MBSJ_SLOPE_SURFACE_ANALYSIS.pdf'],
                ],
            ],
        ];

        // Optional: top-level survey photo report
        $photoPdf = $root.DIRECTORY_SEPARATOR.'CERUN'.DIRECTORY_SEPARATOR.'NZ4734 LAPORAN KERJA UKUR - GAMBAR 2026.pdf';
        if (is_file($photoPdf)) {
            $cases[] = [
                'report_number' => 'EMAIL-NZ4734-2026',
                'file_number' => 'NZ4734',
                'category_id' => $cerun->id,
                'title' => 'Laporan Kerja Ukur — Gambar 2026 (NZ4734)',
                'description' => 'Laporan kerja ukur dengan gambar tapak dari MBSJ EMAIL DATA (NZ4734).',
                'location_name' => 'Tapak Ukur NZ4734',
                'address' => 'MBSJ — kawasan ukur NZ4734',
                'latitude' => 3.0600,
                'longitude' => 101.6100,
                'source_dir' => $root.DIRECTORY_SEPARATOR.'CERUN',
                'attachments' => [
                    'MAIN_REPORT' => ['NZ4734 LAPORAN KERJA UKUR - GAMBAR 2026.pdf'],
                ],
            ];
        }

        $created = 0;
        $attached = 0;

        foreach ($cases as $case) {
            $attachments = $case['attachments'] ?? [];
            $sourceDir = $case['source_dir'];
            unset($case['attachments'], $case['source_dir']);

            $report = Report::updateOrCreate(
                ['report_number' => $case['report_number']],
                array_merge($case, [
                    'user_id' => $consultant->id,
                    'unit_id' => $unit->id,
                    'status' => 'submitted',
                    'workflow_status' => 'pending_site_visit',
                    'vendor_name' => 'NZ Survey Consultant',
                    'submitted_at' => now()->subDays(rand(1, 14)),
                ])
            );
            $created++;

            foreach ($attachments as $typeCode => $candidates) {
                $type = $types->get($typeCode);
                if (! $type) {
                    continue;
                }

                $src = $this->resolveFirstExisting($sourceDir, (array) $candidates);
                if (! $src) {
                    $this->command?->warn("  Missing {$typeCode} for {$report->report_number}");

                    continue;
                }

                if ($this->attachFile($report, $type, $src, $consultant->id)) {
                    $attached++;
                }
            }

            $this->command?->info("Seeded: {$report->report_number} — {$report->title}");
        }

        $this->command?->info("Done. Reports: {$created}, attachments copied: {$attached}");
    }

    /**
     * @param  list<string>  $relativeCandidates
     */
    private function resolveFirstExisting(string $baseDir, array $relativeCandidates): ?string
    {
        foreach ($relativeCandidates as $rel) {
            $path = $baseDir.DIRECTORY_SEPARATOR.$rel;
            if (is_file($path)) {
                return $path;
            }
        }

        // Fallback: case-insensitive search by basename in baseDir (non-recursive then recursive)
        foreach ($relativeCandidates as $rel) {
            $basename = basename($rel);
            $found = $this->findFileCi($baseDir, $basename);
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    private function findFileCi(string $dir, string $basename): ?string
    {
        if (! is_dir($dir)) {
            return null;
        }

        $target = Str::lower($basename);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && Str::lower($file->getFilename()) === $target) {
                return $file->getPathname();
            }
        }

        return null;
    }

    private function attachFile(Report $report, AttachmentType $type, string $sourcePath, int $uploaderId): bool
    {
        $original = basename($sourcePath);
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION) ?: 'bin');
        $stored = Str::uuid()->toString().'.'.$ext;
        $destRel = "reports/{$report->id}/technical/{$stored}";

        Storage::disk('public')->makeDirectory("reports/{$report->id}/technical");
        File::copy($sourcePath, Storage::disk('public')->path($destRel));

        // Mark previous current of same type as not current
        ReportAttachment::where('report_id', $report->id)
            ->where('attachment_type_id', $type->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        $mime = match ($ext) {
            'pdf' => 'pdf',
            'xlsx' => 'xlsx',
            'xls' => 'xls',
            'csv' => 'csv',
            'docx' => 'docx',
            'dwg' => 'dwg',
            default => $ext ?: 'bin',
        };

        ReportAttachment::updateOrCreate(
            [
                'report_id' => $report->id,
                'attachment_type_id' => $type->id,
                'original_filename' => $original,
            ],
            [
                'file_name' => $original,
                'stored_filename' => $stored,
                'file_path' => $destRel,
                'file_type' => $mime,
                'file_size' => filesize($sourcePath) ?: 0,
                'uploaded_by' => $uploaderId,
                'uploaded_at' => now(),
                'version' => 1,
                'is_current' => true,
                'document_type' => 'other',
            ]
        );

        return true;
    }
}
