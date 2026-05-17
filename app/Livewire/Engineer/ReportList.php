<?php

namespace App\Livewire\Engineer;

use App\Models\Report;
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

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterCategory() { $this->resetPage(); }

    public function render()
    {
        $reports = Report::with(['category', 'user'])
            ->submitted()
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%")
                ->orWhere('report_number', 'like', "%{$this->search}%")
                ->orWhere('location_name', 'like', "%{$this->search}%"))
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->orderBy('submitted_at', 'desc')
            ->paginate(10);

        $categories = \App\Models\ReportCategory::orderBy('name')->get();

        return view('livewire.engineer.report-list', [
            'reports'    => $reports,
            'categories' => $categories,
        ]);
    }
}
