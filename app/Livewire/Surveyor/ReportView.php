<?php

namespace App\Livewire\Surveyor;

use App\Models\Report;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Lihat Laporan')]
class ReportView extends Component
{
    public Report $report;

    public function mount(Report $report): void
    {
        $this->authorize('view', $report);
        $this->report = $report->load(['category', 'user', 'unit', 'attachments', 'workflowHistories.user']);
    }

    public function render()
    {
        return view('livewire.surveyor.report-view');
    }
}
