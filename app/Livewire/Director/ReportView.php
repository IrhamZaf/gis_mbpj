<?php

namespace App\Livewire\Director;

use App\Models\Report;
use App\Services\Workflow\ReportWorkflowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Kelulusan Pengarah')]
class ReportView extends Component
{
    public Report $report;
    public string $remarks = '';

    public function mount(Report $report): void
    {
        $this->authorize('view', $report);
        $this->report = $report->load([
            'category', 'user', 'unit', 'attachments',
            'siteVisit.photos', 'siteVisit.ta',
            'latestEngineerVerification.engineer',
            'latestDirectorApproval.director',
            'workflowHistories.user',
        ]);
    }

    public function approve(): void
    {
        $this->authorize('approve', $this->report);
        app(ReportWorkflowService::class)->approveByDirector($this->report, Auth::user(), $this->remarks);
        session()->flash('message', 'Laporan diluluskan.');
        $this->redirect(route('director.reports'), navigate: false);
    }

    public function reject(): void
    {
        $this->authorize('approve', $this->report);
        $this->validate([
            'remarks' => 'required|string|min:5',
        ], [
            'remarks.required' => 'Sebab penolakan wajib diisi.',
        ]);
        app(ReportWorkflowService::class)->rejectByDirector($this->report, Auth::user(), $this->remarks);
        session()->flash('message', 'Laporan ditolak / dikembalikan.');
        $this->redirect(route('director.reports'), navigate: false);
    }

    public function render()
    {
        return view('livewire.director.report-view', [
            'canApprove' => Auth::user()->can('approve', $this->report),
        ]);
    }
}
