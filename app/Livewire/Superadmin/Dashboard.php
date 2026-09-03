<?php

namespace App\Livewire\Superadmin;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Dashboard Superadmin')]
class Dashboard extends Component
{
    public function render()
    {
        $reportsByCategory = ReportCategory::query()
            ->withCount('reports')
            ->orderByDesc('reports_count')
            ->get();

        $reportsThisWeek = Report::where('created_at', '>=', now()->startOfWeek())->count();
        $submittedThisWeek = Report::submitted()->where('submitted_at', '>=', now()->startOfWeek())->count();
        $mappedReports = Report::whereNotNull('latitude')->whereNotNull('longitude')->count();

        $recentReports = Report::with(['category', 'user'])
            ->latest()
            ->take(8)
            ->get();

        $weeklyTrend = Report::query()
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
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
            'user'              => Auth::user(),
            'totalUsers'        => User::count(),
            'totalSurveyors'    => User::where('role', 'surveyor')->count(),
            'totalEngineers'    => User::where('role', 'engineer')->count(),
            'totalReports'      => Report::count(),
            'submittedReports'  => Report::submitted()->count(),
            'draftReports'      => Report::draft()->count(),
            'totalCategories'   => ReportCategory::count(),
            'mappedReports'     => $mappedReports,
            'reportsThisWeek'   => $reportsThisWeek,
            'submittedThisWeek' => $submittedThisWeek,
            'reportsByCategory' => $reportsByCategory,
            'recentReports'     => $recentReports,
            'trendDays'         => $trendDays,
            'trendMax'          => max(1, $trendDays->max('total')),
        ]);
    }
}
