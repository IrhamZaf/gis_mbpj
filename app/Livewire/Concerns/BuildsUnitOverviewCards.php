<?php

namespace App\Livewire\Concerns;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use App\Support\UnitModule;
use App\Support\UnitTheme;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait BuildsUnitOverviewCards
{
    /**
     * Overview cards for all 4 engineering units (cross-unit read monitoring).
     *
     * @return list<array<string, mixed>>
     */
    protected function buildUnitOverviewCards(User $user): array
    {
        $cards = [];

        foreach (UnitModule::navUnits() as $unit) {
            $theme = UnitTheme::for($unit);
            $canWrite = $user->canWriteUnit($unit->id);
            $isOwn = $user->unit_id && (int) $user->unit_id === (int) $unit->id;
            $isGlobal = $user->isSuperadmin() || $user->isDirector();

            $base = Report::query()->where('unit_id', $unit->id);

            if ($isGlobal || $user->isConsultant()) {
                // Full visibility including drafts for monitoring / all-unit writers
            } elseif ($isOwn) {
                $base->where(function ($q) use ($user) {
                    $q->where('status', '!=', 'draft')->orWhere('user_id', $user->id);
                });
            } else {
                $base->where('status', '!=', 'draft');
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
                'isOwn' => $isOwn || $user->isConsultant(),
                'isReadOnly' => ! $isGlobal && ! $canWrite,
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

    protected function unitOverviewGrandTotal(array $unitCards): int
    {
        return (int) collect($unitCards)->sum('total');
    }
}
