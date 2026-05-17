<?php

namespace App\Livewire\Surveyor;

use App\Livewire\Concerns\AppliesSurveyMetadata;
use App\Livewire\Concerns\StoresSurveyAttachments;
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
#[Title('Edit Laporan')]
class ReportEdit extends Component
{
    use AppliesSurveyMetadata;
    use StoresSurveyAttachments;
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
        'newFiles.*'    => 'nullable|file|max:20480',
    ];

    public function setCoordinates($lat, $lng, $label = null)
    {
        $this->latitude  = round($lat, 7);
        $this->longitude = round($lng, 7);

        if ($label && trim($this->location_name) === '') {
            $this->location_name = mb_substr(trim($label), 0, 255);
        }
    }

    public function setGisData($data)
    {
        $this->gis_data = $data;
    }

    public function deleteAttachment(int $id)
    {
        $attachment = ReportAttachment::where('report_id', $this->report->id)->findOrFail($id);
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();
    }

    public function updatedNewFiles(): void
    {
        $this->applyMetadataFromUploadedFiles($this->newFiles);
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
        $this->applyMetadataFromUploadedFiles($this->newFiles);
        $this->validateSurveyFiles($this->newFiles, $this->latitude, $this->longitude);

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

        $this->storeAttachments($this->report, $this->newFiles, $this->latitude, $this->longitude);

        session()->flash('message', $status === 'submitted' ? 'Laporan berjaya dihantar.' : 'Draf dikemaskini.');

        return redirect()->route('surveyor.reports');
    }

    public function render()
    {
        return view('livewire.surveyor.report-edit', [
            'categories'  => ReportCategory::orderBy('name')->get(),
            'attachments' => $this->report->attachments()->get(),
            'surveyLayers' => $this->report->attachments()
                ->whereIn('document_type', ['survey_3d', 'survey_2d'])
                ->where('parse_status', 'ok')
                ->get()
                ->map(fn ($a) => [
                    'file_name'    => $a->file_name,
                    'parse_status' => $a->parse_status,
                    'parsed_data'  => $a->parsed_data,
                ])
                ->values(),
        ]);
    }
}
