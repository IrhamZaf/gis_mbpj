<?php

namespace App\Livewire\UnitModule;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Support\UnitModule;
use App\Support\UnitTheme;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.master')]
class UnitDashboard extends Component
{
    public string $unitCode = 'SAL-CERUN';

    public string $filterYear = '';

    public string $filterCategory = '';

    public string $filterStatus = '';

    public function mount(?string $unitCode = null): void
    {
        $this->filterYear = (string) now()->year;

        $routeUnit = request()->route()?->parameter('unitCode')
            ?? request()->route()?->defaults['unitCode']
            ?? null;

        if ($unitCode) {
            $this->unitCode = $unitCode;
        } elseif (is_string($routeUnit) && $routeUnit !== '') {
            $this->unitCode = $routeUnit;
        } else {
            $this->syncUnitFromRoute();
        }
    }

    public function updatedUnitCode(string $value): void
    {
        $slug = UnitModule::unitSlugFromCode($value);
        if (! $slug) {
            return;
        }

        $this->redirect(route($slug.'.dashboard'), navigate: true);
    }

    public function resetFilters(): void
    {
        $this->filterYear = (string) now()->year;
        $this->filterCategory = '';
        $this->filterStatus = '';
        $this->dispatchMapRefresh();
    }

    public function updatedFilterCategory(): void
    {
        $this->dispatchMapRefresh();
    }

    public function updatedFilterStatus(): void
    {
        $this->dispatchMapRefresh();
    }

    public function updatedFilterYear(): void
    {
        $this->dispatchMapRefresh();
    }

    protected function syncUnitFromRoute(): void
    {
        $routeName = request()->route()?->getName() ?? '';
        foreach (UnitModule::UNIT_SLUGS as $slug => $code) {
            if (str_starts_with($routeName, $slug.'.')) {
                $this->unitCode = $code;

                return;
            }
        }
    }

