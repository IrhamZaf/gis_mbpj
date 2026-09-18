<?php

namespace App\Livewire\Shared;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Support\UnitModule;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Peta Interaktif')]
class InteractiveMap extends Component
{
    public string $search = '';

    public string $filterStatus = '';

    public string $filterCategory = '';

    public string $filterUnit = '';

    public function updatedSearch(): void
    {
        $this->dispatchMarkers();
    }

    public function updatedFilterStatus(): void
    {
        $this->dispatchMarkers();
    }

    public function updatedFilterCategory(): void
    {
        $this->dispatchMarkers();
    }

    public function updatedFilterUnit(): void
    {
        // Reset category when unit changes so cross-unit category ids are not kept
        $this->filterCategory = '';
        $this->dispatchMarkers();
    }

    private function dispatchMarkers(): void
    {
        $this->dispatch('map-markers-updated', markers: $this->markers);
    }

    public function getMarkersProperty(): array
    {
        $user = Auth::user();

        $query = Report::with(['category', 'unit', 'user'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Cross-unit GIS read for all staff; optional unit filter
        if ($user->isSuperadmin() || $user->isDirector() || $user->canBrowseAllUnits()) {
            $query->when($this->filterUnit, fn ($q) => $q->where('unit_id', $this->filterUnit));
        }

        // Hide others' drafts
        if (! $user->isSuperadmin() && ! $user->isDirector()) {
            $query->where(function ($q) use ($user) {
                $q->where('status', '!=', 'draft')
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('status', 'draft')->where('user_id', $user->id);
                    });
            });
        }

        // Surveyor map: can see all units' non-draft + own drafts (not limited to own reports only for cross-unit)
        // Engineer/TA: exclude drafts already handled above

        $query
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('report_number', 'like', "%{$this->search}%")
                    ->orWhere('location_name', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, function ($q) {
                $status = $this->filterStatus;
                if (in_array($status, Report::REPORT_STATUSES, true)) {
                    $q->where('status', $status);
                } elseif (in_array($status, Report::WORKFLOW_STATUSES, true)) {
                    $q->where('workflow_status', $status);
                }
            })
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory));

        return $query->get()->map(function (Report $report) use ($user) {
            $categoryId = $report->category_id ?? 0;

            return [
                'id' => $report->id,
                'title' => $report->title,
                'report_number' => $report->report_number,
                'latitude' => (float) $report->latitude,
                'longitude' => (float) $report->longitude,
                'status' => $report->status,
                'status_label' => $report->workflow_status_label ?: $report->status_label,
                'workflow_status' => $report->workflow_status,
                'category' => $report->category?->display_name ?? '-',
                'category_id' => $categoryId,
                'category_color' => $this->categoryColor($categoryId, $report->category?->display_name ?? null),
                'unit' => $report->unit->name ?? '-',
                'surveyor' => $report->user->name ?? '-',
                'location_name' => $report->location_name,
                'date' => $report->created_at?->format('d/m/Y'),
                'url' => $this->reportUrl($report, $user),
                'gis_data' => $report->gis_data,
            ];
        })->values()->all();
    }

    private function categoryColor(int $categoryId, ?string $categoryName = null): string
    {
        $colors = [
            '#e74c3c', '#3498db', '#2ecc71', '#f39c12',
            '#9b59b6', '#1abc9c', '#e67e22', '#34495e',
        ];

        if ($categoryName) {
            return $colors[crc32(mb_strtolower(trim($categoryName))) % count($colors)];
        }

        return $colors[$categoryId % count($colors)];
    }

    private function reportUrl(Report $report, $user): ?string
    {
        $unitCode = $report->unit?->code;

        // Prefer unit-module case show (read-only for other units via policy)
        if ($unitCode && UnitModule::unitSlugFromCode($unitCode) && $user->can('view', $report)) {
            if ($user->isEngineer() && $user->belongsToSameUnit($report) && $user->can('review', $report)) {
                return route('engineer.reports.view', $report);
            }

            if ($user->isTa() && $user->belongsToSameUnit($report) && ($user->can('startSiteVisit', $report) || $user->can('manageSiteVisit', $report))) {
                return route('site-visits.form', $report);
            }

            return UnitModule::caseShowRoute($unitCode, $report);
        }

        if ($user->isEngineer()) {
            return route('engineer.reports.view', $report);
        }

        if ($user->isTa()) {
            return route('site-visits.form', $report);
        }

        if ($user->isDirector()) {
            return route('director.reports.view', $report);
        }

        if ($user->isSuperadmin()) {
            return route('superadmin.reports.show', $report);
        }

        if ($user->isSurveyor()) {
            return $user->can('update', $report)
                ? route('surveyor.reports.edit', $report)
                : route('surveyor.reports.view', $report);
        }

        return null;
    }

    /**
     * Categories for filter + legend, optionally scoped to selected unit.
     *
     * @return array{filter: \Illuminate\Support\Collection, legend: \Illuminate\Support\Collection}
     */
    protected function mapCategories(): array
    {
        $user = Auth::user();

        $query = ReportCategory::query()
            ->with('unit:id,name,code')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            })
            ->whereNotNull('unit_id')
            ->whereIn('code', UnitModule::CATEGORY_CODES);

        if ($this->filterUnit !== '') {
            $query->where('unit_id', $this->filterUnit);
        }

        $all = $query->orderBy('name')->get();

        $nameCounts = $all->countBy(fn (ReportCategory $c) => mb_strtolower(trim($c->display_name)));

        $filter = $all->map(function (ReportCategory $c) use ($nameCounts) {
            $key = mb_strtolower(trim($c->display_name));
            $label = $c->display_name;
            if (($nameCounts[$key] ?? 0) > 1) {
                $label .= $c->unit?->name ? ' — '.$c->unit->name : '';
            }

            return (object) [
                'id' => $c->id,
                'name' => $label,
                'unit_id' => $c->unit_id,
                'color' => $this->categoryColor((int) $c->id, $c->display_name),
            ];
        });

        $legend = $all
            ->unique(fn (ReportCategory $c) => mb_strtolower(trim($c->display_name)))
            ->values()
            ->map(fn (ReportCategory $c) => (object) [
                'id' => $c->id,
                'name' => $c->display_name,
                'color' => $this->categoryColor((int) $c->id, $c->display_name),
            ]);

        return ['filter' => $filter, 'legend' => $legend];
    }

    public function render()
    {
        $user = Auth::user();
        $categories = $this->mapCategories();

        return view('livewire.shared.interactive-map', [
            'markers' => $this->markers,
            'categories' => $categories['filter'],
            'legendCategories' => $categories['legend'],
            'units' => Unit::active()->whereIn('code', array_values(UnitModule::UNIT_SLUGS))->orderBy('sort_order')->orderBy('name')->get(),
            'showUnitFilter' => true,
            'lockedUnit' => null,
        ]);
    }
}
