<?php

namespace Database\Seeders;

use App\Models\AttachmentType;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Support\UnitModule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UnitCategorySeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UnitSeeder::class);

        $commonExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'zip', 'jpg', 'jpeg', 'png', 'tif', 'tiff'];
        $lidarExt = array_values(array_unique(array_merge($commonExt, ['las', 'laz', 'geotiff', 'xyz', 'txt'])));

        $types = [
            ['code' => 'MAIN_REPORT', 'name' => 'Main Report', 'required' => true, 'allowed_extensions' => $commonExt, 'max_size' => 51200],
            ['code' => 'BOREHOLE', 'name' => 'Report Borehole', 'required' => true, 'allowed_extensions' => $commonExt, 'max_size' => 51200],
            ['code' => 'LIDAR', 'name' => 'Data LiDAR', 'required' => true, 'allowed_extensions' => $lidarExt, 'max_size' => 204800],
            ['code' => 'UMAP', 'name' => 'Report UMAP', 'required' => true, 'allowed_extensions' => $commonExt, 'max_size' => 51200],
            ['code' => 'SEISMIC', 'name' => 'Report Seismic', 'required' => true, 'allowed_extensions' => $commonExt, 'max_size' => 51200],
        ];

        $typeModels = [];
        foreach ($types as $t) {
            $typeModels[$t['code']] = AttachmentType::updateOrCreate(
                ['code' => $t['code']],
                [
                    'name' => $t['name'],
                    'required' => $t['required'],
                    'allowed_extensions' => $t['allowed_extensions'],
                    'max_size' => $t['max_size'],
                ]
            );
        }

        $categoryDefs = [
            'SINKHOLE' => ['name' => 'Sinkhole', 'description' => 'Laporan Sinkhole'],
            'CERUN' => ['name' => 'Cerun', 'description' => 'Laporan Cerun'],
            'BOREHOLE' => ['name' => 'Borehole', 'description' => 'Laporan Borehole'],
        ];

        $attachmentPivot = [
            'SINKHOLE' => [
                'MAIN_REPORT' => ['label' => 'Report Sinkhole', 'sort' => 1],
                'BOREHOLE' => ['label' => 'Report Borehole', 'sort' => 2],
                'LIDAR' => ['label' => 'Data LiDAR', 'sort' => 3],
                'UMAP' => ['label' => 'Report UMAP', 'sort' => 4],
                'SEISMIC' => ['label' => 'Report Seismic', 'sort' => 5],
            ],
            'CERUN' => [
                'MAIN_REPORT' => ['label' => 'Report Cerun', 'sort' => 1],
                'BOREHOLE' => ['label' => 'Report Borehole', 'sort' => 2],
                'LIDAR' => ['label' => 'Data LiDAR', 'sort' => 3],
                'UMAP' => ['label' => 'Report UMAP', 'sort' => 4],
                'SEISMIC' => ['label' => 'Report Seismic', 'sort' => 5],
            ],
            'BOREHOLE' => [
                'MAIN_REPORT' => ['label' => 'Report Borehole', 'sort' => 1],
                'LIDAR' => ['label' => 'Data LiDAR', 'sort' => 2],
                'UMAP' => ['label' => 'Report UMAP', 'sort' => 3],
            ],
        ];

        foreach (UnitModule::UNIT_SLUGS as $unitSlug => $unitCode) {
            $unit = Unit::where('code', $unitCode)->first();
            if (! $unit) {
                $this->command?->error("Unit {$unitCode} missing");

                continue;
            }

            // Migrate legacy CERUN_RUNTUH → CERUN for this unit (keep same row / reports)
            $legacyCerun = ReportCategory::where('unit_id', $unit->id)->where('code', 'CERUN_RUNTUH')->first();
            if ($legacyCerun) {
                $legacyCerun->update([
                    'code' => 'CERUN',
                    'name' => 'Cerun',
                    'slug' => 'cerun-'.$unitSlug,
                    'description' => 'Laporan Cerun — '.$unit->name,
                    'status' => 'active',
                ]);
            }

            foreach ($categoryDefs as $code => $def) {
                $category = ReportCategory::updateOrCreate(
                    ['unit_id' => $unit->id, 'code' => $code],
                    [
                        'name' => $def['name'],
                        'slug' => Str::slug($def['name']).'-'.$unitSlug,
                        'description' => $def['description'].' — '.$unit->name,
                        'status' => 'active',
                    ]
                );

                $pivot = $attachmentPivot[$code] ?? [];
                if ($pivot !== []) {
                    $category->attachmentTypes()->sync(
                        collect($pivot)->mapWithKeys(fn ($meta, $typeCode) => [
                            $typeModels[$typeCode]->id => [
                                'display_name' => $meta['label'],
                                'sort_order' => $meta['sort'],
                            ],
                        ])->all()
                    );
                }
            }
        }

        $this->command?->info('Seeded 4 units × 3 categories (Sinkhole, Cerun, Borehole).');
    }
}