    public function render()
    {
        $this->syncUnitFromRoute();

        $unit = Unit::where('code', $this->unitCode)->firstOrFail();
        $this->authorizeUnitAccess($unit);

        $user = Auth::user();
        $theme = UnitTheme::for($unit);
        $isOwnUnit = $user->isSuperadmin() || $user->canWriteUnit($unit->id);
        $isReadOnly = ! $isOwnUnit && ! $user->isDirector();

        $categories = ReportCategory::active()
            ->forUnit($unit->id)
            ->whereIn('code', UnitModule::CATEGORY_CODES)
            ->orderByRaw("CASE code WHEN 'SINKHOLE' THEN 1 WHEN 'CERUN' THEN 2 WHEN 'BOREHOLE' THEN 3 ELSE 9 END")
            ->get();

        $base = $this->unitBaseQuery($unit->id);

        $totalReports = (clone $base)->count();

        $categoryCounts = $this->categoryCounts($unit->id, $categories);
        $statusBreakdown = $this->statusBreakdown($unit->id);
        $siteVisitSummary = $this->siteVisitSummary($unit->id);
        $months = $this->monthlyTrend($unit->id);
        $mapPoints = $this->mapPoints($unit);
        $recent = $this->recentReports($unit->id);
        $availableYears = $this->availableYears($unit->id);
        $navUnits = UnitModule::navUnits();

        $pendingVisit = $siteVisitSummary['pending'];
        $approvedRow = collect($statusBreakdown)->firstWhere('key', 'approved');
        $completedReports = (int) ($approvedRow['total'] ?? 0);

        $kpi = [
            [
                'key' => 'total',
                'label' => __('app.total_reports'),
                'value' => $totalReports,
                'hint' => __('app.all_reports_for_unit'),
                'icon' => 'tabler-file-analytics',
                'color' => $theme['color'],
                'url' => UnitModule::categoryRoute($unit->code, 'SINKHOLE'),
            ],
            [
                'key' => 'sinkhole',
                'label' => __('app.sinkhole'),
                'value' => $categoryCounts['SINKHOLE'] ?? 0,
                'hint' => __('app.category_reports'),
                'icon' => UnitTheme::forCategory('SINKHOLE')['icon'],
                'color' => UnitTheme::forCategory('SINKHOLE')['color'],
                'url' => UnitModule::categoryRoute($unit->code, 'SINKHOLE'),
            ],
            [
                'key' => 'cerun',
                'label' => __('app.cerun'),
                'value' => $categoryCounts['CERUN'] ?? 0,
                'hint' => __('app.category_reports'),
                'icon' => UnitTheme::forCategory('CERUN')['icon'],
                'color' => UnitTheme::forCategory('CERUN')['color'],
                'url' => UnitModule::categoryRoute($unit->code, 'CERUN'),
            ],
            [
                'key' => 'borehole',
                'label' => __('app.borehole'),
                'value' => $categoryCounts['BOREHOLE'] ?? 0,
                'hint' => __('app.category_reports'),
                'icon' => UnitTheme::forCategory('BOREHOLE')['icon'],
                'color' => UnitTheme::forCategory('BOREHOLE')['color'],
                'url' => UnitModule::categoryRoute($unit->code, 'BOREHOLE'),
            ],
            [
                'key' => 'pending',
                'label' => __('app.pending_site_visit'),
                'value' => $pendingVisit,
                'hint' => __('app.awaiting_site_visit'),
                'icon' => 'tabler-map-pin',
                'color' => '#ffc107',
                'url' => UnitModule::categoryRoute($unit->code, 'SINKHOLE').'?filterStatus=pending',
            ],
            [
                'key' => 'completed',
                'label' => __('app.completed'),
                'value' => $completedReports,
                'hint' => __('app.workflow_completed'),
                'icon' => 'tabler-circle-check',
                'color' => '#198754',
                'url' => UnitModule::categoryRoute($unit->code, 'SINKHOLE').'?filterStatus=completed',
            ],
        ];

        $quickActions = $this->quickActions($unit, $user, $isOwnUnit);

        $byCategory = $categories->map(fn (ReportCategory $c) => [
            'code' => $c->code,
            'name' => $c->display_name,
            'total' => $categoryCounts[$c->code] ?? 0,
            'color' => UnitTheme::forCategory($c->code)['color'],
            'url' => UnitModule::categoryRoute($unit->code, $c->code),
        ]);

        $categoryMax = max(1, (int) collect($categoryCounts)->max());
        $statusMax = max(1, (int) collect($statusBreakdown)->max('total'));
        $trendMax = max(1, (int) $months->max('total'));
        $siteVisitMax = max(1, max($siteVisitSummary['pending'], $siteVisitSummary['in_progress'], $siteVisitSummary['completed']));

        return view('livewire.unit-module.unit-dashboard', [
            'unit' => $unit,
            'theme' => $theme,
            'user' => $user,
            'navUnits' => $navUnits,
            'categories' => $categories,
            'kpi' => $kpi,
            'byCategory' => $byCategory,
            'categoryMax' => $categoryMax,
            'statusBreakdown' => $statusBreakdown,
            'statusMax' => $statusMax,
            'siteVisitSummary' => $siteVisitSummary,
            'siteVisitMax' => $siteVisitMax,
            'months' => $months,
            'trendMax' => $trendMax,
            'mapPoints' => $mapPoints,
            'recent' => $recent,
            'availableYears' => $availableYears,
            'quickActions' => $quickActions,
            'totalReports' => $totalReports,
            'isOwnUnit' => $isOwnUnit,
            'isReadOnly' => $isReadOnly,
            'unitIcon' => UnitModule::unitIcon($unit->code),
            'mapRoute' => $this->mapRouteFor($user),
        ])->title(__('app.dashboard').' — '.$unit->name);
    }

    protected function unitBaseQuery(int $unitId)
    {
        $query = Report::query()->where('unit_id', $unitId);

        $user = Auth::user();
        if (! $user->isSuperadmin() && ! $user->isDirector()) {
            $query->where(function ($q) use ($user) {
                $q->where('status', '!=', 'draft')
                    ->orWhere('user_id', $user->id);
            });
        }

        if ($this->filterYear !== '') {
            $query->whereYear('created_at', (int) $this->filterYear);
        }

        if ($this->filterCategory !== '') {
            $query->where('category_id', $this->filterCategory);
        }

        if ($this->filterStatus !== '') {
            $this->applyStatusFilter($query, $this->filterStatus);
        }

        return $query;
    }

    protected function applyStatusFilter($query, string $status): void
    {
        match ($status) {
            'draft' => $query->where('status', 'draft'),
            'submitted' => $query->where('status', 'submitted')->where(function ($q) {
                $q->whereNull('workflow_status')->orWhere('workflow_status', 'pending_site_visit');
            }),
            'pending_site_visit' => $query->where('workflow_status', 'pending_site_visit'),
            'site_visit_in_progress' => $query->whereIn('workflow_status', ['site_visit_in_progress', 'engineer_returned']),
            'pending_engineer_verification' => $query->where('workflow_status', 'pending_engineer_verification'),
            'engineer_verified' => $query->where('workflow_status', 'engineer_verified'),
            'pending_director_approval' => $query->where('workflow_status', 'pending_director_approval'),
            'approved' => $query->where(function ($q) {
                $q->where('workflow_status', 'approved')->orWhere('status', 'completed');
            }),
            'director_rejected' => $query->where('workflow_status', 'director_rejected'),
            'completed' => $query->where(function ($q) {
                $q->where('workflow_status', 'approved')->orWhere('status', 'completed');
            }),
            default => null,
        };
    }

