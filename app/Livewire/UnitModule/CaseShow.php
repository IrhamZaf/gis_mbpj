<?php

namespace App\Livewire\UnitModule;

use App\Models\Report;
use App\Services\Workflow\ReportWorkflowService;
use App\Support\UnitModule;
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
        $report->loadMissing('unit');

        // Ensure URL unit slug matches report unit (prevent cross-unit URL confusion)
        $routeName = request()->route()?->getName() ?? '';
        foreach (UnitModule::UNIT_SLUGS as $slug => $code) {
            if (str_starts_with($routeName, $slug.'.') && $report->unit?->code && $report->unit->code !== $code) {
                abort(404);
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
        $this->authorize('update', $this->report);
        app(ReportWorkflowService::class)->submitReport($this->report->fresh(), Auth::user());
        session()->flash('message', __('app.case_submitted'));
        $this->report = $this->report->fresh([
            'category.attachmentTypes', 'unit', 'user', 'attachments.uploader', 'attachments.attachmentType',
            'siteVisit.ta', 'siteVisit.photos', 'workflowHistories.user',
        ]);
    }

    public function render()
    {
        $user = Auth::user();
        $progress = $this->report->requiredDocumentsProgress();
        $siteStatus = match ($this->report->workflow_status) {
            'pending_site_visit' => __('app.pending'),
            'site_visit_in_progress', 'engineer_returned' => __('app.in_progress'),
            'pending_engineer_verification', 'pending_director_approval', 'approved', 'engineer_verified' => __('app.completed'),
            'director_rejected' => __('app.completed'),
            default => __('app.pending'),
        };

        $unitCode = $this->report->unit?->code ?? 'SAL-CERUN';
        $canUpdate = $user->can('update', $this->report);
        $isReadOnly = ! $user->isSuperadmin()
            && ! $user->isDirector()
            && ! $user->belongsToSameUnit($this->report);

        return view('livewire.unit-module.case-show', [
            'progress' => $progress,
            'siteStatus' => $siteStatus,
            'histories' => $this->report->attachments()->where('is_current', false)
                ->orderByDesc('attachment_type_id')
                ->orderByDesc('version')
                ->get()
                ->groupBy('attachment_type_id'),
            'listUrl' => UnitModule::categoryRoute($unitCode, $this->report->category?->code ?? 'SINKHOLE'),
            'editUrl' => UnitModule::caseEditRoute($unitCode, $this->report),
            'dashboardUrl' => UnitModule::dashboardRoute($unitCode),
            'canUpdate' => $canUpdate,
            'isReadOnly' => $isReadOnly,
        ])->title($this->report->report_number);
    }
}
