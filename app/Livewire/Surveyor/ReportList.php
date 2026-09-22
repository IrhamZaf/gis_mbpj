<?php

namespace App\Livewire\Surveyor;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.master')]
#[Title('Senarai Laporan')]
class ReportList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        // Consultant: own reports across all units
        $reports = Report::with(['category', 'unit'])
            ->where('user_id', $user->id)
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('report_number', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, function ($q) {
                if (in_array($this->filterStatus, ['draft', 'submitted', 'completed'], true)) {
                    $q->where('status', $this->filterStatus);
                } else {
                    $q->where('workflow_status', $this->filterStatus);
                }
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.surveyor.report-list', [
            'reports' => $reports,
            'unitName' => __('app.role_consultant'),
        ]);
    }
}
