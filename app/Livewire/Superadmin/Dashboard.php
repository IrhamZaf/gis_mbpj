<?php

namespace App\Livewire\Superadmin;

use App\Models\Report;
use App\Models\Unit;
use App\Models\User;
use App\Support\ReportsByCategory;
use App\Support\UnitModule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Dashboard Superadmin')]
class Dashboard extends Component
{
    public string $filterUnit = '';
    public string $filterStatus = '';
    public string $filterCategory = '';

    public function render()
    {
        $reportsByCategory = ReportsByCategory::summarize(
            unitId: $this->filterUnit !== '' ? (int) $this->filterUnit : null,
            scope: function ($q) {
                $q->when($this->filterStatus, function ($q) {
                    if (in_array($this->filterStatus, ['draft', 'submitted', 'completed'], true)) {
                        $q->where('reports.status', $this->filterStatus);
                    } else {
                        $q->where('reports.workflow_status', $this->filterStatus);
                    }
                });
                $this->applyCategoryCodeFilter($q, 'report_categories.code');
            },
        );

        $reportsByUnit = Unit::active()
            ->withCount(['reports' => function ($q) {
                $this->applyFilters($q);
            }])
            ->orderBy('sort_order')
            ->get();

        $base = Report::query();
        $this->applyFilters($base);

        $reportsThisWeek = (clone $base)->where('created_at', '>=', now()->startOfWeek())->count();
        $submittedThisWeek = Report::query()
            ->when($this->filterUnit, fn ($q) => $q->where('unit_id', $this->filterUnit))
            ->where('status', '!=', 'draft')
            ->where('submitted_at', '>=', now()->startOfWeek());
        $this->applyCategoryCodeFilter($submittedThisWeek);
        $submittedThisWeek = $submittedThisWeek->count();

        $mappedReports = (clone $base)->whereNotNull('latitude')->whereNotNull('longitude')->count();

        $recentReports = Report::with(['category', 'user', 'unit'])
            ->when($this->filterUnit, fn ($q) => $q->where('unit_id', $this->filterUnit))
            ->when($this->filterStatus, function ($q) {
                if (in_array($this->filterStatus, ['draft', 'submitted', 'completed'], true)) {
                    $q->where('status', $this->filterStatus);
                } else {
                    $q->where('workflow_status', $this->filterStatus);
                }
            });
        $this->applyCategoryCodeFilter($recentReports);
        $recentReports = $recentReports->latest()->take(8)->get();

        $weeklyTrend = Report::query()
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->when($this->filterUnit, fn ($q) => $q->where('unit_id', $this->filterUnit))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $trendDays = collect(range(6, 0))->map(function (int $ago) use ($weeklyTrend) {
            $day = now()->subDays($ago)->toDateString();

            return [
                'label' => now()->subDays($ago)->translatedFormat('D'),
                'total' => (int) ($weeklyTrend[$day] ?? 0),
            ];
        });

        return view('livewire.superadmin.dashboard', [
            'user'               => Auth::user(),
            'totalUsers'         => User::count(),
            'totalConsultants'   => User::where('role', 'consultant')->count(),
            'totalEngineers'     => User::where('role', 'engineer')->count(),
            'totalTa'            => User::where('role', 'ta')->count(),
            'totalUnits'         => Unit::count(),
            'totalReports'       => (clone $base)->count(),
            'submittedReports'   => (clone $base)->where('status', '!=', 'draft')->count(),
            'draftReports'       => (clone $base)->where('status', 'draft')->count(),
            'completedReports'   => (clone $base)->where(fn ($q) => $q->where('status', 'completed')->orWhere('workflow_status', 'approved'))->count(),
            'pendingSiteVisit'   => (clone $base)->where('workflow_status', 'pending_site_visit')->count(),
            'pendingEngineer'    => (clone $base)->where('workflow_status', 'pending_engineer_verification')->count(),
            'pendingDirector'    => (clone $base)->where('workflow_status', 'pending_director_approval')->count(),
            'totalCategories'    => count(UnitModule::CATEGORY_CODES),
            'mappedReports'      => $mappedReports,
            'reportsThisWeek'    => $reportsThisWeek,
            'submittedThisWeek'  => $submittedThisWeek,
            'reportsByCategory'  => $reportsByCategory,
            'reportsByUnit'      => $reportsByUnit,
            'recentReports'      => $recentReports,
            'trendDays'          => $trendDays,
            'trendMax'           => max(1, $trendDays->max('total')),
            'units'              => Unit::active()->orderBy('sort_order')->get(),
            'categories'         => collect(UnitModule::CATEGORY_CODES)->map(fn (string $code) => (object) [
                'id' => $code,
                'code' => $code,
                'name' => match ($code) {
                    'SINKHOLE' => __('app.sinkhole'),
                    'CERUN' => __('app.cerun'),
                    'BOREHOLE' => __('app.borehole'),
                    default => $code,
                },
            ]),
        ]);
    }

    private function applyFilters($query): void
    {
        $query
            ->when($this->filterUnit, fn ($q) => $q->where('unit_id', $this->filterUnit))
            ->when($this->filterStatus, function ($q) {
                if (in_array($this->filterStatus, ['draft', 'submitted', 'completed'], true)) {
                    $q->where('status', $this->filterStatus);
                } else {
                    $q->where('workflow_status', $this->filterStatus);
                }
            });

        $this->applyCategoryCodeFilter($query);
    }

    private function applyCategoryCodeFilter($query, string $codeColumn = 'category_id'): void
    {
        if ($this->filterCategory === '') {
            return;
        }

        $code = UnitModule::normalizeCategoryCode($this->filterCategory) ?? $this->filterCategory;
        $codes = $code === 'CERUN' ? ['CERUN', 'CERUN_RUNTUH'] : [$code];

        if ($codeColumn === 'category_id') {
            $query->whereHas('category', fn ($q) => $q->whereIn('code', $codes));

            return;
        }

        $query->whereIn($codeColumn, $codes);
    }
}
