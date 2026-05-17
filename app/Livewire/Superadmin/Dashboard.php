<?php

namespace App\Livewire\Superadmin;

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Dashboard Superadmin')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.superadmin.dashboard', [
            'totalUsers'      => User::count(),
            'totalSurveyors'  => User::where('role', 'surveyor')->count(),
            'totalEngineers'  => User::where('role', 'engineer')->count(),
            'totalReports'    => Report::count(),
            'submittedReports'=> Report::submitted()->count(),
            'draftReports'    => Report::draft()->count(),
            'totalCategories' => ReportCategory::count(),
        ]);
    }
}
