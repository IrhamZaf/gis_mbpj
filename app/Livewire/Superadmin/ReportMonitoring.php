<?php

namespace App\Livewire\Superadmin;

use App\Models\Report;
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

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterCategory() { $this->resetPage(); }

    public function render()
    {
        $reports = Report::with(['category', 'user'])
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%")
                ->orWhere('report_number', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $categories = \App\Models\ReportCategory::orderBy('name')->get();

        return view('livewire.superadmin.report-monitoring', [
            'reports'    => $reports,
            'categories' => $categories,
        ]);
    }
}
