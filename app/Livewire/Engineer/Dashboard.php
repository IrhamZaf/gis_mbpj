<?php

namespace App\Livewire\Engineer;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Support\UnitTheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.master')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $unitId = $user->unit_id;
        $theme = UnitTheme::for($user->unit);
        $base = Report::query()->where('unit_id', $unitId);

        $weeklyTrend = Report::query()
            ->where('unit_id', $unitId)
            ->whereNotNull('submitted_at')
            ->select(DB::raw('DATE(submitted_at) as day'), DB::raw('COUNT(*) as total'))
            ->where('submitted_at', '>=', now()->subDays(6)->startOfDay())
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

        $reportsByCategory = ReportCategory::query()
            ->withCount(['reports' => fn ($q) => $q->where('unit_id', $unitId)->where('status', '!=', 'draft')])
            ->orderByDesc('reports_count')
            ->get();

        return view('livewire.engineer.dashboard', [
            'user'              => $user,
            'unitName'          => $theme['name'],
            'unitTheme'         => $theme,
            'totalSubmitted'    => (clone $base)->where('status', '!=', 'draft')->count(),
            'pendingVerify'     => (clone $base)->where('workflow_status', 'pending_engineer_verification')->count(),
            'verified'          => (clone $base)->whereIn('workflow_status', ['pending_director_approval', 'approved', 'engineer_verified'])->count(),
            'returnedReports'   => (clone $base)->where('workflow_status', 'engineer_returned')->count(),
            'approvedReports'   => (clone $base)->where('workflow_status', 'approved')->count(),
            'submittedThisWeek' => (clone $base)->where('submitted_at', '>=', now()->startOfWeek())->count(),
            'submittedToday'    => (clone $base)->whereDate('submitted_at', today())->count(),
            'underReview'       => (clone $base)->where('workflow_status', 'pending_engineer_verification')->count(),
            'completedReports'  => (clone $base)->where('workflow_status', 'approved')->count(),
            'mappedReports'     => (clone $base)->whereNotNull('latitude')->whereNotNull('longitude')->where('status', '!=', 'draft')->count(),
            'reportsByCategory' => $reportsByCategory,
            'totalCategories'   => ReportCategory::count(),
            'recentReports'     => Report::with(['category', 'user'])
                ->where('unit_id', $unitId)
                ->whereIn('workflow_status', ['pending_engineer_verification', 'director_rejected', 'pending_director_approval', 'approved', 'engineer_returned'])
                ->latest('updated_at')
                ->take(8)
                ->get(),
            'trendDays'         => $trendDays,
            'trendMax'          => max(1, $trendDays->max('total')),
        ])->title('Dashboard Engineer — Unit '.$theme['name']);
    }
}
