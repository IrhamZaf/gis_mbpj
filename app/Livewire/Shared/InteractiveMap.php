<?php

namespace App\Livewire\Shared;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
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

        if ($user->isSuperadmin() || $user->isDirector()) {
            $query->when($this->filterUnit, fn ($q) => $q->where('unit_id', $this->filterUnit));
        } elseif ($user->isSurveyor() || $user->isEngineer() || $user->isTa()) {
            $query->where('unit_id', $user->unit_id);
            if ($user->isSurveyor()) {
                $query->where('user_id', $user->id);
            }
            if ($user->isEngineer() || $user->isTa()) {
                $query->where('status', '!=', 'draft');
            }
        }

        $query
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('report_number', 'like', "%{$this->search}%")
                    ->orWhere('location_name', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory));

        return $query->get()->map(function (Report $report) use ($user) {
            $categoryId = $report->category_id ?? 0;

            return [
                'id'             => $report->id,
                'title'          => $report->title,
                'report_number'  => $report->report_number,
                'latitude'       => (float) $report->latitude,
                'longitude'      => (float) $report->longitude,
                'status'         => $report->status,
                'status_label'   => $report->workflow_status_label ?: $report->status_label,
                'workflow_status'=> $report->workflow_status,
                'category'       => $report->category->name ?? '-',
                'category_id'    => $categoryId,
                'category_color' => $this->categoryColor($categoryId, $report->category->name ?? null),
                'unit'           => $report->unit->name ?? '-',
                'surveyor'       => $report->user->name ?? '-',
                'location_name'  => $report->location_name,
                'date'           => $report->created_at?->format('d/m/Y'),
                'url'            => $this->reportUrl($report, $user),
                'gis_data'       => $report->gis_data,
            ];
        })->values()->all();
    }

    private function categoryColor(int $categoryId, ?string $categoryName = null): string
    {
        $colors = [
            '#e74c3c', '#3498db', '#2ecc71', '#f39c12',
            '#9b59b6', '#1abc9c', '#e67e22', '#34495e',
        ];

        // Same display name → same colour (avoids duplicate-looking legend chips)
        if ($categoryName) {
            return $colors[crc32(mb_strtolower(trim($categoryName))) % count($colors)];
        }

        return $colors[$categoryId % count($colors)];
    }

    private function reportUrl(Report $report, $user): ?string
    {
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
            if ($report->unit?->code === 'SAL-CERUN') {
                return route('saliran-cerun.cases.show', $report);
            }

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
     * Categories for filter + legend (no duplicate display names).
     *
     * @return array{filter: \Illuminate\Support\Collection, legend: \Illuminate\Support\Collection}
     */
    protected function mapCategories(): array
    {
        $all = ReportCategory::query()
            ->with('unit:id,name,code')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'active');
            })
            ->orderBy('name')
            ->get();

        // Prefer unit-scoped categories when names collide with global ones
        $preferred = $all
            ->sortByDesc(fn (ReportCategory $c) => $c->unit_id ? 1 : 0)
            ->unique(fn (ReportCategory $c) => mb_strtolower(trim($c->name)))
            ->values();

        $nameCounts = $all->countBy(fn (ReportCategory $c) => mb_strtolower(trim($c->name)));

        $filter = $all->map(function (ReportCategory $c) use ($nameCounts) {
            $key = mb_strtolower(trim($c->name));
            $label = $c->name;
            if (($nameCounts[$key] ?? 0) > 1) {
                $label .= $c->unit?->name ? ' — '.$c->unit->name : ' — Global';
            }

            return (object) [
                'id' => $c->id,
                'name' => $label,
                'color' => $this->categoryColor((int) $c->id, $c->name),
            ];
        });

        $legend = $preferred->map(fn (ReportCategory $c) => (object) [
            'id' => $c->id,
            'name' => $c->name,
            'color' => $this->categoryColor((int) $c->id, $c->name),
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
            'units' => ($user->isSuperadmin() || $user->isDirector())
                ? Unit::active()->orderBy('sort_order')->orderBy('name')->get()
                : collect(),
            'isSuperadmin' => $user->isSuperadmin() || $user->isDirector(),
            'lockedUnit' => $user->unit?->name,
        ]);
    }
}
