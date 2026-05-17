<?php

namespace App\Livewire\Shared;

use App\Models\Report;
use App\Models\ReportCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.master')]
#[Title('Peta Interaktif')]
class InteractiveMap extends Component
{
    public string $search = '';
    public string $filterStatus = '';
    public string $filterCategory = '';

    public function updatedSearch(): void
    {
        $this->dispatchMarkers();
    }

    public function updatedFilterStatus(): void
    {
        $this->dispatchMarkers();
    }

    public function updatedFilterCategory(): void
    {
        $this->dispatchMarkers();
    }

    private function dispatchMarkers(): void
    {
        $this->dispatch('map-markers-updated', markers: $this->markers);
    }

    public function getMarkersProperty(): array
    {
        $user = Auth::user();

        $query = Report::with('category')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($user->isSurveyor()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isEngineer()) {
            $query->submitted();
        }

        $query
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('report_number', 'like', "%{$this->search}%")
                    ->orWhere('location_name', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory));

        return $query->get()->map(function (Report $report) use ($user) {
            $categoryId = $report->category_id ?? 0;

            return [
                'id'             => $report->id,
                'title'          => $report->title,
                'report_number'  => $report->report_number,
                'latitude'       => (float) $report->latitude,
                'longitude'      => (float) $report->longitude,
                'status'         => $report->status,
                'status_label'   => $report->status === 'submitted' ? 'Dihantar' : 'Draf',
                'category'       => $report->category->name ?? '-',
                'category_id'    => $categoryId,
                'category_color' => $this->categoryColor($categoryId),
                'location_name'  => $report->location_name,
                'url'            => $this->reportUrl($report, $user),
                'gis_data'       => $report->gis_data,
            ];
        })->values()->all();
    }

    private function categoryColor(int $categoryId): string
    {
        $colors = [
            '#e74c3c', '#3498db', '#2ecc71', '#f39c12',
            '#9b59b6', '#1abc9c', '#e67e22', '#34495e',
        ];

        return $colors[$categoryId % count($colors)];
    }

    private function reportUrl(Report $report, $user): ?string
    {
        if ($user->isEngineer()) {
            return route('engineer.reports.view', $report);
        }

        if ($user->isSurveyor()) {
            return route('surveyor.reports.edit', $report);
        }

        return null;
    }

    public function render()
    {
        return view('livewire.shared.interactive-map', [
            'markers'    => $this->markers,
            'categories' => ReportCategory::orderBy('name')->get(),
        ]);
    }
}
