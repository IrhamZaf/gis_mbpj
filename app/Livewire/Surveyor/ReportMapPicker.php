<?php

namespace App\Livewire\Surveyor;

use App\Support\MbsjArea;
use Livewire\Component;

/**
 * Isolated map — parent form typing does not re-render the Leaflet DOM (wire:ignore).
 * Lat/lng inputs live outside wire:ignore so users can edit them reliably.
 */
class ReportMapPicker extends Component
{
    public ?float $latitude = null;

    public ?float $longitude = null;

    public function mount(?float $latitude = null, ?float $longitude = null): void
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function updateCoordinates(float $latitude, float $longitude, ?string $label = null): void
    {
        if (! MbsjArea::contains($latitude, $longitude)) {
            $this->addError('latitude', MbsjArea::validationMessage());

            return;
        }

        $this->latitude = round($latitude, 7);
        $this->longitude = round($longitude, 7);
        $this->dispatch('report-coordinates-updated', latitude: $this->latitude, longitude: $this->longitude, label: $label);
    }

    public function applyManualCoordinates(): void
    {
        if (! is_numeric($this->latitude) || ! is_numeric($this->longitude)) {
            return;
        }

        $lat = round((float) $this->latitude, 7);
        $lng = round((float) $this->longitude, 7);

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            $this->addError('latitude', __('validation.between.numeric', ['attribute' => 'latitude', 'min' => -90, 'max' => 90]));

            return;
        }

        if (! MbsjArea::contains($lat, $lng)) {
            $this->addError('latitude', MbsjArea::validationMessage());

            return;
        }

        $this->resetErrorBag('latitude');
        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->dispatch('report-coordinates-updated', latitude: $lat, longitude: $lng, label: null);
        $this->js(sprintf(
            'window.gisMapPicker && window.gisMapPicker.setAnchor(%s, %s, null, false)',
            $lat,
            $lng
        ));
    }

    public function updatedLatitude(mixed $value): void
    {
        $this->applyManualCoordinates();
    }

    public function updatedLongitude(mixed $value): void
    {
        $this->applyManualCoordinates();
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
