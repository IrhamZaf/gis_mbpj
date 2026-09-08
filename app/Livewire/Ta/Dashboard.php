<?php

namespace App\Livewire\Ta;

use App\Models\Report;
use App\Support\UnitTheme;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.master')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $unit = $user->unit;
        $theme = UnitTheme::for($unit);
        $base = Report::query()->where('unit_id', $user->unit_id);

        return view('livewire.ta.dashboard', [
            'user'     => $user,
            'unitName' => $theme['name'],
            'unitTheme'=> $theme,
            'pending'  => (clone $base)->where('workflow_status', 'pending_site_visit')->count(),
            'inProgress' => (clone $base)->whereIn('workflow_status', ['site_visit_in_progress', 'engineer_returned'])->count(),
            'awaitingEngineer' => (clone $base)->where('workflow_status', 'pending_engineer_verification')->count(),
            'completed' => (clone $base)->whereIn('workflow_status', ['approved'])->count(),
            'recent' => Report::with(['user', 'unit', 'category'])
                ->where('unit_id', $user->unit_id)
                ->forTaQueue()
                ->latest('submitted_at')
                ->take(8)
                ->get(),
        ])->title('Dashboard TA — Unit '.$theme['name']);
    }
}
