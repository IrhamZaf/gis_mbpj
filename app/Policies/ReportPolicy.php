<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

/**
 * Cross-unit access:
 * - Most staff: READ ALL, WRITE OWN UNIT
 * - Consultant: READ ALL, WRITE ALL UNITS (own reports only)
 * - Superadmin / Director: global as before
 */
class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canBrowseAllUnits();
    }

    public function view(User $user, Report $report): bool
    {
        if (! $user->canBrowseAllUnits()) {
            return false;
        }

        if ($user->isSuperadmin() || $user->isDirector()) {
            return true;
        }

        if ($report->status === 'draft') {
            return $user->isConsultant() && (int) $report->user_id === (int) $user->id;
        }

        return $user->isConsultant() || $user->isTa() || $user->isEngineer();
    }

    public function create(User $user): bool
    {
        return $user->isActive() && $user->isConsultant();
    }

    public function update(User $user, Report $report): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isSuperadmin()) {
            return $report->status === 'draft'
                || $report->workflow_status === 'pending_site_visit';
        }

        if (! $user->isConsultant() || (int) $report->user_id !== (int) $user->id) {
            return false;
        }

        if (! $user->canWriteUnit($report->unit_id)) {
            return false;
        }

        // Draft (not yet submitted) or still waiting for TA site visit
        if ($report->status === 'draft' && $report->workflow_status === null) {
            return true;
        }

        return $report->workflow_status === 'pending_site_visit';
    }

    public function delete(User $user, Report $report): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isSuperadmin()) {
            return $report->status === 'draft';
        }

        if (! $user->isConsultant() || (int) $report->user_id !== (int) $user->id) {
            return false;
        }

        return $report->status === 'draft' && $report->workflow_status === null;
    }

    public function uploadAttachment(User $user, Report $report): bool
    {
        return $this->update($user, $report);
    }

    public function startSiteVisit(User $user, Report $report): bool
    {
        return $user->isTa()
            && $user->isActive()
            && $user->belongsToSameUnit($report)
            && in_array($report->workflow_status, ['pending_site_visit', 'engineer_returned'], true);
    }

    public function manageSiteVisit(User $user, Report $report): bool
    {
        return $user->isTa()
            && $user->isActive()
            && $user->belongsToSameUnit($report)
            && in_array($report->workflow_status, ['site_visit_in_progress', 'engineer_returned'], true);
    }

    public function review(User $user, Report $report): bool
    {
        return $user->isEngineer()
            && $user->isActive()
            && $user->belongsToSameUnit($report)
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
        if (! $this->view($user, $report)) {
            return false;
        }

        if ($user->isSuperadmin() || $user->isDirector()) {
            return true;
        }

        if ($user->isTa()) {
            if ($user->belongsToSameUnit($report)) {
                return in_array($report->workflow_status, [
                    'site_visit_in_progress',
                    'engineer_returned',
                    'pending_engineer_verification',
                    'engineer_verified',
                    'pending_director_approval',
                    'approved',
                    'director_rejected',
                ], true) || $report->siteVisit !== null;
            }

            return $report->siteVisit !== null || in_array($report->workflow_status, [
                'pending_engineer_verification',
                'engineer_verified',
                'pending_director_approval',
                'approved',
                'director_rejected',
            ], true);
        }

        if ($report->status === 'completed' || $report->workflow_status === 'approved') {
            return true;
        }

        return in_array($report->workflow_status, [
            'pending_engineer_verification',
            'engineer_verified',
            'pending_director_approval',
            'approved',
            'director_rejected',
        ], true);
    }
}
