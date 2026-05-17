<?php

namespace App\Livewire\Engineer;

use App\Models\Report;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Lihat Laporan')]
class ReportView extends Component
{
    public Report $report;

    public function mount(Report $report)
    {
        if ($report->status !== 'submitted') {
            abort(404);
        }
        $this->report = $report->load(['category', 'user', 'attachments']);
    }

    public function render()
    {
        return view('livewire.engineer.report-view');
    }
}
