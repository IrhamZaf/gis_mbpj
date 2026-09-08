<?php

namespace App\Livewire\Surveyor;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Dashboard Surveyor')]
class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $base = Report::where('user_id', $user->id)->where('unit_id', $user->unit_id);

        return view('livewire.surveyor.dashboard', [
            'user'              => $user,
            'unitName'          => $user->unit->name ?? '—',
            'totalReports'      => (clone $base)->count(),
            'draftReports'      => (clone $base)->where('status', 'draft')->count(),
            'submittedReports'  => (clone $base)->where('status', 'submitted')->count(),
            'returnedReports'   => (clone $base)->whereIn('workflow_status', ['engineer_returned', 'director_rejected'])->count(),
            'completedReports'  => (clone $base)->where(function ($q) {
                $q->where('status', 'completed')->orWhere('workflow_status', 'approved');
            })->count(),
            'mappedReports'     => (clone $base)->whereNotNull('latitude')->whereNotNull('longitude')->count(),
            'reportsThisWeek'   => (clone $base)->where('created_at', '>=', now()->startOfWeek())->count(),
            'recentReports'     => Report::with('category')
                ->where('user_id', $user->id)
                ->where('unit_id', $user->unit_id)
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }
}
