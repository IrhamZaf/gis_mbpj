<?php

namespace App\Livewire\Surveyor;

use App\Livewire\Concerns\BuildsUnitOverviewCards;
use App\Models\Report;
use App\Support\ReportsByCategory;
use App\Support\UnitTheme;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Dashboard Consultant')]
class Dashboard extends Component
{
    use BuildsUnitOverviewCards;

    public function render()
    {
        $user = Auth::user();

        $myBase = Report::query()->where('user_id', $user->id);

        $unitCards = $this->buildUnitOverviewCards($user);
        $reportsByCategory = ReportsByCategory::summarize(
            scope: fn ($q) => $q->where('reports.user_id', $user->id),
        );

        return view('livewire.surveyor.dashboard', [
            'user' => $user,
            'unitName' => __('app.role_consultant'),
            'ownUnitId' => null,
            'unitTheme' => UnitTheme::for(null),
            'totalReports' => (clone $myBase)->count(),
            'draftReports' => (clone $myBase)->where('status', 'draft')->count(),
            'submittedReports' => (clone $myBase)->where('status', 'submitted')->count(),
            'returnedReports' => (clone $myBase)->whereIn('workflow_status', ['engineer_returned', 'director_rejected'])->count(),
            'completedReports' => (clone $myBase)->where(function ($q) {
                $q->where('status', 'completed')->orWhere('workflow_status', 'approved');
            })->count(),
            'reportsThisWeek' => (clone $myBase)->where('created_at', '>=', now()->startOfWeek())->count(),
            'unitCards' => $unitCards,
            'grandTotal' => $this->unitOverviewGrandTotal($unitCards),
            'reportsByCategory' => $reportsByCategory,
            'recentReports' => Report::with(['category', 'unit'])
                ->where('user_id', $user->id)
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }
}
