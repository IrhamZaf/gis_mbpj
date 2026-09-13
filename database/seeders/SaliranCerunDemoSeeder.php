<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkflowHistory;
use Illuminate\Database\Seeder;

class SaliranCerunDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SaliranCerunCategorySeeder::class);
        $this->call(SaliranCerunStaffSeeder::class);

        $unit = Unit::where('code', 'SAL-CERUN')->firstOrFail();
        $sinkhole = ReportCategory::where('unit_id', $unit->id)->where('code', 'SINKHOLE')->firstOrFail();
        $cerun = ReportCategory::where('unit_id', $unit->id)->where('code', 'CERUN_RUNTUH')->firstOrFail();

        $surveyor = User::where('email', 'surveyor.saliran-cerun@mbsj.gov.my')->first()
            ?? User::where('role', 'surveyor')->where('unit_id', $unit->id)->first()
            ?? User::where('email', 'surveyor@mbsj.gov.my')->first();

        if (! $surveyor) {
            $this->command?->error('No surveyor found for Saliran & Cerun demo.');

            return;
        }

        // Remap existing demo rows onto unit categories
        Report::where('report_number', 'RPT-WF-SALIRAN-01')->update([
            'unit_id' => $unit->id,
            'category_id' => $sinkhole->id,
            'user_id' => $surveyor->id,
            'title' => 'Sinkhole di SS12 — Saliran & Cerun',
        ]);

        Report::where('report_number', 'RPT-WF-CERUN-01')->update([
            'unit_id' => $unit->id,
            'category_id' => $cerun->id,
            'user_id' => $surveyor->id,
            'title' => 'Cerun Runtuh di Ara Damansara',
        ]);

        $demos = [
            [
                'report_number' => 'SC-2026-0001',
                'category_id' => $sinkhole->id,
                'title' => 'Sinkhole berhampiran longkang SS15',
                'description' => 'Lubang sinkhole muncul selepas hujan lebat. Perlu siasatan teknikal dan lawatan tapak.',
                'status' => 'submitted',
                'workflow_status' => 'pending_site_visit',
                'latitude' => 3.0748,
                'longitude' => 101.5865,
                'location_name' => 'Jalan SS15/4, Subang Jaya',
                'address' => 'Jalan SS15/4, 47500 Subang Jaya',
                'file_number' => 'MBSJ/ENG/SC/2026/001',
                'submitted_at' => now()->subDays(2),
            ],
            [
                'report_number' => 'SC-2026-0002',
                'category_id' => $sinkhole->id,
                'title' => 'Sinkhole kecil di kawasan parkir USJ',
                'description' => 'Draf laporan sinkhole untuk semakan lampiran teknikal.',
                'status' => 'draft',
                'workflow_status' => null,
                'latitude' => 3.0622,
                'longitude' => 101.5911,
                'location_name' => 'USJ 1, Subang Jaya',
                'address' => 'Persiaran Tujuan, USJ 1',
                'file_number' => null,
                'submitted_at' => null,
            ],
            [
                'report_number' => 'SC-2026-0003',
                'category_id' => $sinkhole->id,
                'title' => 'Kemerosotan permukaan jalan — disyaki sinkhole',
                'description' => 'Permukaan jalan merosot; menunggu lawatan TA.',
                'status' => 'submitted',
                'workflow_status' => 'pending_site_visit',
                'latitude' => 3.0812,
                'longitude' => 101.5728,
                'location_name' => 'SS18, Subang Jaya',
                'address' => 'Jalan SS18/1',
                'file_number' => 'MBSJ/ENG/SC/2026/003',
                'submitted_at' => now()->subHours(18),
            ],
            [
                'report_number' => 'CR-2026-0001',
                'category_id' => $cerun->id,
                'title' => 'Cerun Runtuh tepi jalan Ara Damansara',
                'description' => 'Cerun menunjukkan tanda ketidakstabilan selepas hujan berterusan.',
                'status' => 'submitted',
                'workflow_status' => 'pending_site_visit',
                'latitude' => 3.1289,
                'longitude' => 101.5784,
                'location_name' => 'Ara Damansara, Subang Jaya',
                'address' => 'Persiaran Ara, Ara Damansara',
                'file_number' => 'MBSJ/ENG/CR/2026/001',
                'submitted_at' => now()->subDays(1),
            ],
            [
                'report_number' => 'CR-2026-0002',
                'category_id' => $cerun->id,
                'title' => 'Retakan cerun di kawasan kediaman',
                'description' => 'Retakan pada cerun belakang rumah — draf surveyor.',
                'status' => 'draft',
                'workflow_status' => null,
                'latitude' => 3.1105,
                'longitude' => 101.5602,
                'location_name' => 'Puchong Prima',
                'address' => 'Jalan Prima 3',
                'file_number' => null,
                'submitted_at' => null,
            ],
            [
                'report_number' => 'CR-2026-0003',
                'category_id' => $cerun->id,
                'title' => 'Longsoran cerun kecil di Bukit Indah',
                'description' => 'Longsoran cetek selepas hujan. Menunggu lawatan tapak TA.',
                'status' => 'submitted',
                'workflow_status' => 'pending_site_visit',
                'latitude' => 3.0455,
                'longitude' => 101.6055,
                'location_name' => 'Bukit Indah, Ampang',
                'address' => 'Jalan Indah 2/1',
                'file_number' => 'MBSJ/ENG/CR/2026/003',
                'submitted_at' => now()->subHours(8),
            ],
            [
                'report_number' => 'CR-2026-0004',
                'category_id' => $cerun->id,
                'title' => 'Cerun Runtuh berhampiran saliran USJ 9',
                'description' => 'Cerun runtuh menjejaskan saliran tepi jalan. Perlu semakan teknikal.',
                'status' => 'submitted',
                'workflow_status' => 'site_visit_in_progress',
                'latitude' => 3.0488,
                'longitude' => 101.5822,
                'location_name' => 'USJ 9, Subang Jaya',
                'address' => 'Persiaran Kewajipan, USJ 9',
                'file_number' => 'MBSJ/ENG/CR/2026/004',
                'submitted_at' => now()->subDays(3),
            ],
        ];

        foreach ($demos as $demo) {
            $report = Report::updateOrCreate(
                ['report_number' => $demo['report_number']],
                array_merge($demo, [
                    'user_id' => $surveyor->id,
                    'unit_id' => $unit->id,
                    'vendor_name' => 'ABC Survey Sdn Bhd',
                ])
            );

            if ($demo['status'] === 'submitted' && $demo['workflow_status'] === 'pending_site_visit') {
                $exists = WorkflowHistory::where('report_id', $report->id)
                    ->where('action', 'submit_report')
                    ->exists();
                if (! $exists) {
                    WorkflowHistory::create([
                        'report_id' => $report->id,
                        'user_id' => $surveyor->id,
                        'role' => $surveyor->role,
                        'action' => 'submit_report',
                        'from_status' => null,
                        'to_status' => 'pending_site_visit',
                        'remarks' => 'Demo case dihantar',
                        'ip_address' => '127.0.0.1',
                    ]);
                }
            }
        }

        $sinkCount = Report::where('unit_id', $unit->id)->where('category_id', $sinkhole->id)->count();
        $cerunCount = Report::where('unit_id', $unit->id)->where('category_id', $cerun->id)->count();

        $this->command?->info("Saliran & Cerun demo ready — Sinkhole: {$sinkCount}, Cerun Runtuh: {$cerunCount}");
    }
}
