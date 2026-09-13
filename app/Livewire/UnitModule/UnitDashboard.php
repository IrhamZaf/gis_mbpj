<?php

namespace App\Livewire\UnitModule;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Support\UnitTheme;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.master')]
class UnitDashboard extends Component
{
    public string $unitCode = 'SAL-CERUN';

    public string $filterCategory = '';

    public string $filterStatus = '';

    public function mount(?string $unitCode = null): void
    {
        if ($unitCode) {
            $this->unitCode = $unitCode;
        }
    }

    public function render()
    {
        $unit = Unit::where('code', $this->unitCode)->firstOrFail();
        $this->authorizeUnitAccess($unit);

        $theme = UnitTheme::for($unit);
        $base = Report::query()->where('unit_id', $unit->id);

        $categories = ReportCategory::active()->forUnit($unit->id)->orderBy('name')->get();
        $sinkholeId = $categories->firstWhere('code', 'SINKHOLE')?->id;
        $cerunId = $categories->firstWhere('code', 'CERUN_RUNTUH')?->id;

        $monthlyRaw = Report::query()
            ->where('unit_id', $unit->id)
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['created_at']);

        $monthly = $monthlyRaw->groupBy(fn ($r) => $r->created_at->format('Y-m'))
            ->map->count();

        $months = collect(range(5, 0))->map(function (int $ago) use ($monthly) {
            $key = now()->subMonths($ago)->format('Y-m');

            return [
                'label' => now()->subMonths($ago)->translatedFormat('M'),
                'total' => (int) ($monthly[$key] ?? 0),
            ];
        });

        $mapQuery = Report::with(['category', 'user'])
            ->where('unit_id', $unit->id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterStatus === 'pending', function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNull('workflow_status')->orWhere('status', 'draft')
                        ->orWhere('workflow_status', 'pending_site_visit');
                });
            })
            ->when($this->filterStatus === 'site_visit', fn ($q) => $q->whereIn('workflow_status', ['pending_site_visit', 'site_visit_in_progress', 'engineer_returned']))
            ->when($this->filterStatus === 'completed', function ($q) {
                $q->where(function ($q2) {
                    $q2->where('workflow_status', 'approved')->orWhere('status', 'completed');
                });
            })
            ->latest()
            ->take(200)
            ->get();

        $mapPoints = $mapQuery->map(function (Report $r) {
            return [
                'id' => $r->id,
                'lat' => (float) $r->latitude,
                'lng' => (float) $r->longitude,
                'title' => $r->title,
                'case' => $r->report_number,
                'cat' => $r->category->code ?? '',
                'catName' => $r->category->name ?? '',
                'status' => $r->workflow_status_label ?? $r->status_label,
                'url' => route('saliran-cerun.cases.show', $r),
            ];
        })->values();

        return view('livewire.unit-module.unit-dashboard', [
            'unit' => $unit,
            'theme' => $theme,
            'user' => Auth::user(),
            'totalCases' => (clone $base)->count(),
            'sinkholeCount' => $sinkholeId ? (clone $base)->where('category_id', $sinkholeId)->count() : 0,
            'cerunCount' => $cerunId ? (clone $base)->where('category_id', $cerunId)->count() : 0,
            'pendingVisit' => (clone $base)->whereIn('workflow_status', ['pending_site_visit', 'site_visit_in_progress', 'engineer_returned'])->count(),
            'completedVisit' => (clone $base)->where(function ($q) {
                $q->where('workflow_status', 'approved')->orWhere('status', 'completed');
            })->count(),
            'byCategory' => $categories->map(fn ($c) => [
                'name' => $c->name,
                'total' => (clone $base)->where('category_id', $c->id)->count(),
            ]),
            'recent' => Report::with(['category', 'user'])
                ->where('unit_id', $unit->id)
                ->latest()
                ->take(8)
                ->get(),
            'categories' => $categories,
            'mapPoints' => $mapPoints,
            'months' => $months,
            'trendMax' => max(1, $months->max('total')),
        ])->title('Dashboard — '.$unit->name);
    }

    protected function authorizeUnitAccess(Unit $unit): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }
        if ($user->isSuperadmin() || $user->isDirector()) {
            return;
        }
        if ($user->unit_id && (int) $user->unit_id === (int) $unit->id) {
            return;
        }
        // Allow surveyors assigned to this unit module
        if ($user->isSurveyor() && (int) $user->unit_id === (int) $unit->id) {
            return;
        }
        abort(403, __('app.unit_access_denied'));
    }
}
