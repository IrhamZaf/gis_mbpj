<?php

namespace App\Livewire\Surveyor;

use Livewire\Component;

/**
 * Isolated map — parent form typing does not re-render this component (no props from parent).
 */
class ReportMapPicker extends Component
{
    public function updateCoordinates(float $latitude, float $longitude, ?string $label = null): void
    {
        $this->dispatch('report-coordinates-updated', latitude: $latitude, longitude: $longitude, label: $label);
    }

    public function updateGisData(?array $data = null): void
    {
        $this->dispatch('report-gis-data-updated', data: $data);
    }

    public function render()
    {
        return view('livewire.surveyor.report-map-picker');
    }
}
