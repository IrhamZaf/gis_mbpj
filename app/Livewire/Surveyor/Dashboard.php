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

        return view('livewire.surveyor.dashboard', [
            'totalReports'    => Report::where('user_id', $userId)->count(),
            'draftReports'    => Report::where('user_id', $userId)->draft()->count(),
            'submittedReports'=> Report::where('user_id', $userId)->submitted()->count(),
            'recentReports'   => Report::with('category')->where('user_id', $userId)->latest()->take(5)->get(),
        ]);
    }
}
