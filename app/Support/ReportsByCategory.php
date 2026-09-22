<?php

namespace App\Support;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Aggregate report counts by canonical category code (across per-unit category rows).
 */
class ReportsByCategory
{
    /**
     * @return Collection<int, array{code: string, name: string, reports_count: int, color: string}>
     */
    public static function summarize(
        ?int $unitId = null,
        bool $excludeDrafts = false,
        ?callable $scope = null,
    ): Collection {
        $query = Report::query()
            ->join('report_categories', 'reports.category_id', '=', 'report_categories.id')
            ->when($unitId, fn (Builder $q) => $q->where('reports.unit_id', $unitId))
            ->when($excludeDrafts, fn (Builder $q) => $q->where('reports.status', '!=', 'draft'));

        if ($scope) {
            $scope($query);
        }

        $rows = $query
            ->select('report_categories.code', DB::raw('COUNT(reports.id) as total'))
            ->groupBy('report_categories.code')
            ->pluck('total', 'code');

        return collect(UnitModule::CATEGORY_CODES)->map(function (string $code) use ($rows) {
            $total = (int) ($rows[$code] ?? 0);
            if ($code === 'CERUN') {
                $total += (int) ($rows['CERUN_RUNTUH'] ?? 0);
            }

            $theme = UnitTheme::forCategory($code);

            return [
                'code' => $code,
                'name' => match ($code) {
                    'SINKHOLE' => __('app.sinkhole'),
                    'CERUN' => __('app.cerun'),
                    'BOREHOLE' => __('app.borehole'),
                    default => $code,
                },
                'reports_count' => $total,
                'color' => $theme['color'] ?? '#6c757d',
            ];
        })->values();
    }
}