    /**
     * @param  Collection<int, ReportCategory>  $categories
     * @return array<string, int>
     */
    protected function categoryCounts(int $unitId, Collection $categories): array
    {
        $rows = $this->unitBaseQuery($unitId)
            ->select('category_id', DB::raw('COUNT(*) as total'))
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $out = [];
        foreach ($categories as $cat) {
            $out[$cat->code] = (int) ($rows[$cat->id] ?? 0);
        }

        return $out;
    }

    /**
     * @return list<array{key: string, label: string, total: int, color: string}>
     */
    protected function statusBreakdown(int $unitId): array
    {
        $base = $this->unitBaseQuery($unitId);

        $draft = (clone $base)->where('status', 'draft')->count();
        $pendingSv = (clone $base)->where('workflow_status', 'pending_site_visit')->count();
        $inProgress = (clone $base)->whereIn('workflow_status', ['site_visit_in_progress', 'engineer_returned'])->count();
        $pendingEng = (clone $base)->whereIn('workflow_status', ['pending_engineer_verification', 'engineer_verified'])->count();
        $pendingDir = (clone $base)->where('workflow_status', 'pending_director_approval')->count();
        $approved = (clone $base)->where(function ($q) {
            $q->where('workflow_status', 'approved')->orWhere('status', 'completed');
        })->count();
        $rejected = (clone $base)->where('workflow_status', 'director_rejected')->count();

        return [
            ['key' => 'draft', 'label' => __('app.status_draft'), 'total' => $draft, 'color' => '#6c757d'],
            ['key' => 'pending_site_visit', 'label' => __('app.wf_pending_site_visit'), 'total' => $pendingSv, 'color' => '#0dcaf0'],
            ['key' => 'site_visit', 'label' => __('app.wf_site_visit_in_progress'), 'total' => $inProgress, 'color' => '#0d6efd'],
            ['key' => 'verification', 'label' => __('app.wf_pending_engineer_verification'), 'total' => $pendingEng, 'color' => '#6f42c1'],
            ['key' => 'approval', 'label' => __('app.wf_pending_director_approval'), 'total' => $pendingDir, 'color' => '#fd7e14'],
            ['key' => 'approved', 'label' => __('app.wf_approved'), 'total' => $approved, 'color' => '#198754'],
            ['key' => 'rejected', 'label' => __('app.wf_director_rejected'), 'total' => $rejected, 'color' => '#dc3545'],
        ];
    }

    /**
     * @return array{pending: int, in_progress: int, completed: int}
     */
    protected function siteVisitSummary(int $unitId): array
    {
        $base = $this->unitBaseQuery($unitId);

        return [
            'pending' => (clone $base)->where('workflow_status', 'pending_site_visit')->count(),
            'in_progress' => (clone $base)->whereIn('workflow_status', ['site_visit_in_progress', 'engineer_returned'])->count(),
            'completed' => (clone $base)->where(function ($q) {
                $q->whereIn('workflow_status', [
                    'pending_engineer_verification',
                    'engineer_verified',
                    'pending_director_approval',
                    'approved',
                    'director_rejected',
                ])->orWhere('status', 'completed');
            })->count(),
        ];
    }

    protected function monthlyTrend(int $unitId): Collection
    {
        $year = (int) ($this->filterYear !== '' ? $this->filterYear : now()->year);

        $rows = $this->unitBaseQuery($unitId)
            ->whereYear('created_at', $year)
            ->select(DB::raw($this->monthExpression().' as month'), DB::raw('COUNT(*) as total'))
            ->groupBy('month')
            ->pluck('total', 'month');

        return collect(range(1, 12))->map(function (int $month) use ($rows, $year) {
            $label = now()->setDate($year, $month, 1)->translatedFormat('M');

            return [
                'month' => $month,
                'label' => $label,
                'total' => (int) ($rows[$month] ?? $rows[str_pad((string) $month, 2, '0', STR_PAD_LEFT)] ?? 0),
            ];
        });
    }

