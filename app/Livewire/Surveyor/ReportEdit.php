<?php

namespace App\Livewire\Surveyor;

use App\Models\Report;
use App\Models\ReportAttachment;
use App\Models\ReportCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.master')]
#[Title('Edit Laporan')]
class ReportEdit extends Component
{
    use WithFileUploads;

    public Report $report;
    public int $category_id = 0;
    public string $title = '';
    public string $description = '';
    public string $location_name = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?array $gis_data = null;
    public array $newFiles = [];

    public function mount(Report $report)
    {
        if ($report->user_id !== Auth::id() || $report->status !== 'draft') {
            abort(403);
        }

        $this->report        = $report;
        $this->category_id   = $report->category_id;
        $this->title         = $report->title;
        $this->description   = $report->description ?? '';
        $this->location_name = $report->location_name ?? '';
        $this->latitude      = $report->latitude;
        $this->longitude     = $report->longitude;
        $this->gis_data      = $report->gis_data;
    }

    protected array $rules = [
        'category_id'   => 'required|exists:report_categories,id',
        'title'         => 'required|min:5',
        'description'   => 'required|min:10',
        'newFiles.*'    => 'nullable|file|max:10240',
    ];

    public function setCoordinates($lat, $lng)
    {
        $this->latitude  = round($lat, 7);
        $this->longitude = round($lng, 7);
    }

    public function setGisData($data)
    {
        $this->gis_data = $data;
    }

    public function deleteAttachment(int $id)
    {
        $attachment = ReportAttachment::where('report_id', $this->report->id)->findOrFail($id);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();
    }

    public function saveDraft()
    {
        $this->saveReport('draft');
    }

    public function submit()
    {
        $this->saveReport('submitted');
    }

    private function saveReport(string $status)
    {
        $this->validate();

        $this->report->update([
            'category_id'   => $this->category_id,
            'title'         => $this->title,
            'description'   => $this->description,
            'status'        => $status,
            'latitude'      => $this->latitude,
            'longitude'     => $this->longitude,
            'location_name' => $this->location_name ?: null,
            'gis_data'      => $this->gis_data,
            'submitted_at'  => $status === 'submitted' ? now() : null,
        ]);

        foreach ($this->newFiles as $file) {
            $path = $file->store('reports/' . $this->report->id, 'public');
            ReportAttachment::create([
                'report_id' => $this->report->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        session()->flash('message', $status === 'submitted' ? 'Laporan berjaya dihantar.' : 'Draf dikemaskini.');
        return redirect()->route('surveyor.reports');
    }

    public function render()
    {
        return view('livewire.surveyor.report-edit', [
            'categories'  => ReportCategory::orderBy('name')->get(),
            'attachments' => $this->report->attachments()->get(),
        ]);
    }
}
