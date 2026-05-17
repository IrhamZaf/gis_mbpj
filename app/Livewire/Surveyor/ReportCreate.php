<?php

namespace App\Livewire\Surveyor;

use App\Models\Report;
use App\Models\ReportAttachment;
use App\Models\ReportCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.master')]
#[Title('Cipta Laporan')]
class ReportCreate extends Component
{
    use WithFileUploads;

    public int $category_id = 0;
    public string $title = '';
    public string $description = '';
    public string $location_name = '';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public ?array $gis_data = null;
    public array $files = [];

    protected array $rules = [
        'category_id'   => 'required|exists:report_categories,id',
        'title'         => 'required|min:5',
        'description'   => 'required|min:10',
        'location_name' => 'nullable|string',
        'latitude'      => 'nullable|numeric|between:-90,90',
        'longitude'     => 'nullable|numeric|between:-180,180',
        'files.*'       => 'nullable|file|max:10240',
    ];

    protected array $messages = [
        'category_id.required' => 'Sila pilih kategori laporan.',
        'title.required'       => 'Sila masukkan tajuk laporan.',
        'description.required' => 'Sila masukkan keterangan.',
        'files.*.max'          => 'Saiz fail maksimum 10MB.',
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

    public function removeFile($index)
    {
        array_splice($this->files, $index, 1);
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

        $report = Report::create([
            'category_id'   => $this->category_id,
            'user_id'       => Auth::id(),
            'title'         => $this->title,
            'description'   => $this->description,
            'status'        => $status,
            'latitude'      => $this->latitude,
            'longitude'     => $this->longitude,
            'location_name' => $this->location_name ?: null,
            'gis_data'      => $this->gis_data,
            'submitted_at'  => $status === 'submitted' ? now() : null,
        ]);

        // Upload files
        foreach ($this->files as $file) {
            $path = $file->store('reports/' . $report->id, 'public');

            ReportAttachment::create([
                'report_id' => $report->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        $msg = $status === 'submitted' ? 'Laporan berjaya dihantar.' : 'Draf laporan berjaya disimpan.';
        session()->flash('message', $msg);

        return redirect()->route('surveyor.reports');
    }

    public function render()
    {
        return view('livewire.surveyor.report-create', [
            'categories' => ReportCategory::orderBy('name')->get(),
        ]);
    }
}
