<?php

namespace App\Livewire\Engineering;

use App\Models\Report;
use App\Models\Unit;
use App\Models\User;
use App\Support\UnitTheme;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.master')]
class UnitDashboard extends Component
{
    public Unit $unit;

    public function mount(Unit $unit): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isSuperadmin() && ! $user->isDirector())) {
            abort(403);
        }

        if (! $unit->isActive()) {
            abort(404);
        }

        $this->unit = $unit;
    }

    public function render()
    {
        $unitId = $this->unit->id;
        $base = Report::query()->where('unit_id', $unitId);
        $theme = UnitTheme::for($this->unit);

        return view('livewire.engineering.unit-dashboard', [
            'user'  => Auth::user(),
            'unit'  => $this->unit,
            'theme' => $theme,
            'kpis'  => [
                'draft'             => (clone $base)->where('status', 'draft')->count(),
                'pending_visit'     => (clone $base)->where('workflow_status', 'pending_site_visit')->count(),
                'visit_progress'    => (clone $base)->whereIn('workflow_status', ['site_visit_in_progress', 'engineer_returned'])->count(),
                'pending_engineer'  => (clone $base)->where('workflow_status', 'pending_engineer_verification')->count(),
                'pending_director'  => (clone $base)->where('workflow_status', 'pending_director_approval')->count(),
                'approved'          => (clone $base)->where('workflow_status', 'approved')->count(),
                'rejected'          => (clone $base)->where('workflow_status', 'director_rejected')->count(),
                'total'             => (clone $base)->count(),
            ],
            'staff' => User::where('unit_id', $unitId)
                ->whereIn('role', ['ta', 'engineer', 'surveyor'])
                ->orderBy('role')
                ->orderBy('name')
                ->get(),
            'recentReports' => Report::with(['user', 'category'])
                ->where('unit_id', $unitId)
                ->latest()
                ->take(10)
                ->get(),
        ])->title('Dashboard Unit '.$this->unit->name);
    }
}
