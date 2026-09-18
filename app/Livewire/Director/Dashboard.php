<?php

namespace App\Livewire\Director;

use App\Livewire\Concerns\BuildsUnitOverviewCards;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Dashboard Pengarah')]
class Dashboard extends Component
{
    use BuildsUnitOverviewCards;

    public function render()
    {
        $user = Auth::user();
        $base = Report::query();
        $unitCards = $this->buildUnitOverviewCards($user);

        return view('livewire.director.dashboard', [
            'user' => $user,
            'pending' => (clone $base)->where('workflow_status', 'pending_director_approval')->count(),
            'approved' => (clone $base)->where('workflow_status', 'approved')->count(),
            'rejected' => (clone $base)->where('workflow_status', 'director_rejected')->count(),
            'recent' => Report::with(['unit', 'user', 'latestEngineerVerification.engineer'])
                ->forDirectorQueue()
                ->latest('updated_at')
                ->take(8)
                ->get(),
            'unitCards' => $unitCards,
            'grandTotal' => $this->unitOverviewGrandTotal($unitCards),
        ]);
    }
}
