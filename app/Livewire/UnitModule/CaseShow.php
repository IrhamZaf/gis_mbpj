<?php

namespace App\Livewire\UnitModule;

use App\Models\Report;
use App\Models\Unit;
use App\Services\Workflow\ReportWorkflowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.master')]
class CaseShow extends Component
{
    public Report $report;

    public string $tab = 'overview';

    public function mount(Report $report): void
    {
        $this->authorize('view', $report);
        $unit = Unit::where('code', 'SAL-CERUN')->first();
        if ($unit && (int) $report->unit_id !== (int) $unit->id && ! Auth::user()->isSuperadmin() && ! Auth::user()->isDirector()) {
            // Still allow if user's unit matches report
            if ((int) Auth::user()->unit_id !== (int) $report->unit_id) {
                abort(403);
            }
        }
        $this->report = $report->load([
            'category.attachmentTypes',
            'unit',
            'user',
            'attachments.uploader',
            'attachments.attachmentType',
            'siteVisit.ta',
            'siteVisit.photos',
            'workflowHistories.user',
        ]);
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function submitCase(): void
    {
        app(ReportWorkflowService::class)->submitReport($this->report->fresh(), Auth::user());
        session()->flash('message', __('app.case_submitted'));
        $this->report = $this->report->fresh([
            'category.attachmentTypes', 'unit', 'user', 'attachments.uploader', 'attachments.attachmentType',
            'siteVisit.ta', 'siteVisit.photos', 'workflowHistories.user',
        ]);
    }

    public function render()
    {
        $progress = $this->report->requiredDocumentsProgress();
        $siteStatus = match ($this->report->workflow_status) {
            'pending_site_visit' => __('app.pending'),
            'site_visit_in_progress', 'engineer_returned' => __('app.in_progress'),
            'pending_engineer_verification', 'pending_director_approval', 'approved', 'engineer_verified' => __('app.completed'),
            'director_rejected' => __('app.completed'),
            default => __('app.pending'),
        };

        return view('livewire.unit-module.case-show', [
            'progress' => $progress,
            'siteStatus' => $siteStatus,
            'histories' => $this->report->attachments()->where('is_current', false)
                ->orderByDesc('attachment_type_id')
                ->orderByDesc('version')
                ->get()
                ->groupBy('attachment_type_id'),
        ])->title($this->report->report_number);
    }
}
