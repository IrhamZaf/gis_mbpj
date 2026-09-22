<?php

namespace App\Livewire\Concerns;

use App\Models\Report;
use App\Models\User;
use App\Support\ReportsByCategory;
use App\Support\UnitModule;
use App\Support\UnitTheme;

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

            $byCode = ReportsByCategory::summarize(
                unitId: $unit->id,
                scope: function ($q) use ($user, $isGlobal, $isOwn) {
                    if ($isGlobal || $user->isConsultant()) {
                        return;
                    }

                    if ($isOwn) {
                        $q->where(function ($inner) use ($user) {
                            $inner->where('reports.status', '!=', 'draft')
                                ->orWhere('reports.user_id', $user->id);
                        });
                    } else {
                        $q->where('reports.status', '!=', 'draft');
                    }
                },
            )->mapWithKeys(fn (array $row) => [$row['code'] => $row['reports_count']]);

            $cards[] = [
                'unit' => $unit,
                'theme' => $theme,
                'icon' => UnitModule::unitIcon($unit->code),
                'isOwn' => $isOwn || $user->isConsultant(),
                'isReadOnly' => ! $isGlobal && ! $canWrite,
                'dashboardUrl' => UnitModule::dashboardRoute($unit->code),
                'total' => (clone $base)->count(),
                'sinkhole' => (int) ($byCode['SINKHOLE'] ?? 0),
                'cerun' => (int) ($byCode['CERUN'] ?? 0),
                'borehole' => (int) ($byCode['BOREHOLE'] ?? 0),
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
