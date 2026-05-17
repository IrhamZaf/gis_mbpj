<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $surveyor = User::where('email', 'surveyor@mbpj.gov.my')->first();
        if (!$surveyor) {
            $this->command?->warn('Surveyor user not found. Run DatabaseSeeder first.');

            return;
        }

        $categories = ReportCategory::all()->keyBy('slug');
        if ($categories->isEmpty()) {
            $this->command?->warn('No report categories found. Run DatabaseSeeder first.');

            return;
        }

        $reports = [
            [
                'report_number' => 'RPT-DEMO-0001',
                'category_id'   => $categories['sinkhole']->id,
                'title'         => 'Sinkhole di Jalan SS2/24',
                'description'   => 'Sinkhole berdiameter kira-kira 1.2m dikesan berhampiran longkang. Kawasan perlu diasingkan serta-merta.',
                'status'        => 'submitted',
                'latitude'      => 3.1182,
                'longitude'     => 101.6234,
                'location_name' => 'Persimpangan Jalan SS2/24, Petaling Jaya',
                'submitted_at'  => now()->subDays(5),
                'gis_data'      => [
                    'type'     => 'FeatureCollection',
                    'features' => [[
                        'type'       => 'Feature',
                        'properties' => [],
                        'geometry'   => [
                            'type'        => 'Polygon',
                            'coordinates' => [[
                                [101.6228, 3.1178],
                                [101.6240, 3.1178],
                                [101.6240, 3.1186],
                                [101.6228, 3.1186],
                                [101.6228, 3.1178],
                            ]],
                        ],
                    ]],
                ],
            ],
            [
                'report_number' => 'RPT-DEMO-0002',
                'category_id'   => $categories['cerun-tanah-runtuh']->id,
                'title'         => 'Cerun runtuh berhampiran Taman Jaya',
                'description'   => 'Cerun tanah di lereng bukit menunjukkan retakan dan pergerakan tanah selepas hujan lebat.',
                'status'        => 'submitted',
                'latitude'      => 3.1045,
                'longitude'     => 101.6521,
                'location_name' => 'Taman Jaya, Petaling Jaya',
                'submitted_at'  => now()->subDays(3),
            ],
            [
                'report_number' => 'RPT-DEMO-0003',
                'category_id'   => $categories['utiliti-bawah-tanah']->id,
                'title'         => 'Kebocoran paip bawah tanah SS3',
                'description'   => 'Air bertakung di jalan raya menunjukkan kemungkinan kebocoran paip utiliti bawah tanah.',
                'status'        => 'submitted',
                'latitude'      => 3.0821,
                'longitude'     => 101.6108,
                'location_name' => 'Jalan SS3/45, Petaling Jaya',
                'submitted_at'  => now()->subDays(2),
            ],
            [
                'report_number' => 'RPT-DEMO-0004',
                'category_id'   => $categories['sinkhole']->id,
                'title'         => 'Lubang jalan di Seksyen 14',
                'description'   => 'Lubang jalan sedalam 40cm di tengah lorong. Risiko kepada kenderaan dan penunggang motosikal.',
                'status'        => 'submitted',
                'latitude'      => 3.1127,
                'longitude'     => 101.6359,
                'location_name' => 'Jalan 14/29, Seksyen 14, PJ',
                'submitted_at'  => now()->subDay(),
            ],
            [
                'report_number' => 'RPT-DEMO-0005',
                'category_id'   => $categories['cerun-tanah-runtuh']->id,
                'title'         => 'Tanah runtuh di Bukit Gasing',
                'description'   => 'Hakisan tanah di lereng bukit berhampiran laluan pendaki. Kawasan perlu dipantau.',
                'status'        => 'submitted',
                'latitude'      => 3.0956,
                'longitude'     => 101.6589,
                'location_name' => 'Bukit Gasing, Petaling Jaya',
                'submitted_at'  => now()->subHours(12),
            ],
            [
                'report_number' => 'RPT-DEMO-0006',
                'category_id'   => $categories['utiliti-bawah-tanah']->id,
                'title'         => 'Kabel utiliti terdedah di Kelana Jaya',
                'description'   => 'Kabel utiliti terdedah akibat kerja penggalian tanpa penutupan semula yang betul.',
                'status'        => 'submitted',
                'latitude'      => 3.1068,
                'longitude'     => 101.5932,
                'location_name' => 'Lorong Kelana Jaya 1, Petaling Jaya',
                'submitted_at'  => now()->subHours(6),
            ],
            [
                'report_number' => 'RPT-DEMO-0007',
                'category_id'   => $categories['sinkhole']->id,
                'title'         => 'Kemerosotan tanah di Damansara Utama',
                'description'   => 'Kemerosotan tanah kecil dikesan berhampiran tapak binaan. Laporan awal untuk pemantauan.',
                'status'        => 'draft',
                'latitude'      => 3.1354,
                'longitude'     => 101.6215,
                'location_name' => 'Damansara Utama, Petaling Jaya',
                'submitted_at'  => null,
            ],
            [
                'report_number' => 'RPT-DEMO-0008',
                'category_id'   => $categories['cerun-tanah-runtuh']->id,
                'title'         => 'Cerun tidak stabil di Ara Damansara',
                'description'   => 'Cerun di tepi jalan menunjukkan tanda-tanda ketidakstabilan. Draf laporan untuk semakan lanjut.',
                'status'        => 'draft',
                'latitude'      => 3.1289,
                'longitude'     => 101.5784,
                'location_name' => 'Ara Damansara, Petaling Jaya',
                'submitted_at'  => null,
            ],
            [
                'report_number' => 'RPT-DEMO-0009',
                'category_id'   => $categories['utiliti-bawah-tanah']->id,
                'title'         => 'Saluran paip rosak di Bandar Sunway',
                'description'   => 'Saluran paip utama rosak menyebabkan air bertakung di kawasan komersial.',
                'status'        => 'submitted',
                'latitude'      => 3.0738,
                'longitude'     => 101.6065,
                'location_name' => 'Jalan PJS 11/15, Bandar Sunway',
                'submitted_at'  => now()->subDays(7),
            ],
            [
                'report_number' => 'RPT-DEMO-0010',
                'category_id'   => $categories['sinkhole']->id,
                'title'         => 'Retakan jalan di SS7',
                'description'   => 'Retakan panjang di permukaan jalan sepanjang 15 meter. Perlu penilaian kejuruteraan.',
                'status'        => 'submitted',
                'latitude'      => 3.1073,
                'longitude'     => 101.6067,
                'location_name' => 'Jalan SS7/13, Kelana Jaya',
                'submitted_at'  => now()->subDays(1),
            ],
        ];

        foreach ($reports as $data) {
            Report::updateOrCreate(
                ['report_number' => $data['report_number']],
                array_merge($data, ['user_id' => $surveyor->id])
            );
        }

        $this->command?->info('Seeded ' . count($reports) . ' demo reports with map coordinates.');
    }
}
