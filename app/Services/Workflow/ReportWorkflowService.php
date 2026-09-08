<?php

namespace App\Services\Workflow;

use App\Models\DirectorApproval;
use App\Models\EngineerVerification;
use App\Models\Report;
use App\Models\SiteVisit;
use App\Models\User;
use App\Models\WorkflowHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ReportWorkflowService
{
    public function submitReport(Report $report, User $actor): Report
    {
        $this->assertActive($actor);
        if (! $actor->isSurveyor() || $report->user_id !== $actor->id) {
            throw new AccessDeniedHttpException('Hanya surveyor pemilik laporan boleh menghantar.');
        }
        if (! in_array($report->status, ['draft'], true) && $report->workflow_status !== 'engineer_returned') {
            // Allow resubmit only from draft; engineer_returned is for TA corrections on site visit
            if ($report->status !== 'draft') {
                $this->fail('Laporan tidak boleh dihantar dari status semasa.');
            }
        }
        if ($report->status !== 'draft') {
            $this->fail('Hanya draf boleh dihantar.');
        }

        return DB::transaction(function () use ($report, $actor) {
            $from = $report->workflow_status;
            if (! $report->file_number) {
                $report->loadMissing('unit');
                $report->file_number = $report->generateFileNumber();
            }

            $report->update([
                'status'          => 'submitted',
                'workflow_status' => 'pending_site_visit',
                'submitted_at'    => now(),
            ]);

            $this->audit($report, $actor, 'submit_report', $from, 'pending_site_visit', 'Laporan dihantar oleh Surveyor');

            return $report->fresh();
        });
    }

    public function startSiteVisit(Report $report, User $actor): SiteVisit
    {
        $this->assertActive($actor);
        $this->assertTaSameUnit($actor, $report);
        $this->assertWorkflow($report, ['pending_site_visit', 'engineer_returned']);

        return DB::transaction(function () use ($report, $actor) {
            $from = $report->workflow_status;

            $visit = SiteVisit::firstOrCreate(
                ['report_id' => $report->id],
                [
                    'ta_user_id'     => $actor->id,
                    'file_number'    => $report->file_number,
                    'reference'      => 'MBSJ.SPB.PT.PPP(KEJ)-01.RK(01)',
                    'visit_date'     => now()->toDateString(),
                    'visit_time'     => now()->format('H:i:s'),
                    'ta_designation' => $actor->default_designation,
                    'status'         => 'draft',
                ]
            );

            if ($visit->ta_user_id !== $actor->id && $visit->isDraft()) {
                $visit->update(['ta_user_id' => $actor->id]);
            }

            $report->update(['workflow_status' => 'site_visit_in_progress']);
            $this->audit($report, $actor, 'start_site_visit', $from, 'site_visit_in_progress', 'Lawatan tapak dimulakan');

            return $visit->fresh();
        });
    }

    public function saveSiteVisitDraft(SiteVisit $visit, User $actor, array $data): SiteVisit
    {
        $this->assertActive($actor);
        $report = $visit->report;
        $this->assertTaSameUnit($actor, $report);
        $this->assertWorkflow($report, ['site_visit_in_progress', 'engineer_returned']);

        if ($visit->ta_user_id !== $actor->id) {
            throw new AccessDeniedHttpException('Hanya TA yang menjalankan lawatan boleh mengemas kini.');
        }

        $visit->update(array_merge($data, ['status' => 'draft']));

        return $visit->fresh();
    }

    public function submitSiteVisit(SiteVisit $visit, User $actor, array $data = []): SiteVisit
    {
        $this->assertActive($actor);
        $report = $visit->report()->first();
        $this->assertTaSameUnit($actor, $report);
        $this->assertWorkflow($report, ['site_visit_in_progress', 'engineer_returned']);

        if ($visit->ta_user_id !== $actor->id) {
            throw new AccessDeniedHttpException('Hanya TA yang menjalankan lawatan boleh menghantar.');
        }

        if (empty($data['laporan_pj_pjk'] ?? $visit->laporan_pj_pjk)) {
            $this->fail('Laporan PJ/PJK wajib diisi sebelum dihantar.');
        }

        return DB::transaction(function () use ($visit, $actor, $data, $report) {
            $from = $report->workflow_status;

            $visit->update(array_merge($data, [
                'status'         => 'submitted',
                'submitted_at'   => now(),
                'ta_designation' => $data['ta_designation'] ?? $actor->default_designation,
                'ta_signature'   => $data['ta_signature'] ?? $actor->name,
            ]));

            $report->update(['workflow_status' => 'pending_engineer_verification']);
            $this->audit($report, $actor, 'submit_site_visit', $from, 'pending_engineer_verification', 'Laporan lawatan tapak dihantar');

            return $visit->fresh();
        });
    }

    public function verifyByEngineer(Report $report, User $actor, string $remarks = '', ?string $signature = null): EngineerVerification
    {
        $this->assertActive($actor);
        $this->assertEngineerSameUnit($actor, $report);
        $this->assertWorkflow($report, ['pending_engineer_verification', 'director_rejected']);

        $visit = $report->siteVisit;
        if (! $visit || ! $visit->isSubmitted()) {
            $this->fail('Laporan lawatan tapak belum dihantar oleh TA.');
        }

        return DB::transaction(function () use ($report, $actor, $remarks, $signature) {
            $from = $report->workflow_status;

            $verification = EngineerVerification::create([
                'report_id'         => $report->id,
                'engineer_user_id'  => $actor->id,
                'remarks'           => $remarks,
                'decision'          => 'verified',
                'signature'         => $signature ?: $actor->name,
                'designation'       => $actor->default_designation,
                'verified_at'       => now(),
            ]);

            $report->update([
                'workflow_status' => 'pending_director_approval',
                'review_note'     => $remarks ?: null,
                'reviewed_at'     => now(),
                'reviewed_by'     => $actor->id,
            ]);

            // intermediate engineer_verified then auto to pending_director_approval (logged as one step to director)
            $this->audit($report, $actor, 'engineer_verify', $from, 'pending_director_approval', $remarks ?: 'Laporan disahkan Engineer');

            return $verification;
        });
    }

    public function returnByEngineer(Report $report, User $actor, string $remarks): EngineerVerification
    {
        $this->assertActive($actor);
        $this->assertEngineerSameUnit($actor, $report);
        $this->assertWorkflow($report, ['pending_engineer_verification', 'director_rejected']);

        if (trim($remarks) === '') {
            $this->fail('Sebab pemulangan wajib diisi.');
        }

        return DB::transaction(function () use ($report, $actor, $remarks) {
            $from = $report->workflow_status;

            $verification = EngineerVerification::create([
                'report_id'        => $report->id,
                'engineer_user_id' => $actor->id,
                'remarks'          => $remarks,
                'decision'         => 'returned',
                'signature'        => $actor->name,
                'designation'      => $actor->default_designation,
                'verified_at'      => now(),
            ]);

            if ($report->siteVisit) {
                $report->siteVisit->update(['status' => 'draft']);
            }

            $report->update([
                'workflow_status' => 'engineer_returned',
                'review_note'     => $remarks,
                'reviewed_at'     => now(),
                'reviewed_by'     => $actor->id,
            ]);

            $this->audit($report, $actor, 'engineer_return', $from, 'engineer_returned', $remarks);

            return $verification;
        });
    }

    public function approveByDirector(Report $report, User $actor, string $remarks = '', ?string $signature = null): DirectorApproval
    {
        $this->assertActive($actor);
        if (! $actor->isDirector() && ! $actor->isSuperadmin()) {
            throw new AccessDeniedHttpException('Hanya Pengarah boleh meluluskan.');
        }
        $this->assertWorkflow($report, ['pending_director_approval']);

        $latest = $report->latestEngineerVerification;
        if (! $latest || $latest->decision !== 'verified') {
            $this->fail('Laporan belum disahkan oleh Engineer.');
        }

        return DB::transaction(function () use ($report, $actor, $remarks, $signature) {
            $from = $report->workflow_status;

            $approval = DirectorApproval::create([
                'report_id'         => $report->id,
                'director_user_id'  => $actor->id,
                'decision'          => 'approved',
                'remarks'           => $remarks,
                'signature'         => $signature ?: $actor->name,
                'designation'       => $actor->default_designation,
                'approved_at'       => now(),
            ]);

            $report->update([
                'status'          => 'completed',
                'workflow_status' => 'approved',
            ]);

            $this->audit($report, $actor, 'director_approve', $from, 'approved', $remarks ?: 'Laporan diluluskan Pengarah');

            return $approval;
        });
    }

    public function rejectByDirector(Report $report, User $actor, string $remarks): DirectorApproval
    {
        $this->assertActive($actor);
        if (! $actor->isDirector() && ! $actor->isSuperadmin()) {
            throw new AccessDeniedHttpException('Hanya Pengarah boleh menolak.');
        }
        $this->assertWorkflow($report, ['pending_director_approval']);

        if (trim($remarks) === '') {
            $this->fail('Ulasan/sebab penolakan wajib diisi.');
        }

        return DB::transaction(function () use ($report, $actor, $remarks) {
            $from = $report->workflow_status;

            $approval = DirectorApproval::create([
                'report_id'        => $report->id,
                'director_user_id' => $actor->id,
                'decision'         => 'rejected',
                'remarks'          => $remarks,
                'signature'        => $actor->name,
                'designation'      => $actor->default_designation,
                'approved_at'      => now(),
            ]);

            $report->update(['workflow_status' => 'director_rejected']);
            $this->audit($report, $actor, 'director_reject', $from, 'director_rejected', $remarks);

            return $approval;
        });
    }

    public function resubmitAfterDirectorReject(Report $report, User $actor, string $remarks = ''): Report
    {
        $this->assertActive($actor);
        $this->assertEngineerSameUnit($actor, $report);
        $this->assertWorkflow($report, ['director_rejected']);

        return DB::transaction(function () use ($report, $actor, $remarks) {
            $from = $report->workflow_status;
            $report->update(['workflow_status' => 'pending_engineer_verification']);
            $this->audit($report, $actor, 'engineer_resubmit', $from, 'pending_engineer_verification', $remarks ?: 'Dihantar semula selepas penolakan Pengarah');

            return $report->fresh();
        });
    }

    private function audit(Report $report, User $actor, string $action, ?string $from, ?string $to, ?string $remarks): void
    {
        WorkflowHistory::create([
            'report_id'    => $report->id,
            'user_id'      => $actor->id,
            'role'         => $actor->role,
            'action'       => $action,
            'from_status'  => $from,
            'to_status'    => $to,
            'remarks'      => $remarks,
            'ip_address'   => request()?->ip(),
            'created_at'   => now(),
        ]);
    }

    private function assertActive(User $actor): void
    {
        if (! $actor->isActive()) {
            throw new AccessDeniedHttpException('Akaun dinyahaktifkan.');
        }
    }

    private function assertTaSameUnit(User $actor, Report $report): void
    {
        if (! $actor->isTa()) {
            throw new AccessDeniedHttpException('Hanya TA dibenarkan.');
        }
        if ($actor->unit_id !== $report->unit_id) {
            throw new AccessDeniedHttpException('TA hanya boleh mengurus laporan unit sendiri.');
        }
    }

    private function assertEngineerSameUnit(User $actor, Report $report): void
    {
        if (! $actor->isEngineer()) {
            throw new AccessDeniedHttpException('Hanya Engineer dibenarkan.');
        }
        if ($actor->unit_id !== $report->unit_id) {
            throw new AccessDeniedHttpException('Engineer hanya boleh mengurus laporan unit sendiri.');
        }
    }

    private function assertWorkflow(Report $report, array $allowed): void
    {
        if (! in_array($report->workflow_status, $allowed, true)) {
            $this->fail('Tindakan tidak dibenarkan untuk status workflow semasa: ' . ($report->workflow_status_label ?? '-'));
        }
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['workflow' => $message]);
    }
}
