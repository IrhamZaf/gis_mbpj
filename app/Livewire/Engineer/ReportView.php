<?php

namespace App\Livewire\Engineer;

use App\Models\Report;
use App\Services\Workflow\ReportWorkflowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Semak Laporan')]
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
        $this->remarks = $report->latestEngineerVerification?->remarks
            ?? $report->review_note
            ?? '';
    }

    public function verify(): void
    {
        $this->authorize('review', $this->report);
        app(ReportWorkflowService::class)->verifyByEngineer($this->report, Auth::user(), $this->remarks);
        session()->flash('message', 'Laporan disahkan. Dihantar kepada Pengarah.');
        $this->redirect(route('engineer.reports'), navigate: false);
    }

    public function returnToTa(): void
    {
        $this->authorize('review', $this->report);
        $this->validate([
            'remarks' => 'required|string|min:5',
        ], [
            'remarks.required' => 'Sebab pemulangan wajib diisi.',
        ]);
        app(ReportWorkflowService::class)->returnByEngineer($this->report, Auth::user(), $this->remarks);
        session()->flash('message', 'Laporan dikembalikan kepada TA.');
        $this->redirect(route('engineer.reports'), navigate: false);
    }

    public function resubmitToDirector(): void
    {
        $this->authorize('review', $this->report);
        app(ReportWorkflowService::class)->resubmitAfterDirectorReject($this->report, Auth::user(), $this->remarks);
        session()->flash('message', 'Laporan dihantar semula untuk proses pengesahan.');
        $this->report->refresh();
    }

    public function getDistanceKmProperty(): ?float
    {
        $visit = $this->report->siteVisit;
        if (! $visit?->latitude || ! $visit?->longitude || ! $this->report->latitude || ! $this->report->longitude) {
            return null;
        }
        $earth = 6371;
        $lat1 = (float) $this->report->latitude;
        $lon1 = (float) $this->report->longitude;
        $lat2 = (float) $visit->latitude;
        $lon2 = (float) $visit->longitude;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return round($earth * 2 * atan2(sqrt($a), sqrt(1 - $a)), 3);
    }

    public function render()
    {
        return view('livewire.engineer.report-view', [
            'canReview'  => Auth::user()->can('review', $this->report),
            'distanceKm' => $this->distanceKm,
        ]);
    }
}
