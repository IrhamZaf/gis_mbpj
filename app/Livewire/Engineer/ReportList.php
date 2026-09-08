<?php

namespace App\Livewire\Engineer;

use App\Models\Report;
use App\Models\ReportCategory;
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
    public string $filterCategory = '';
    public string $filterStatus = 'pending_engineer_verification';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
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

        $reports = Report::with(['category', 'user', 'unit', 'siteVisit.ta'])
            ->visibleTo($user)
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('report_number', 'like', "%{$this->search}%")
                    ->orWhere('file_number', 'like', "%{$this->search}%")
                    ->orWhere('location_name', 'like', "%{$this->search}%");
            }))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterStatus, fn ($q) => $q->where('workflow_status', $this->filterStatus))
            ->orderByDesc('updated_at')
            ->paginate(10);

        return view('livewire.engineer.report-list', [
            'reports'    => $reports,
            'categories' => ReportCategory::orderBy('name')->get(),
            'unitName'   => $user->unit->name ?? '—',
        ]);
    }
}
