<?php

namespace App\Livewire\Superadmin;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.master')]
#[Title('Pemantauan Laporan')]
class ReportMonitoring extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterCategory = '';
    public string $filterUnit = '';
    public string $filterSurveyor = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatingFilterUnit(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSurveyor(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $reports = Report::with(['category', 'user', 'unit'])
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
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterUnit, fn ($q) => $q->where('unit_id', $this->filterUnit))
            ->when($this->filterSurveyor, fn ($q) => $q->where('user_id', $this->filterSurveyor))
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.superadmin.report-monitoring', [
            'reports'    => $reports,
            'categories' => ReportCategory::orderBy('name')->get(),
            'units'      => Unit::active()->orderBy('sort_order')->get(),
            'surveyors'  => User::where('role', 'surveyor')->orderBy('name')->get(),
        ]);
    }
}
