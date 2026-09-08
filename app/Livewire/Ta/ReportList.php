<?php

namespace App\Livewire\Ta;

use App\Models\Report;
use App\Services\Workflow\ReportWorkflowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.master')]
#[Title('Lawatan Tapak')]
class ReportList extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function startVisit(int $reportId): void
    {
        $report = Report::findOrFail($reportId);
        $this->authorize('startSiteVisit', $report);
        app(ReportWorkflowService::class)->startSiteVisit($report, Auth::user());
        $this->redirect(route('ta.site-visits.form', $report), navigate: false);
    }

    public function render()
    {
        $user = Auth::user();

        $reports = Report::with(['user', 'category', 'siteVisit'])
            ->where('unit_id', $user->unit_id)
            ->forTaQueue()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('report_number', 'like', "%{$this->search}%")
                    ->orWhere('file_number', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('workflow_status', $this->filterStatus))
            ->orderByDesc('submitted_at')
            ->paginate(10);

        return view('livewire.ta.report-list', [
            'reports'  => $reports,
            'unitName' => $user->unit->name ?? '—',
        ]);
    }
}
