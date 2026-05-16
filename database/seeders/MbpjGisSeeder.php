<?php

namespace Database\Seeders;

use App\Models\GisLayer;
use App\Models\Incident;
use App\Models\IncidentMedia;
use App\Models\IncidentTimeline;
use App\Models\SurveyData;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MbpjGisSeeder extends Seeder
{
    public function run(): void
    {
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        Storage::disk('public')->makeDirectory('demos');
        Storage::disk('public')->put('demos/before.gif', $gif);
        Storage::disk('public')->put('demos/after.gif', $gif);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@mbpj.gov.my'],
            ['name' => 'Admin MBPJ', 'password' => Hash::make('password'), 'role' => User::ROLE_ADMIN]
        );
        $engineer = User::query()->updateOrCreate(
            ['email' => 'engineer@mbpj.gov.my'],
            ['name' => 'Jurutera Demo', 'password' => Hash::make('password'), 'role' => User::ROLE_ENGINEER]
        );
        $surveyorLuar = User::query()->updateOrCreate(
            ['email' => 'vendor@mbpj.gov.my'],
            ['name' => 'Surveyor Dilantik Demo', 'password' => Hash::make('password'), 'role' => User::ROLE_VENDOR]
        );

        $pjBoundary = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => ['name' => 'Sempadan contoh PJ'],
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [[
                            [101.58, 3.09],
                            [101.64, 3.09],
                            [101.64, 3.13],
                            [101.58, 3.13],
                            [101.58, 3.09],
                        ]],
                    ],
                ],
            ],
        ];

        GisLayer::query()->updateOrCreate(
            ['name' => 'Sempadan Petaling Jaya (demo)'],
            [
                'type' => 'boundary',
                'file_path' => null,
                'geojson_data' => $pjBoundary,
                'metadata' => ['source' => 'demo'],
                'is_active' => true,
            ]
        );

        $incidentsData = [
            ['category' => Incident::CATEGORY_SINKHOLE, 'lat' => 3.1045, 'lng' => 101.6068, 'risk' => Incident::RISK_CRITICAL, 'status' => 'baru_dilaporkan', 'addr' => 'Seksyen 14'],
            ['category' => Incident::CATEGORY_SLOPE, 'lat' => 3.112, 'lng' => 101.598, 'risk' => Incident::RISK_MONITOR, 'status' => 'dalam_siasatan', 'addr' => 'Bukit Gasing'],
            ['category' => Incident::CATEGORY_SINKHOLE, 'lat' => 3.098, 'lng' => 101.62, 'risk' => Incident::RISK_SAFE, 'status' => 'selesai', 'addr' => 'SS2'],
            ['category' => Incident::CATEGORY_SLOPE, 'lat' => 3.12, 'lng' => 101.61, 'risk' => Incident::RISK_CRITICAL, 'status' => 'tindakan_diperlukan', 'addr' => 'Taman Medan'],
            ['category' => Incident::CATEGORY_SINKHOLE, 'lat' => 3.09, 'lng' => 101.59, 'risk' => Incident::RISK_MONITOR, 'status' => 'dalam_pemantauan', 'addr' => 'Kelana Jaya'],
        ];

        foreach ($incidentsData as $idx => $row) {
            $inc = Incident::query()->create([
                'category' => $row['category'],
                'date_reported' => now()->subDays(20 - $idx * 3),
                'latitude' => $row['lat'],
                'longitude' => $row['lng'],
                'address' => $row['addr'],
                'risk_level' => $row['risk'],
                'status' => $row['status'],
                'description' => 'Data demo Petaling Jaya untuk ujian sistem GIS.',
                'reported_by' => $engineer->id,
                'assigned_engineer' => $engineer->id,
            ]);

            IncidentTimeline::query()->create([
                'incident_id' => $inc->id,
                'action' => 'created',
                'description' => 'Rekod demo dicipta.',
                'performed_by' => $engineer->id,
                'status_from' => null,
                'status_to' => $inc->status,
            ]);

            if ($idx === 0) {
                IncidentMedia::query()->create([
                    'incident_id' => $inc->id,
                    'type' => 'image',
                    'file_path' => 'demos/before.gif',
                    'caption' => 'Drone sebelum',
                    'upload_phase' => 'before',
                ]);
                IncidentMedia::query()->create([
                    'incident_id' => $inc->id,
                    'type' => 'image',
                    'file_path' => 'demos/after.gif',
                    'caption' => 'Drone selepas',
                    'upload_phase' => 'after',
                ]);
            }
        }

        $first = Incident::query()->orderBy('id')->first();
        if ($first) {
            SurveyData::query()->create([
                'incident_id' => $first->id,
                'surveyor_id' => $surveyorLuar->id,
                'vendor_name' => 'Surveyor Demo Sdn Bhd',
                'surveyor_name' => 'Surveyor Tapak Demo',
                'survey_date' => now()->subDay(),
                'survey_type' => 'Survey tapak & drone',
                'gps_coordinates' => ['lat' => $first->latitude, 'lng' => $first->longitude],
                'geojson_data' => [
                    'type' => 'FeatureCollection',
                    'features' => [[
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Point',
                            'coordinates' => [$first->longitude, $first->latitude],
                        ],
                        'properties' => ['label' => 'Titik survey'],
                    ]],
                ],
                'notes' => 'Contoh laporan surveyor (GeoJSON)',
                'technical_notes' => 'Kecondongan cerun anggaran 35° (demo).',
                'original_filename' => null,
                'review_status' => SurveyData::REVIEW_PENDING,
                'version' => 1,
                'parent_survey_id' => null,
            ]);
        }
    }
}
