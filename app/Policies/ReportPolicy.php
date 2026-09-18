<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

/**
 * Cross-unit access: READ ALL, WRITE OWN UNIT.
 *
 * - view / download: any active staff role across units (drafts remain private to owner)
 * - update / delete / upload / site visit / verify: own unit + role rules
 * - approve: director / superadmin (global)
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

        // Drafts are private to the owning surveyor (not visible cross-unit)
        if ($report->status === 'draft') {
            return $user->isSurveyor() && (int) $report->user_id === (int) $user->id;
        }

        // Non-draft reports: readable by all staff roles (including other units)
        return $user->isSurveyor() || $user->isTa() || $user->isEngineer();
    }

    public function create(User $user): bool
    {
        return $user->isSurveyor() && $user->isActive() && $user->unit_id;
    }

    public function update(User $user, Report $report): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isSuperadmin()) {
            return $report->status === 'draft';
        }

        // Cross-unit write is never allowed
        if (! $user->belongsToSameUnit($report)) {
            return false;
        }

        if (! $user->isSurveyor()) {
            return false;
        }

        if ((int) $report->user_id !== (int) $user->id) {
            return false;
        }

        return $report->status === 'draft' && $report->workflow_status === null;
    }

    public function delete(User $user, Report $report): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isSuperadmin()) {
            return $report->status === 'draft';
        }

        if (! $user->belongsToSameUnit($report)) {
            return false;
        }

        return $user->isSurveyor()
            && (int) $report->user_id === (int) $user->id
            && $report->status === 'draft'
            && $report->workflow_status === null;
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
            // Own-unit TA: print when visit is underway / done
            // Other-unit TA: still may download when a visit record exists (read-only monitoring)
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

        // Surveyor / Engineer — download when past site visit or completed
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