    protected function monthExpression(): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'sqlite' => "CAST(strftime('%m', created_at) AS INTEGER)",
            'pgsql' => 'EXTRACT(MONTH FROM created_at)',
            default => 'MONTH(created_at)',
        };
    }

    protected function mapPoints(Unit $unit): Collection
    {
        $query = Report::query()
            ->with(['category:id,name,code', 'user:id,name'])
            ->where('unit_id', $unit->id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        $user = Auth::user();
        if (! $user->isSuperadmin() && ! $user->isDirector()) {
            $query->where(function ($q) use ($user) {
                $q->where('status', '!=', 'draft')->orWhere('user_id', $user->id);
            });
        }

        if ($this->filterYear !== '') {
            $query->whereYear('created_at', (int) $this->filterYear);
        }
        if ($this->filterCategory !== '') {
            $query->where('category_id', $this->filterCategory);
        }
        if ($this->filterStatus !== '') {
            $this->applyStatusFilter($query, $this->filterStatus);
        }

        return $query->latest()->take(200)->get()->map(function (Report $r) use ($unit) {
            $code = UnitModule::normalizeCategoryCode($r->category->code ?? '') ?? '';

            return [
                'id' => $r->id,
                'lat' => (float) $r->latitude,
                'lng' => (float) $r->longitude,
                'title' => $r->title,
                'case' => $r->report_number,
                'cat' => $code,
                'catName' => $r->category->display_name ?? '',
                'location' => $r->location_name ?? '-',
                'status' => $r->workflow_status_label ?? $r->status_label,
                'url' => UnitModule::caseShowRoute($unit->code, $r),
            ];
        })->values();
    }

    protected function recentReports(int $unitId): Collection
    {
        return $this->unitBaseQuery($unitId)
            ->with(['category:id,name,code', 'user:id,name', 'siteVisit:id,report_id,status'])
            ->latest()
            ->take(10)
            ->get();
    }

    protected function availableYears(int $unitId): Collection
    {
        $years = Report::query()
            ->where('unit_id', $unitId)
            ->select(DB::raw($this->yearExpression().' as y'))
            ->groupBy('y')
            ->orderByDesc('y')
            ->pluck('y')
            ->filter()
            ->map(fn ($y) => (string) (int) $y)
            ->values();

        $current = (string) now()->year;
        if ($years->isEmpty()) {
            return collect([$current]);
        }

        if (! $years->contains($current)) {
            $years->prepend($current);
        }

        return $years;
    }

    protected function yearExpression(): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'sqlite' => "CAST(strftime('%Y', created_at) AS INTEGER)",
            'pgsql' => 'EXTRACT(YEAR FROM created_at)',
            default => 'YEAR(created_at)',
        };
    }

    /**
     * @return list<array{label: string, url: string, icon: string, class: string}>
     */
    protected function quickActions(Unit $unit, $user, bool $isOwnUnit): array
    {
        $actions = [
            [
                'label' => __('app.view_all_reports'),
                'url' => UnitModule::categoryRoute($unit->code, 'SINKHOLE'),
                'icon' => 'tabler-list',
                'class' => 'btn-outline-primary',
            ],
            [
                'label' => __('app.interactive_map'),
                'url' => $this->mapRouteFor($user),
                'icon' => 'tabler-map',
                'class' => 'btn-outline-primary',
            ],
            [
                'label' => __('app.pending_site_visit'),
                'url' => UnitModule::categoryRoute($unit->code, 'SINKHOLE').'?filterStatus=pending',
                'icon' => 'tabler-map-pin',
                'class' => 'btn-outline-warning',
            ],
        ];

        if ($isOwnUnit && ($user->isReportCreator() || $user->isSuperadmin())) {
            array_unshift($actions, [
                'label' => __('app.add_report'),
                'url' => UnitModule::caseCreateRoute($unit->code, 'SINKHOLE'),
                'icon' => 'tabler-plus',
                'class' => 'btn-primary',
            ]);
        }

        return $actions;
    }

    protected function mapRouteFor($user): string
    {
        return match (true) {
            $user->isSuperadmin() => route('superadmin.map'),
            $user->isConsultant() => route('consultant.map'),
            $user->isEngineer() => route('engineer.map'),
            $user->isTa() => route('ta.map'),
            $user->isDirector() => route('director.map'),
            default => '#',
        };
    }

    protected function dispatchMapRefresh(): void
    {
        $unit = Unit::where('code', $this->unitCode)->first();
        if (! $unit) {
            return;
        }

        $this->dispatch('dash-map-updated', points: $this->mapPoints($unit)->all());
    }

    protected function authorizeUnitAccess(Unit $unit): void
    {
        $user = Auth::user();
        if (! $user || ! $user->canBrowseAllUnits()) {
            abort(403, __('app.unit_access_denied'));
        }
    }
}
