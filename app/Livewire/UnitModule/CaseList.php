<?php

namespace App\Livewire\UnitModule;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Support\UnitModule;
use App\Support\UnitTheme;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
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

    #[Url]
    public string $filterStatus = '';

    public function mount(string $unitCode = 'SAL-CERUN', string $categoryCode = ''): void
    {
        $this->unitCode = $unitCode;
        if ($categoryCode !== '') {
            $this->categoryCode = UnitModule::normalizeCategoryCode($categoryCode) ?? $categoryCode;
        }
        $this->syncFromRoute();
    }

    public function boot(): void
    {
        // Same Livewire component serves multiple category URLs;
        // re-sync on every request so wire:navigate does not keep the wrong category.
        $this->syncFromRoute();
    }

    protected function syncFromRoute(): void
    {
        $route = request()->route();
        $routeName = $route?->getName() ?? '';

        foreach (UnitModule::UNIT_SLUGS as $slug => $code) {
            if (! str_starts_with($routeName, $slug.'.')) {
                continue;
            }

            $this->unitCode = $code;
            $suffix = substr($routeName, strlen($slug) + 1);
            if (isset(UnitModule::CATEGORY_SLUGS[$suffix])) {
                $this->categoryCode = UnitModule::CATEGORY_SLUGS[$suffix];
            }

            return;
        }

        // Fallbacks from route defaults / params
        $defaultUnit = $route?->parameter('unitCode') ?? $route?->defaults['unitCode'] ?? null;
        $defaultCat = $route?->parameter('categoryCode') ?? $route?->defaults['categoryCode'] ?? null;
        if (is_string($defaultUnit) && $defaultUnit !== '') {
            $this->unitCode = $defaultUnit;
        }
        if (is_string($defaultCat) && $defaultCat !== '') {
            $this->categoryCode = UnitModule::normalizeCategoryCode($defaultCat) ?? $defaultCat;
        }

        if ($this->categoryCode === '') {
            $this->categoryCode = 'SINKHOLE';
        }
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
        $this->syncFromRoute();

        $unit = Unit::where('code', $this->unitCode)->firstOrFail();
        $user = Auth::user();

        if (! $user->canBrowseAllUnits()) {
            abort(403);
        }

        $isOwnUnit = $user->isSuperadmin() || $user->canWriteUnit($unit->id);

        $category = ReportCategory::active()
            ->forUnit($unit->id)
            ->whereIn('code', array_filter([
                $this->categoryCode,
                $this->categoryCode === 'CERUN' ? 'CERUN_RUNTUH' : null,
            ]))
            ->firstOrFail();

        $this->categoryCode = UnitModule::normalizeCategoryCode($category->code) ?? $category->code;

        $theme = UnitTheme::forCategory($category->code);

        $base = Report::query()
            ->forUnitListing($user, $unit->id)
            ->where('category_id', $category->id);

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
        $siblingCategories = ReportCategory::active()
            ->forUnit($unit->id)
            ->whereIn('code', UnitModule::CATEGORY_CODES)
            ->orderByRaw("CASE code WHEN 'SINKHOLE' THEN 1 WHEN 'CERUN' THEN 2 WHEN 'BOREHOLE' THEN 3 ELSE 9 END")
            ->get();

        return view('livewire.unit-module.case-list', [
            'unit' => $unit,
            'category' => $category,
            'theme' => $theme,
            'stats' => $stats,
            'reports' => $reports,
            'docsTotal' => $docsTotal,
            'canCreate' => $user->canWriteUnit($unit->id) && ($user->isReportCreator() || $user->isSuperadmin()),
            'canEdit' => $user->canWriteUnit($unit->id) && ($user->isReportCreator() || $user->isSuperadmin()),
            'isOwnUnit' => $user->canWriteUnit($unit->id),
            'isReadOnly' => ! $user->canWriteUnit($unit->id),
            'siblingCategories' => $siblingCategories,
            'dashboardUrl' => UnitModule::dashboardRoute($unit->code),
            'createUrl' => UnitModule::caseCreateRoute($unit->code, $category->code),
        ])->title($category->display_name.' — '.$unit->name);
    }
}
