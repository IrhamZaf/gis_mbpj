<?php

namespace Database\Seeders;

use App\Models\AttachmentType;
use App\Models\ReportCategory;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class SaliranCerunCategorySeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UnitSeeder::class);

        $unit = Unit::where('code', 'SAL-CERUN')->first();
        if (! $unit) {
            $this->command?->error('SAL-CERUN unit missing');

            return;
        }

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

        $sinkhole = ReportCategory::updateOrCreate(
            ['unit_id' => $unit->id, 'code' => 'SINKHOLE'],
            [
                'name' => 'Sinkhole',
                'slug' => 'sinkhole-saliran-cerun',
                'description' => 'Laporan Sinkhole — Unit Saliran & Cerun',
                'status' => 'active',
            ]
        );

        $cerun = ReportCategory::updateOrCreate(
            ['unit_id' => $unit->id, 'code' => 'CERUN_RUNTUH'],
            [
                'name' => 'Cerun Runtuh',
                'slug' => 'cerun-runtuh-saliran-cerun',
                'description' => 'Laporan Cerun Runtuh — Unit Saliran & Cerun',
                'status' => 'active',
            ]
        );

        $pivot = [
            'MAIN_REPORT' => ['sink' => 'Report Sinkhole', 'cerun' => 'Report Cerun Runtuh', 'sort' => 1],
            'BOREHOLE' => ['sink' => 'Report Borehole', 'cerun' => 'Report Borehole', 'sort' => 2],
            'LIDAR' => ['sink' => 'Data LiDAR', 'cerun' => 'Data LiDAR', 'sort' => 3],
            'UMAP' => ['sink' => 'Report UMAP', 'cerun' => 'Report UMAP', 'sort' => 4],
            'SEISMIC' => ['sink' => 'Report Seismic', 'cerun' => 'Report Seismic', 'sort' => 5],
        ];

        $sinkhole->attachmentTypes()->sync(
            collect($pivot)->mapWithKeys(fn ($labels, $code) => [
                $typeModels[$code]->id => ['display_name' => $labels['sink'], 'sort_order' => $labels['sort']],
            ])->all()
        );
        $cerun->attachmentTypes()->sync(
            collect($pivot)->mapWithKeys(fn ($labels, $code) => [
                $typeModels[$code]->id => ['display_name' => $labels['cerun'], 'sort_order' => $labels['sort']],
            ])->all()
        );

        $this->command?->info('Saliran & Cerun categories + attachment types seeded.');
    }
}
