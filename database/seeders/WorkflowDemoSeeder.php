<?php

namespace Database\Seeders;

use App\Models\DirectorApproval;
use App\Models\EngineerVerification;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\SiteVisit;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkflowHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo data to showcase multi-unit GIS + full approval workflow.
 */
class WorkflowDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UnitSeeder::class);

        $units = Unit::active()->orderBy('sort_order')->get()->keyBy('code');
        $jalan = $units['JLN'] ?? null;
        $saliranCerun = $units['SAL-CERUN'] ?? null;
        $structure = $units['STR'] ?? null;
        $me = $units['ME'] ?? null;

        if (! $jalan || ! $saliranCerun || ! $structure || ! $me) {
            $this->command?->error('Units missing. Run UnitSeeder first (need SAL-CERUN, JLN, STR, ME).');

            return;
        }

        $password = Hash::make('password');

        // ── Global accounts ────────────────────────────────
        $admin = User::updateOrCreate(
            ['email' => 'admin@mbsj.gov.my'],
            ['name' => 'Super Admin', 'password' => $password, 'role' => 'superadmin', 'unit_id' => null, 'status' => 'active', 'phone' => '03-8000-0001']
        );

        // Surveyor kekal akaun sedia ada (vendor) — jangan seed surveyor baharu per unit
        $surveyorJln = User::updateOrCreate(
            ['email' => 'surveyor@mbsj.gov.my'],
            ['name' => 'Ahmad Surveyor (Vendor)', 'password' => $password, 'role' => 'surveyor', 'unit_id' => $jalan->id, 'status' => 'active', 'phone' => '012-1111001']
        );

        $surveyorSalCerun = User::updateOrCreate(
            ['email' => 'surveyor.saliran-cerun@mbsj.gov.my'],
            ['name' => 'Surveyor Saliran & Cerun', 'password' => $password, 'role' => 'surveyor', 'unit_id' => $saliranCerun->id, 'status' => 'active', 'phone' => '012-1111002']
        );

        $director = User::updateOrCreate(
            ['email' => 'director@mbsj.gov.my'],
            ['name' => 'Pengarah Kejuruteraan', 'password' => $password, 'role' => 'director', 'unit_id' => null, 'status' => 'active', 'phone' => '03-8000-0099']
        );

        // ── TA + Engineer for the 4 active units ───────────
        $staffSeed = [
            'JLN' => ['slug' => null, 'ta' => 'Ali TA', 'eng' => 'Rahman Engineer', 'ta_phone' => '012-2222001', 'eng_phone' => '012-3333001'],
            'SAL-CERUN' => ['slug' => 'saliran-cerun', 'ta' => 'Fatimah TA', 'eng' => 'Kumar Engineer', 'ta_phone' => '012-2222002', 'eng_phone' => '012-3333002'],
            'STR' => ['slug' => 'structure', 'ta' => 'Wong TA', 'eng' => 'Azlan Engineer', 'ta_phone' => '012-2222003', 'eng_phone' => '012-3333003'],
            'ME' => ['slug' => 'me', 'ta' => 'Ravi TA', 'eng' => 'Mei Engineer', 'ta_phone' => '012-2222004', 'eng_phone' => '012-3333004'],
        ];

        $taByCode = [];
        $engByCode = [];
        $accountTable = [
            ['admin@mbsj.gov.my', 'superadmin', '-'],
            ['surveyor@mbsj.gov.my', 'surveyor', 'Jalan'],
            ['surveyor.saliran-cerun@mbsj.gov.my', 'surveyor', 'Saliran & Cerun'],
            ['director@mbsj.gov.my', 'director', '-'],
        ];

        foreach ($staffSeed as $code => $meta) {
            $unit = $units[$code] ?? null;
            if (! $unit) {
                continue;
            }

            $taEmail = $meta['slug'] ? "ta.{$meta['slug']}@mbsj.gov.my" : 'ta@mbsj.gov.my';
            $engEmail = $meta['slug'] ? "engineer.{$meta['slug']}@mbsj.gov.my" : 'engineer@mbsj.gov.my';

            $taByCode[$code] = User::updateOrCreate(
                ['email' => $taEmail],
                [
                    'name' => "{$meta['ta']} ({$unit->name})",
                    'password' => $password,
                    'role' => 'ta',
                    'unit_id' => $unit->id,
                    'status' => 'active',
                    'phone' => $meta['ta_phone'],
                ]
            );

            $engByCode[$code] = User::updateOrCreate(
                ['email' => $engEmail],
                [
                    'name' => "{$meta['eng']} ({$unit->name})",
                    'password' => $password,
                    'role' => 'engineer',
                    'unit_id' => $unit->id,
                    'status' => 'active',
                    'phone' => $meta['eng_phone'],
                ]
            );

            $accountTable[] = [$taEmail, 'ta', $unit->name];
            $accountTable[] = [$engEmail, 'engineer', $unit->name];
        }

        $taJln = $taByCode['JLN'];
        $engJln = $engByCode['JLN'];
        $taSalCerun = $taByCode['SAL-CERUN'] ?? null;
        $engSalCerun = $engByCode['SAL-CERUN'] ?? null;

        // Categories
        $cats = collect([
            ['slug' => 'sinkhole', 'name' => 'Sinkhole', 'description' => 'Laporan berkaitan sinkhole'],
            ['slug' => 'cerun-tanah-runtuh', 'name' => 'Cerun / Tanah Runtuh', 'description' => 'Laporan berkaitan cerun dan tanah runtuh'],
            ['slug' => 'utiliti-bawah-tanah', 'name' => 'Utiliti Bawah Tanah', 'description' => 'Laporan berkaitan utiliti bawah tanah'],
            ['slug' => 'jalan-rosak', 'name' => 'Jalan Rosak', 'description' => 'Retakan, lubang dan kerosakan permukaan jalan'],
            ['slug' => 'saliran-tertutup', 'name' => 'Saliran Tertutup', 'description' => 'Longkang / parit tersumbat'],
        ])->mapWithKeys(function ($c) {
            $cat = ReportCategory::updateOrCreate(
                ['slug' => $c['slug']],
                ['name' => $c['name'], 'description' => $c['description']]
            );

            return [$c['slug'] => $cat];
        });

        // Clear previous demo workflow records for re-seed idempotency on DEMO file numbers
        $demoNumbers = [
            'RPT-WF-DRAFT-01',
            'RPT-WF-PENDING-VISIT-01',
            'RPT-WF-VISIT-PROGRESS-01',
            'RPT-WF-PENDING-ENG-01',
            'RPT-WF-RETURNED-01',
            'RPT-WF-PENDING-DIR-01',
            'RPT-WF-APPROVED-01',
            'RPT-WF-REJECTED-01',
            'RPT-WF-SALIRAN-01',
            'RPT-WF-CERUN-01',
            'RPT-WF-STR-01',
            'RPT-WF-ME-01',
        ];

        $oldIds = Report::whereIn('report_number', $demoNumbers)->pluck('id');
        if ($oldIds->isNotEmpty()) {
            WorkflowHistory::whereIn('report_id', $oldIds)->delete();
            DirectorApproval::whereIn('report_id', $oldIds)->delete();
            EngineerVerification::whereIn('report_id', $oldIds)->delete();
            SiteVisit::whereIn('report_id', $oldIds)->delete();
            Report::whereIn('id', $oldIds)->delete();
        }

        // 1) DRAFT — surveyor still editing
        $draft = $this->makeReport([
            'report_number'   => 'RPT-WF-DRAFT-01',
            'file_number'     => null,
            'category_id'     => $cats['jalan-rosak']->id,
            'user_id'         => $surveyorJln->id,
            'unit_id'         => $jalan->id,
            'title'           => '[DRAFT] Retakan jalan SS15 belum dihantar',
            'description'     => 'Draf laporan retakan jalan sepanjang 8 meter di SS15. Belum lengkap lampiran.',
            'status'          => 'draft',
            'workflow_status' => null,
            'latitude'        => 3.0735,
            'longitude'       => 101.5852,
            'location_name'   => 'Jalan SS15/4, Subang Jaya',
            'vendor_name'     => 'ABC Survey Sdn Bhd',
            'submitted_at'    => null,
        ]);

        // 2) PENDING SITE VISIT — waiting for TA
        $pendingVisit = $this->makeReport([
            'report_number'   => 'RPT-WF-PENDING-VISIT-01',
            'file_number'     => 'MBSJ/ENG/JLN/2026/101',
            'category_id'     => $cats['sinkhole']->id,
            'user_id'         => $surveyorJln->id,
            'unit_id'         => $jalan->id,
            'title'           => 'Sinkhole kecil di Jalan SS2/24',
            'description'     => 'Sinkhole diameter ~0.8m berhampiran longkang. Perlu lawatan TA.',
            'status'          => 'submitted',
            'workflow_status' => 'pending_site_visit',
            'latitude'        => 3.1182,
            'longitude'       => 101.6234,
            'location_name'   => 'Persimpangan Jalan SS2/24, Subang Jaya',
            'vendor_name'     => 'ABC Survey Sdn Bhd',
            'submitted_at'    => now()->subDays(1),
        ]);
        $this->history($pendingVisit, $surveyorJln, 'submit_report', null, 'pending_site_visit', 'Laporan dihantar oleh Surveyor', now()->subDays(1));

        // 3) SITE VISIT IN PROGRESS
        $inProgress = $this->makeReport([
            'report_number'   => 'RPT-WF-VISIT-PROGRESS-01',
            'file_number'     => 'MBSJ/ENG/JLN/2026/102',
            'category_id'     => $cats['jalan-rosak']->id,
            'user_id'         => $surveyorJln->id,
            'unit_id'         => $jalan->id,
            'title'           => 'Lubang jalan di USJ 1 — lawatan sedang dijalankan',
            'description'     => 'Lubang jalan sedalam 15cm di lorong kiri. TA sedang buat lawatan.',
            'status'          => 'submitted',
            'workflow_status' => 'site_visit_in_progress',
            'latitude'        => 3.0648,
            'longitude'       => 101.5921,
            'location_name'   => 'Persiaran Tujuan, USJ 1',
            'vendor_name'     => 'GeoMap Ventures',
            'submitted_at'    => now()->subDays(2),
        ]);
        $this->history($inProgress, $surveyorJln, 'submit_report', null, 'pending_site_visit', 'Laporan dihantar', now()->subDays(2));
        $this->history($inProgress, $taJln, 'start_site_visit', 'pending_site_visit', 'site_visit_in_progress', 'Lawatan tapak dimulakan', now()->subHours(5));
        SiteVisit::create([
            'report_id'      => $inProgress->id,
            'ta_user_id'     => $taJln->id,
            'file_number'    => $inProgress->file_number,
            'reference'      => 'MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)',
            'visit_date'     => now()->toDateString(),
            'visit_time'     => '10:30:00',
            'latitude'       => 3.0649,
            'longitude'      => 101.5923,
            'gps_accuracy'   => 8.5,
            'laporan_pj_pjk' => "Pemerhatian awal:\n- Lubang disahkan wujud di lorong kiri.\n- Laluan masih boleh dilalui dengan berhati-hati.\n- Menunggu foto tambahan dan ukuran mendalam.",
            'ta_designation' => 'Pembantu Teknik',
            'ta_signature'   => $taJln->name,
            'status'         => 'draft',
        ]);

        // 4) PENDING ENGINEER VERIFICATION
        $pendingEng = $this->makeReport([
            'report_number'   => 'RPT-WF-PENDING-ENG-01',
            'file_number'     => 'MBSJ/ENG/JLN/2026/103',
            'category_id'     => $cats['utiliti-bawah-tanah']->id,
            'user_id'         => $surveyorJln->id,
            'unit_id'         => $jalan->id,
            'title'           => 'Kebocoran paip bawah tanah SS3',
            'description'     => 'Air bertakung menunjukkan kemungkinan kebocoran paip utiliti.',
            'status'          => 'submitted',
            'workflow_status' => 'pending_engineer_verification',
            'latitude'        => 3.0821,
            'longitude'       => 101.6108,
            'location_name'   => 'Jalan SS3/45, Subang Jaya',
            'vendor_name'     => 'ABC Survey Sdn Bhd',
            'submitted_at'    => now()->subDays(4),
        ]);
        $this->history($pendingEng, $surveyorJln, 'submit_report', null, 'pending_site_visit', 'Laporan dihantar', now()->subDays(4));
        $this->history($pendingEng, $taJln, 'start_site_visit', 'pending_site_visit', 'site_visit_in_progress', 'Lawatan dimulakan', now()->subDays(3)->setTime(9, 0));
        $this->history($pendingEng, $taJln, 'submit_site_visit', 'site_visit_in_progress', 'pending_engineer_verification', 'Laporan lawatan dihantar', now()->subDays(3)->setTime(11, 20));
        SiteVisit::create([
            'report_id'      => $pendingEng->id,
            'ta_user_id'     => $taJln->id,
            'file_number'    => $pendingEng->file_number,
            'reference'      => 'MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)',
            'visit_date'     => now()->subDays(3)->toDateString(),
            'visit_time'     => '10:15:00',
            'latitude'       => 3.0823,
            'longitude'      => 101.6110,
            'gps_accuracy'   => 6.2,
            'laporan_pj_pjk' => "LAPORAN PJ/PJK\n\n1. Keadaan sebenar: Air bertakung di bahu jalan SS3/45.\n2. Punca digambarkan berkaitan utiliti bawah tanah.\n3. Cadangan: Semakan bersama unit utiliti & tutup sementara kawasan.\n4. Risiko: Gelinciran kenderaan semasa hujan.",
            'visit_notes'    => 'Keadaan jalan basah; ambil gambar keseluruhan & close-up.',
            'ta_designation' => 'Pembantu Teknik',
            'ta_signature'   => $taJln->name,
            'status'         => 'submitted',
            'submitted_at'   => now()->subDays(3)->setTime(11, 20),
        ]);

        // 5) ENGINEER RETURNED — needs TA correction
        $returned = $this->makeReport([
            'report_number'   => 'RPT-WF-RETURNED-01',
            'file_number'     => 'MBSJ/ENG/JLN/2026/104',
            'category_id'     => $cats['jalan-rosak']->id,
            'user_id'         => $surveyorJln->id,
            'unit_id'         => $jalan->id,
            'title'           => 'Retakan jalan SS7 — dikembalikan untuk pembetulan',
            'description'     => 'Retakan panjang di permukaan jalan SS7. Foto lawatan belum lengkap.',
            'status'          => 'submitted',
            'workflow_status' => 'engineer_returned',
            'latitude'        => 3.1073,
            'longitude'       => 101.6067,
            'location_name'   => 'Jalan SS7/13, Kelana Jaya',
            'vendor_name'     => 'GeoMap Ventures',
            'submitted_at'    => now()->subDays(6),
            'review_note'     => 'Sila kemaskini foto keadaan sebenar dan lengkapkan ukuran kedalaman retakan.',
            'reviewed_at'     => now()->subDays(1),
            'reviewed_by'     => $engJln->id,
        ]);
        $this->history($returned, $surveyorJln, 'submit_report', null, 'pending_site_visit', 'Laporan dihantar', now()->subDays(6));
        $this->history($returned, $taJln, 'submit_site_visit', 'site_visit_in_progress', 'pending_engineer_verification', 'Lawatan dihantar', now()->subDays(2));
        $this->history($returned, $engJln, 'engineer_return', 'pending_engineer_verification', 'engineer_returned', 'Sila kemaskini foto keadaan sebenar dan lengkapkan ukuran kedalaman retakan.', now()->subDays(1));
        SiteVisit::create([
            'report_id'      => $returned->id,
            'ta_user_id'     => $taJln->id,
            'file_number'    => $returned->file_number,
            'reference'      => 'MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)',
            'visit_date'     => now()->subDays(2)->toDateString(),
            'visit_time'     => '14:00:00',
            'latitude'       => 3.1074,
            'longitude'      => 101.6068,
            'gps_accuracy'   => 10.0,
            'laporan_pj_pjk' => "Retakan sepanjang kira-kira 15m dikesan. Foto close-up belum lengkap — perlu semakan semula.",
            'ta_designation' => 'Pembantu Teknik',
            'ta_signature'   => $taJln->name,
            'status'         => 'draft',
            'submitted_at'   => null,
        ]);
        EngineerVerification::create([
            'report_id'        => $returned->id,
            'engineer_user_id' => $engJln->id,
            'remarks'          => 'Sila kemaskini foto keadaan sebenar dan lengkapkan ukuran kedalaman retakan.',
            'decision'         => 'returned',
            'signature'        => $engJln->name,
            'designation'      => 'Jurutera',
            'verified_at'      => now()->subDays(1),
        ]);

        // 6) PENDING DIRECTOR APPROVAL
        $pendingDir = $this->makeReport([
            'report_number'   => 'RPT-WF-PENDING-DIR-01',
            'file_number'     => 'MBSJ/ENG/JLN/2026/105',
            'category_id'     => $cats['sinkhole']->id,
            'user_id'         => $surveyorJln->id,
            'unit_id'         => $jalan->id,
            'title'           => 'Kemerosotan tanah berhampiran SS14 — menunggu kelulusan',
            'description'     => 'Kemerosotan tanah kecil. Telah disahkan Engineer, menunggu Pengarah.',
            'status'          => 'submitted',
            'workflow_status' => 'pending_director_approval',
            'latitude'        => 3.0912,
            'longitude'       => 101.5987,
            'location_name'   => 'Jalan SS14/1, Subang Jaya',
            'vendor_name'     => 'ABC Survey Sdn Bhd',
            'submitted_at'    => now()->subDays(8),
            'reviewed_at'     => now()->subDays(1)->setTime(9, 10),
            'reviewed_by'     => $engJln->id,
        ]);
        $this->history($pendingDir, $surveyorJln, 'submit_report', null, 'pending_site_visit', 'Laporan dihantar', now()->subDays(8));
        $this->history($pendingDir, $taJln, 'submit_site_visit', 'site_visit_in_progress', 'pending_engineer_verification', 'Lawatan dihantar', now()->subDays(3));
        $this->history($pendingDir, $engJln, 'engineer_verify', 'pending_engineer_verification', 'pending_director_approval', 'Kerja lapangan dan lawatan adalah konsisten. Disyorkan untuk diluluskan.', now()->subDays(1)->setTime(9, 10));
        SiteVisit::create([
            'report_id'      => $pendingDir->id,
            'ta_user_id'     => $taJln->id,
            'file_number'    => $pendingDir->file_number,
            'reference'      => 'MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)',
            'visit_date'     => now()->subDays(3)->toDateString(),
            'visit_time'     => '09:45:00',
            'latitude'       => 3.0913,
            'longitude'      => 101.5989,
            'gps_accuracy'   => 5.5,
            'laporan_pj_pjk' => "Pemerhatian:\n- Kemerosotan tanah kecil (~0.4m) berhampiran bahu jalan.\n- Tiada ancaman segera kepada struktur bangunan.\n- Cadangan pemantauan berkala & penutupan sementara.",
            'ta_designation' => 'Pembantu Teknik',
            'ta_signature'   => $taJln->name,
            'status'         => 'submitted',
            'submitted_at'   => now()->subDays(3),
        ]);
        EngineerVerification::create([
            'report_id'        => $pendingDir->id,
            'engineer_user_id' => $engJln->id,
            'remarks'          => 'Kerja lapangan dan lawatan adalah konsisten. Disyorkan untuk diluluskan.',
            'decision'         => 'verified',
            'signature'        => $engJln->name,
            'designation'      => 'Jurutera',
            'verified_at'      => now()->subDays(1)->setTime(9, 10),
        ]);

        // 7) APPROVED / COMPLETED
        $approved = $this->makeReport([
            'report_number'   => 'RPT-WF-APPROVED-01',
            'file_number'     => 'MBSJ/ENG/JLN/2026/106',
            'category_id'     => $cats['jalan-rosak']->id,
            'user_id'         => $surveyorJln->id,
            'unit_id'         => $jalan->id,
            'title'           => 'Penyelenggaraan permukaan jalan USJ 9 — DILULUSKAN',
            'description'     => 'Kerja penyelenggaraan permukaan jalan telah dilawat, disahkan dan diluluskan.',
            'status'          => 'completed',
            'workflow_status' => 'approved',
            'latitude'        => 3.0489,
            'longitude'       => 101.6012,
            'location_name'   => 'Persiaran Kewajipan, USJ 9',
            'vendor_name'     => 'ABC Survey Sdn Bhd',
            'submitted_at'    => now()->subDays(14),
            'reviewed_at'     => now()->subDays(5),
            'reviewed_by'     => $engJln->id,
        ]);
        $this->history($approved, $surveyorJln, 'submit_report', null, 'pending_site_visit', 'Laporan dihantar', now()->subDays(14));
        $this->history($approved, $taJln, 'submit_site_visit', 'site_visit_in_progress', 'pending_engineer_verification', 'Lawatan dihantar', now()->subDays(10));
        $this->history($approved, $engJln, 'engineer_verify', 'pending_engineer_verification', 'pending_director_approval', 'Disahkan lengkap.', now()->subDays(5));
        $this->history($approved, $director, 'director_approve', 'pending_director_approval', 'approved', 'Diluluskan untuk rekod rasmi.', now()->subDays(4)->setTime(14, 30));
        SiteVisit::create([
            'report_id'      => $approved->id,
            'ta_user_id'     => $taJln->id,
            'file_number'    => $approved->file_number,
            'reference'      => 'MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)',
            'visit_date'     => now()->subDays(10)->toDateString(),
            'visit_time'     => '11:00:00',
            'latitude'       => 3.0490,
            'longitude'      => 101.6014,
            'gps_accuracy'   => 4.8,
            'laporan_pj_pjk' => "Kerja penyelenggaraan telah dilaksanakan mengikut spesifikasi.\nPermukaan jalan dalam keadaan memuaskan selepas kerja tampalan.",
            'ta_designation' => 'Pembantu Teknik',
            'ta_signature'   => $taJln->name,
            'status'         => 'submitted',
            'submitted_at'   => now()->subDays(10),
        ]);
        EngineerVerification::create([
            'report_id'        => $approved->id,
            'engineer_user_id' => $engJln->id,
            'remarks'          => 'Disahkan lengkap. Tiada isu teknikal.',
            'decision'         => 'verified',
            'signature'        => $engJln->name,
            'designation'      => 'Jurutera',
            'verified_at'      => now()->subDays(5),
        ]);
        DirectorApproval::create([
            'report_id'        => $approved->id,
            'director_user_id' => $director->id,
            'decision'         => 'approved',
            'remarks'          => 'Diluluskan untuk rekod rasmi Jabatan Kejuruteraan.',
            'signature'        => $director->name,
            'designation'      => 'Pengarah Kejuruteraan',
            'approved_at'      => now()->subDays(4)->setTime(14, 30),
        ]);

        // 8) DIRECTOR REJECTED
        $rejected = $this->makeReport([
            'report_number'   => 'RPT-WF-REJECTED-01',
            'file_number'     => 'MBSJ/ENG/JLN/2026/107',
            'category_id'     => $cats['utiliti-bawah-tanah']->id,
            'user_id'         => $surveyorJln->id,
            'unit_id'         => $jalan->id,
            'title'           => 'Utiliti terdedah SS18 — ditolak Pengarah',
            'description'     => 'Kabel utiliti terdedah. Ditolak kerana dokumentasi sokongan tidak mencukupi.',
            'status'          => 'submitted',
            'workflow_status' => 'director_rejected',
            'latitude'        => 3.0551,
            'longitude'       => 101.5789,
            'location_name'   => 'Jalan SS18/1, Subang Jaya',
            'vendor_name'     => 'GeoMap Ventures',
            'submitted_at'    => now()->subDays(12),
            'reviewed_at'     => now()->subDays(4),
            'reviewed_by'     => $engJln->id,
        ]);
        $this->history($rejected, $surveyorJln, 'submit_report', null, 'pending_site_visit', 'Laporan dihantar', now()->subDays(12));
        $this->history($rejected, $taJln, 'submit_site_visit', 'site_visit_in_progress', 'pending_engineer_verification', 'Lawatan dihantar', now()->subDays(7));
        $this->history($rejected, $engJln, 'engineer_verify', 'pending_engineer_verification', 'pending_director_approval', 'Disahkan untuk semakan Pengarah.', now()->subDays(4));
        $this->history($rejected, $director, 'director_reject', 'pending_director_approval', 'director_rejected', 'Dokumen sokongan dan pelan tapak tidak mencukupi. Sila lengkapkan.', now()->subDays(3));
        SiteVisit::create([
            'report_id'      => $rejected->id,
            'ta_user_id'     => $taJln->id,
            'file_number'    => $rejected->file_number,
            'reference'      => 'MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)',
            'visit_date'     => now()->subDays(7)->toDateString(),
            'visit_time'     => '15:20:00',
            'latitude'       => 3.0552,
            'longitude'      => 101.5790,
            'gps_accuracy'   => 7.1,
            'laporan_pj_pjk' => "Kabel utiliti terdedah di tepi jalan. Pelan tapak masih ringkas.",
            'ta_designation' => 'Pembantu Teknik',
            'ta_signature'   => $taJln->name,
            'status'         => 'submitted',
            'submitted_at'   => now()->subDays(7),
        ]);
        EngineerVerification::create([
            'report_id'        => $rejected->id,
            'engineer_user_id' => $engJln->id,
            'remarks'          => 'Disahkan untuk semakan Pengarah.',
            'decision'         => 'verified',
            'signature'        => $engJln->name,
            'designation'      => 'Jurutera',
            'verified_at'      => now()->subDays(4),
        ]);
        DirectorApproval::create([
            'report_id'        => $rejected->id,
            'director_user_id' => $director->id,
            'decision'         => 'rejected',
            'remarks'          => 'Dokumen sokongan dan pelan tapak tidak mencukupi. Sila lengkapkan.',
            'signature'        => $director->name,
            'designation'      => 'Pengarah Kejuruteraan',
            'approved_at'      => now()->subDays(3),
        ]);

        // 9) Saliran & Cerun — unit isolation demo (use unit categories)
        $sinkholeUnitCat = ReportCategory::where('unit_id', $saliranCerun->id)->where('code', 'SINKHOLE')->first();
        $cerunUnitCat = ReportCategory::where('unit_id', $saliranCerun->id)->where('code', 'CERUN_RUNTUH')->first();

        if ($saliranCerun && $surveyorSalCerun && $taSalCerun && $engSalCerun && $sinkholeUnitCat) {
            $saliranRpt = $this->makeReport([
                'report_number'   => 'RPT-WF-SALIRAN-01',
                'file_number'     => 'MBSJ/ENG/SC/2026/201',
                'category_id'     => $sinkholeUnitCat->id,
                'user_id'         => $surveyorSalCerun->id,
                'unit_id'         => $saliranCerun->id,
                'title'           => 'Sinkhole di SS12 — Saliran & Cerun',
                'description'     => 'Lubang sinkhole muncul selepas hujan. Data unit Saliran & Cerun sahaja.',
                'status'          => 'submitted',
                'workflow_status' => 'pending_engineer_verification',
                'latitude'        => 3.0955,
                'longitude'       => 101.6155,
                'location_name'   => 'Jalan SS12/1, Subang Jaya',
                'vendor_name'     => 'DrainTech Survey',
                'submitted_at'    => now()->subDays(2),
            ]);
            $this->history($saliranRpt, $surveyorSalCerun, 'submit_report', null, 'pending_site_visit', 'Laporan dihantar', now()->subDays(2));
            $this->history($saliranRpt, $taSalCerun, 'submit_site_visit', 'site_visit_in_progress', 'pending_engineer_verification', 'Lawatan Saliran & Cerun dihantar', now()->subDay());
            SiteVisit::create([
                'report_id'      => $saliranRpt->id,
                'ta_user_id'     => $taSalCerun->id,
                'file_number'    => $saliranRpt->file_number,
                'reference'      => 'MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)',
                'visit_date'     => now()->subDay()->toDateString(),
                'visit_time'     => '08:40:00',
                'latitude'       => 3.0956,
                'longitude'      => 101.6156,
                'gps_accuracy'   => 5.0,
                'laporan_pj_pjk' => "Sinkhole dikesan berhampiran longkang. Perlu tindakan segera.",
                'ta_designation' => 'Pembantu Teknik',
                'ta_signature'   => $taSalCerun->name,
                'status'         => 'submitted',
                'submitted_at'   => now()->subDay(),
            ]);
        }

        // 10) Cerun Runtuh under Saliran & Cerun — pending visit
        if ($saliranCerun && $surveyorSalCerun && $cerunUnitCat) {
            $cerunRpt = $this->makeReport([
                'report_number'   => 'RPT-WF-CERUN-01',
                'file_number'     => 'MBSJ/ENG/CR/2026/301',
                'category_id'     => $cerunUnitCat->id,
                'user_id'         => $surveyorSalCerun->id,
                'unit_id'         => $saliranCerun->id,
                'title'           => 'Cerun Runtuh di Ara Damansara',
                'description'     => 'Cerun tepi jalan menunjukkan tanda ketidakstabilan selepas hujan. Menunggu lawatan TA.',
                'status'          => 'submitted',
                'workflow_status' => 'pending_site_visit',
                'latitude'        => 3.1289,
                'longitude'       => 101.5784,
                'location_name'   => 'Ara Damansara, Subang Jaya',
                'vendor_name'     => 'ABC Survey Sdn Bhd',
                'submitted_at'    => now()->subHours(10),
            ]);
            $this->history($cerunRpt, $surveyorSalCerun, 'submit_report', null, 'pending_site_visit', 'Laporan Cerun dihantar', now()->subHours(10));
        }

        // 11) Demo reports for Structure + M&E
        $extraUnitDemos = [
            'STR' => [
                'number' => 'RPT-WF-STR-01',
                'file' => 'MBSJ/ENG/STR/2026/401',
                'cat' => 'jalan-rosak',
                'title' => 'Retakan struktur jejambat USJ — Unit Structure',
                'desc' => 'Retakan kecil pada struktur jejambat. Menunggu lawatan TA Structure.',
                'lat' => 3.0712, 'lng' => 101.5888,
                'loc' => 'Jejambat USJ, Subang Jaya',
                'workflow' => 'pending_site_visit',
            ],
            'ME' => [
                'number' => 'RPT-WF-ME-01',
                'file' => 'MBSJ/ENG/ME/2026/501',
                'cat' => 'utiliti-bawah-tanah',
                'title' => 'Lampu jalan rosak SS15 — Unit M&E',
                'desc' => 'Beberapa tiang lampu jalan tidak berfungsi. Menunggu semakan TA M&E.',
                'lat' => 3.0741, 'lng' => 101.5861,
                'loc' => 'Jalan SS15/2, Subang Jaya',
                'workflow' => 'pending_site_visit',
            ],
        ];

        foreach ($extraUnitDemos as $code => $demo) {
            $unit = $units[$code] ?? null;
            if (! $unit) {
                continue;
            }

            $status = $demo['status'] ?? 'submitted';
            $rpt = $this->makeReport([
                'report_number'   => $demo['number'],
                'file_number'     => $status === 'draft' ? null : $demo['file'],
                'category_id'     => $cats[$demo['cat']]->id,
                'user_id'         => $surveyorJln->id,
                'unit_id'         => $unit->id,
                'title'           => $demo['title'],
                'description'     => $demo['desc'],
                'status'          => $status,
                'workflow_status' => $demo['workflow'],
                'latitude'        => $demo['lat'],
                'longitude'       => $demo['lng'],
                'location_name'   => $demo['loc'],
                'vendor_name'     => 'ABC Survey Sdn Bhd',
                'submitted_at'    => $status === 'draft' ? null : now()->subDays(1),
            ]);

            if ($status !== 'draft') {
                $this->history($rpt, $surveyorJln, 'submit_report', null, 'pending_site_visit', 'Laporan dihantar', now()->subDays(1));
            }

            if (($demo['workflow'] ?? null) === 'pending_engineer_verification' && isset($taByCode[$code])) {
                $this->history($rpt, $taByCode[$code], 'submit_site_visit', 'site_visit_in_progress', 'pending_engineer_verification', 'Lawatan dihantar', now()->subHours(6));
                SiteVisit::create([
                    'report_id'      => $rpt->id,
                    'ta_user_id'     => $taByCode[$code]->id,
                    'file_number'    => $rpt->file_number,
                    'reference'      => 'MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)',
                    'visit_date'     => now()->subDay()->toDateString(),
                    'visit_time'     => '09:00:00',
                    'latitude'       => $demo['lat'],
                    'longitude'      => $demo['lng'],
                    'gps_accuracy'   => 6.0,
                    'laporan_pj_pjk' => 'Pemerhatian lapangan unit '.$unit->name.'.',
                    'ta_designation' => 'Pembantu Teknik',
                    'ta_signature'   => $taByCode[$code]->name,
                    'status'         => 'submitted',
                    'submitted_at'   => now()->subHours(6),
                ]);
            }
        }

        $this->command?->info('Workflow demo seeded.');
        $this->command?->table(['Email', 'Role', 'Unit'], $accountTable);
        $this->command?->info('Password semua: password');
    }

    private function makeReport(array $data): Report
    {
        return Report::create(array_merge([
            'gis_data' => null,
        ], $data));
    }

    private function history(
        Report $report,
        User $user,
        string $action,
        ?string $from,
        ?string $to,
        string $remarks,
        $at
    ): void {
        WorkflowHistory::create([
            'report_id'   => $report->id,
            'user_id'     => $user->id,
            'role'        => $user->role,
            'action'      => $action,
            'from_status' => $from,
            'to_status'   => $to,
            'remarks'     => $remarks,
            'ip_address'  => '127.0.0.1',
            'created_at'  => $at,
        ]);
    }
}
