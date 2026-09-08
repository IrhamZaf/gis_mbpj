<?php

namespace App\Livewire\Engineering;

use App\Models\Report;
use App\Models\Unit;
use App\Models\User;
use App\Support\UnitTheme;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Engineering — Unit')]
class UnitHub extends Component
{
    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || (! $user->isSuperadmin() && ! $user->isDirector())) {
            abort(403);
        }
    }

    public function render()
    {
        $units = Unit::active()->orderBy('sort_order')->get()->map(function (Unit $unit) {
            $theme = UnitTheme::for($unit);
            $base = Report::query()->where('unit_id', $unit->id);

            return [
                'unit'       => $unit,
                'theme'      => $theme,
                'reports'    => (clone $base)->count(),
                'pending'    => (clone $base)->whereIn('workflow_status', [
                    'pending_site_visit',
                    'site_visit_in_progress',
                    'pending_engineer_verification',
                    'pending_director_approval',
                    'engineer_returned',
                ])->count(),
                'staff'      => User::where('unit_id', $unit->id)->whereIn('role', ['ta', 'engineer'])->count(),
                'approved'   => (clone $base)->where('workflow_status', 'approved')->count(),
            ];
        });

        return view('livewire.engineering.unit-hub', [
            'units' => $units,
            'user'  => Auth::user(),
        ]);
    }
}
