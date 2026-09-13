<?php

namespace App\Livewire\UnitModule;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Support\UnitTheme;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.master')]
class CaseList extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $unitCode = 'SAL-CERUN';

    public string $categoryCode = '';

    public string $search = '';

    public string $filterStatus = '';

    public function mount(string $unitCode = 'SAL-CERUN', string $categoryCode = ''): void
    {
        $this->unitCode = $unitCode;
        if ($categoryCode !== '') {
            $this->categoryCode = $categoryCode;
        }
        $this->syncCategoryFromRoute();
    }

    public function boot(): void
    {
        // Same Livewire component serves Sinkhole + Cerun Runtuh URLs;
        // re-sync on every request so wire:navigate does not keep the wrong category.
        $this->syncCategoryFromRoute();
    }

    protected function syncCategoryFromRoute(): void
    {
        $route = request()->route()?->getName();

        $this->categoryCode = match ($route) {
            'saliran-cerun.cerun' => 'CERUN_RUNTUH',
            'saliran-cerun.sinkhole' => 'SINKHOLE',
            default => $this->categoryCode !== '' ? $this->categoryCode : 'SINKHOLE',
        };
    }

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
        $this->syncCategoryFromRoute();

        $unit = Unit::where('code', $this->unitCode)->firstOrFail();
        $user = Auth::user();
        if (! $user->isSuperadmin() && ! $user->isDirector() && (int) $user->unit_id !== (int) $unit->id) {
            abort(403);
        }

        $category = ReportCategory::active()
            ->forUnit($unit->id)
            ->where('code', $this->categoryCode)
            ->firstOrFail();

        $theme = UnitTheme::forCategory($category->code);

        $base = Report::query()
            ->where('unit_id', $unit->id)
            ->where('category_id', $category->id)
            ->when($user->isSurveyor(), fn ($q) => $q->where('user_id', $user->id));

        $stats = [
            'total' => (clone $base)->count(),
            'draft' => (clone $base)->where('status', 'draft')->count(),
            'pending' => (clone $base)->where('workflow_status', 'pending_site_visit')->count(),
            'in_progress' => (clone $base)->whereIn('workflow_status', ['site_visit_in_progress', 'engineer_returned'])->count(),
            'completed' => (clone $base)->where(function ($q) {
                $q->where('workflow_status', 'approved')->orWhere('status', 'completed');
            })->count(),
        ];

        $reports = (clone $base)
            ->with(['user', 'category', 'siteVisit', 'attachments' => fn ($q) => $q->where('is_current', true)])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('report_number', 'like', '%'.$this->search.'%')
                        ->orWhere('file_number', 'like', '%'.$this->search.'%')
                        ->orWhere('location_name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterStatus === 'draft', fn ($q) => $q->where('status', 'draft'))
            ->when($this->filterStatus === 'pending', fn ($q) => $q->where('workflow_status', 'pending_site_visit'))
            ->when($this->filterStatus === 'in_progress', fn ($q) => $q->whereIn('workflow_status', ['site_visit_in_progress', 'engineer_returned']))
            ->when($this->filterStatus === 'completed', function ($q) {
                $q->where(function ($q2) {
                    $q2->where('workflow_status', 'approved')->orWhere('status', 'completed');
                });
            })
            ->latest()
            ->paginate(10);

        $docsTotal = $category->attachmentTypes()->count();

        return view('livewire.unit-module.case-list', [
            'unit' => $unit,
            'category' => $category,
            'theme' => $theme,
            'stats' => $stats,
            'reports' => $reports,
            'docsTotal' => $docsTotal,
            'canCreate' => $user->isSurveyor() || $user->isSuperadmin(),
            'listRoute' => $category->code === 'CERUN_RUNTUH' ? 'saliran-cerun.cerun' : 'saliran-cerun.sinkhole',
        ])->title($category->name.' — '.$unit->name);
    }
}
