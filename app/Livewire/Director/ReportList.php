<?php

namespace App\Livewire\Director;

use App\Models\Report;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.master')]
#[Title('Kelulusan Laporan')]
class ReportList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = 'pending_director_approval';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $reports = Report::with(['unit', 'user', 'siteVisit.ta', 'latestEngineerVerification.engineer'])
            ->forDirectorQueue()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('file_number', 'like', "%{$this->search}%")
                    ->orWhere('report_number', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('workflow_status', $this->filterStatus))
            ->orderByDesc('updated_at')
            ->paginate(10);

        return view('livewire.director.report-list', compact('reports'));
    }
}
