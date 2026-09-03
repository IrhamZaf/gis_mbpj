<?php

namespace App\Livewire\Engineer;

use App\Models\Report;
use App\Models\ReportCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Dashboard Engineer')]
class Dashboard extends Component
{
    public function render()
    {
        $submitted = Report::submitted();

        $reportsByCategory = ReportCategory::query()
            ->withCount(['reports' => fn ($q) => $q->submitted()])
            ->orderByDesc('reports_count')
            ->get();

        $weeklyTrend = Report::query()
            ->submitted()
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

        return view('livewire.engineer.dashboard', [
            'user'              => Auth::user(),
            'totalSubmitted'    => (clone $submitted)->count(),
            'submittedThisWeek' => (clone $submitted)->where('submitted_at', '>=', now()->startOfWeek())->count(),
            'submittedToday'    => (clone $submitted)->whereDate('submitted_at', today())->count(),
            'mappedReports'     => (clone $submitted)->whereNotNull('latitude')->whereNotNull('longitude')->count(),
            'reportsByCategory' => $reportsByCategory,
            'totalCategories'   => ReportCategory::count(),
            'recentReports'     => Report::with(['category', 'user'])
                ->submitted()
                ->latest('submitted_at')
                ->take(8)
                ->get(),
            'trendDays'         => $trendDays,
            'trendMax'          => max(1, $trendDays->max('total')),
        ]);
    }
}
