<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Report $report): bool
    {
        if ($user->isSuperadmin() || $user->isDirector()) {
            return true;
        }

        if (! $user->unit_id || $report->unit_id !== $user->unit_id) {
            return false;
        }

        if ($user->isSurveyor()) {
            return $report->user_id === $user->id;
        }

        if ($user->isTa() || $user->isEngineer()) {
            return $report->status !== 'draft';
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSurveyor() && $user->isActive() && $user->unit_id;
    }

    public function update(User $user, Report $report): bool
    {
        if (! $user->isSurveyor() || ! $user->isActive()) {
            return false;
        }

        if ($report->user_id !== $user->id) {
            return false;
        }

        return $report->status === 'draft' && $report->workflow_status === null;
    }

    public function startSiteVisit(User $user, Report $report): bool
    {
        return $user->isTa()
            && $user->isActive()
            && $user->unit_id === $report->unit_id
            && in_array($report->workflow_status, ['pending_site_visit', 'engineer_returned'], true);
    }

    public function manageSiteVisit(User $user, Report $report): bool
    {
        return $user->isTa()
            && $user->isActive()
            && $user->unit_id === $report->unit_id
            && in_array($report->workflow_status, ['site_visit_in_progress', 'engineer_returned'], true);
    }

    public function review(User $user, Report $report): bool
    {
        return $user->isEngineer()
            && $user->isActive()
            && $user->unit_id === $report->unit_id
            && in_array($report->workflow_status, ['pending_engineer_verification', 'director_rejected'], true);
    }

    public function approve(User $user, Report $report): bool
    {
        return ($user->isDirector() || $user->isSuperadmin())
            && $user->isActive()
            && $report->workflow_status === 'pending_director_approval';
    }

    public function downloadPdf(User $user, Report $report): bool
    {
        if (! in_array($report->workflow_status, ['approved'], true) && $report->status !== 'completed') {
            // Allow preview for roles in chain after site visit submitted
            if (! in_array($report->workflow_status, [
                'pending_engineer_verification',
                'engineer_verified',
                'pending_director_approval',
                'approved',
                'director_rejected',
            ], true)) {
                return false;
            }
        }

        return $this->view($user, $report);
    }
}
