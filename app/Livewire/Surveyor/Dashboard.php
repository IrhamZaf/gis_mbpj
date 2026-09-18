<?php

namespace App\Livewire\Surveyor;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\Unit;
use App\Support\UnitModule;
use App\Support\UnitTheme;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Dashboard Surveyor')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $ownUnitId = $user->unit_id;

        $myBase = Report::query()
            ->where('user_id', $user->id)
            ->when($ownUnitId, fn ($q) => $q->where('unit_id', $ownUnitId));

        $units = UnitModule::navUnits();
        $unitCards = $this->buildUnitCards($units, $user);

        return view('livewire.surveyor.dashboard', [
            'user' => $user,
            'unitName' => $user->unit->name ?? '—',
            'ownUnitId' => $ownUnitId,
            'unitTheme' => UnitTheme::for($user->unit),
            'totalReports' => (clone $myBase)->count(),
            'draftReports' => (clone $myBase)->where('status', 'draft')->count(),
            'submittedReports' => (clone $myBase)->where('status', 'submitted')->count(),
            'returnedReports' => (clone $myBase)->whereIn('workflow_status', ['engineer_returned', 'director_rejected'])->count(),
            'completedReports' => (clone $myBase)->where(function ($q) {
                $q->where('status', 'completed')->orWhere('workflow_status', 'approved');
            })->count(),
            'reportsThisWeek' => (clone $myBase)->where('created_at', '>=', now()->startOfWeek())->count(),
            'unitCards' => $unitCards,
            'grandTotal' => collect($unitCards)->sum('total'),
            'recentReports' => Report::with(['category', 'unit'])
                ->where('user_id', $user->id)
                ->when($ownUnitId, fn ($q) => $q->where('unit_id', $ownUnitId))
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }

    /**
     * @param  Collection<int, Unit>  $units
     * @return list<array<string, mixed>>
     */
    protected function buildUnitCards(Collection $units, $user): array
    {
        $cards = [];

        foreach ($units as $unit) {
            $theme = UnitTheme::for($unit);
            $isOwn = $user->unit_id && (int) $user->unit_id === (int) $unit->id;

            $base = Report::query()->where('unit_id', $unit->id);
            // Cross-unit: hide other people's drafts
            if (! $isOwn) {
                $base->where('status', '!=', 'draft');
            } else {
                // Own unit overview: show all unit reports for monitoring,
                // but personal drafts still counted via my stats above.
                $base->where(function ($q) use ($user) {
                    $q->where('status', '!=', 'draft')->orWhere('user_id', $user->id);
                });
            }

            $categories = ReportCategory::active()
                ->forUnit($unit->id)
                ->whereIn('code', UnitModule::CATEGORY_CODES)
                ->get()
                ->keyBy('code');

            $catCounts = (clone $base)
                ->select('category_id', DB::raw('COUNT(*) as total'))
                ->groupBy('category_id')
                ->pluck('total', 'category_id');

            $sinkholeId = $categories->get('SINKHOLE')?->id;
            $cerunId = $categories->get('CERUN')?->id;
            $boreholeId = $categories->get('BOREHOLE')?->id;

            $cards[] = [
                'unit' => $unit,
                'theme' => $theme,
                'icon' => UnitModule::unitIcon($unit->code),
                'isOwn' => $isOwn,
                'dashboardUrl' => UnitModule::dashboardRoute($unit->code),
                'total' => (clone $base)->count(),
                'sinkhole' => $sinkholeId ? (int) ($catCounts[$sinkholeId] ?? 0) : 0,
                'cerun' => $cerunId ? (int) ($catCounts[$cerunId] ?? 0) : 0,
                'borehole' => $boreholeId ? (int) ($catCounts[$boreholeId] ?? 0) : 0,
                'pending' => (clone $base)->where('workflow_status', 'pending_site_visit')->count(),
                'completed' => (clone $base)->where(function ($q) {
                    $q->where('workflow_status', 'approved')->orWhere('status', 'completed');
                })->count(),
                'sinkholeUrl' => UnitModule::categoryRoute($unit->code, 'SINKHOLE'),
                'cerunUrl' => UnitModule::categoryRoute($unit->code, 'CERUN'),
                'boreholeUrl' => UnitModule::categoryRoute($unit->code, 'BOREHOLE'),
            ];
        }

        return $cards;
    }
}
