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
        $userId = Auth::id();
        $base = Report::where('user_id', $userId);

        return view('livewire.surveyor.dashboard', [
            'user'             => Auth::user(),
            'totalReports'     => (clone $base)->count(),
            'draftReports'     => (clone $base)->draft()->count(),
            'submittedReports' => (clone $base)->submitted()->count(),
            'mappedReports'    => (clone $base)->whereNotNull('latitude')->whereNotNull('longitude')->count(),
            'reportsThisWeek'  => (clone $base)->where('created_at', '>=', now()->startOfWeek())->count(),
            'recentReports'    => Report::with('category')
                ->where('user_id', $userId)
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }
}
