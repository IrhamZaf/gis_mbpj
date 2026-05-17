<?php

namespace App\Livewire\Engineer;

use App\Models\Report;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Dashboard Engineer')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.engineer.dashboard', [
            'totalSubmitted' => Report::submitted()->count(),
            'recentReports'  => Report::with(['category', 'user'])->submitted()->latest('submitted_at')->take(5)->get(),
        ]);
    }
}
